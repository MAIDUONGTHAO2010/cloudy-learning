<?php

namespace App\Repositories;

use App\Enums\User\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepositoryEloquent extends BaseRepository implements UserRepositoryInterface
{
    public function model(): string
    {
        return User::class;
    }

    public function getPaginated(array $filters)
    {
        $query = User::query()->select('id', 'name', 'email', 'role', 'is_active', 'created_at');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->where('role', (int) $filters['role']);
        }

        return $query->orderByDesc('created_at')->paginate(10);
    }

    public function getStats(): array
    {
        return [
            'total' => User::count(),
            'students' => User::where('role', UserRole::STUDENT)->count(),
            'instructors' => User::where('role', UserRole::INSTRUCTOR)->count(),
            'admins' => User::where('role', UserRole::ADMIN)->count(),
        ];
    }

    public function getInstructors()
    {
        return User::where('role', UserRole::INSTRUCTOR)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
    }

    public function getPublicInstructors()
    {
        return User::where('role', UserRole::INSTRUCTOR)
            ->whereHas('courses', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getTopInstructors(int $limit = 6)
    {
        return User::where('role', UserRole::INSTRUCTOR)
            ->withCount(['courses' => fn ($q) => $q->where('is_active', true)])
            ->withAvg('courseReviews', 'rating')
            ->orderByDesc('courses_count')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'created_at']);
    }

    public function toggleActive(int $id): mixed
    {
        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        return $user;
    }
}
