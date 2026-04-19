<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function login(LoginRequest $request)
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return response()->json([
                'message' => 'These credentials do not match our records.',
                'errors' => ['email' => ['These credentials do not match our records.']],
            ], 422);
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

        $profile = $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        if (array_key_exists('categories', $data)) {
            $profile->categories()->sync(array_slice($data['categories'] ?? [], 0, 3));
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $this->userPayload($user->fresh()),
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
}
