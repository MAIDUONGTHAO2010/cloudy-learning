<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PresignProfileAvatarUploadRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use App\Services\NotificationService;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public const MAX_AVATAR_SIZE = 5242880; // 5 MB
    public function __construct(protected NotificationService $notificationService) {}

    public function login(LoginRequest $request)
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return response()->json([
                'message' => 'These credentials do not match our records.',
                'errors' => ['email' => ['These credentials do not match our records.']],
            ], 401);
        }

        if (! Auth::user()->isActive()) {
            Auth::logout();

            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
                'errors' => ['email' => ['Your account has been deactivated. Please contact support.']],
            ], 403);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful.',
            'user' => $this->userPayload(Auth::user()),
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => (int) $request->role,
        ]);

        $profile = Profile::create([
            'user_id' => $user->id,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex !== null ? (int) $request->sex : null,
        ]);

        if ($request->filled('categories')) {
            $profile->categories()->sync(array_slice($request->categories, 0, 3));
        }

        Auth::login($user);
        $request->session()->regenerate();

        $this->notificationService->notifyUserWelcome($user);
        $this->notificationService->notifyAdminNewUser($user);

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        $user = Auth::user();

        if (! $user || $user->isAdmin()) {
            return response()->json(null);
        }

        return response()->json($this->userPayload($user));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
            'sex' => 'nullable|in:0,1,2',
            'bio' => 'nullable|string|max:500',
            'categories' => 'nullable|array|max:3',
            'categories.*' => 'integer|exists:categories,id',
            'avatar' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        $updateData = ['name' => $data['name'], 'email' => $data['email']];
        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }
        $user->update($updateData);

        $profileData = [];
        if (array_key_exists('date_of_birth', $data)) {
            $profileData['date_of_birth'] = $data['date_of_birth'];
        }
        if (array_key_exists('sex', $data)) {
            $profileData['sex'] = isset($data['sex']) ? (int) $data['sex'] : null;
        }
        if (array_key_exists('bio', $data)) {
            $profileData['bio'] = $data['bio'];
        }
        if (array_key_exists('avatar', $data) && $data['avatar'] !== null) {
            $profileData['avatar'] = $data['avatar'];
        }

        $profile = $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        if (array_key_exists('categories', $data)) {
            $profile->categories()->sync(array_slice($data['categories'] ?? [], 0, 3));
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function presignAvatar(PresignProfileAvatarUploadRequest $request): JsonResponse
    {
        $user    = Auth::user();
        $profile = $user->profile;

        $data      = $request->validated();
        $extension = strtolower(pathinfo($data['file_name'], PATHINFO_EXTENSION) ?: 'png');
        $name      = Str::limit(
            Str::slug(pathinfo($data['file_name'], PATHINFO_FILENAME) ?: 'avatar'),
            60,
            ''
        ) ?: 'avatar';
        $path = 'profiles/avatars/' . now()->format('Y/m') . '/' . Str::uuid() . '-' . $name . '.' . $extension;

        Log::info('User requested avatar upload presign', [
            'user_id'      => $user->id,
            'has_avatar'   => $profile && $profile->getRawOriginal('avatar') !== null,
            'file_name'    => $data['file_name'],
            'content_type' => $data['content_type'],
            'file_size'    => $data['file_size'],
            'path'         => $path,
        ]);

        $diskConfig       = config('filesystems.disks.s3');
        $internalEndpoint = rtrim((string) $diskConfig['endpoint'], '/');
        $publicEndpoint   = rtrim((string) ($diskConfig['public_endpoint'] ?: $diskConfig['endpoint']), '/');
        $parsedPublic     = parse_url($publicEndpoint);

        $client = new S3Client([
            'version'                 => 'latest',
            'region'                  => $diskConfig['region'],
            'endpoint'                => $internalEndpoint,
            'use_path_style_endpoint' => (bool) $diskConfig['use_path_style_endpoint'],
            'credentials'             => [
                'key'    => $diskConfig['key'],
                'secret' => $diskConfig['secret'],
            ],
        ]);

        $putCommand  = $client->getCommand('PutObject', [
            'Bucket'      => $diskConfig['bucket'],
            'Key'         => $path,
            'ContentType' => $data['content_type'],
        ]);
        $putRequest  = $client->createPresignedRequest($putCommand, '+15 minutes');
        $uploadUrl   = $this->buildPresignedUrl($putRequest->getUri(), $parsedPublic);

        $getCommand  = $client->getCommand('GetObject', [
            'Bucket' => $diskConfig['bucket'],
            'Key'    => $path,
        ]);
        $getRequest  = $client->createPresignedRequest($getCommand, '+1 hour');
        $previewUrl  = $this->buildPresignedUrl($getRequest->getUri(), $parsedPublic);

        return response()->json([
            'path'          => $path,
            'upload_url'    => $uploadUrl,
            'headers'       => ['Content-Type' => $data['content_type']],
            'preview_url'   => $previewUrl,
            'max_file_size' => self::MAX_AVATAR_SIZE,
        ]);
    }

    private function userPayload($user): array
    {
        $user->loadMissing('profile.categories:id,name,slug');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'profile' => $user->profile ? [
                'avatar' => $user->profile->avatar,
                'date_of_birth' => $user->profile->date_of_birth?->format('Y-m-d'),
                'sex' => $user->profile->sex,
                'bio' => $user->profile->bio,
                'categories' => $user->profile->categories,
            ] : null,
        ];
    }

    private function buildPresignedUrl(\Psr\Http\Message\UriInterface $uri, array $parsedPublic): string
    {
        $parsedRaw = parse_url((string) $uri);
        $basePath  = rtrim($parsedPublic['path'] ?? '', '/');

        return ($parsedPublic['scheme'] ?? 'http') . '://'
            . ($parsedPublic['host'] ?? 'localhost')
            . (isset($parsedPublic['port']) ? ':' . $parsedPublic['port'] : '')
            . $basePath
            . ($parsedRaw['path'] ?? '')
            . (isset($parsedRaw['query']) ? '?' . $parsedRaw['query'] : '');
    }
}
