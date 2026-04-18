<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Summary stats ──────────────────────────────────────────────────
        $totalUsers = User::count();
        $totalCourses = Course::count();
        $totalLessons = Lesson::count();
        $totalCategories = Category::count();

        // ── Monthly lesson count — last 6 months ──────────────────────────
        $monthlyLessons = Lesson::select(
            DB::raw("TO_CHAR(created_at, 'Mon') AS month"),
            DB::raw("TO_CHAR(created_at, 'YYYY-MM') AS sort_key"),
            DB::raw('COUNT(*) AS count')
        )
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('sort_key', 'month')
            ->orderBy('sort_key')
            ->get()
            ->map(fn ($row) => [
                'month' => $row->month,
                'count' => (int) $row->count,
            ]);

        // ── Category distribution ─────────────────────────────────────────
        $categoryCounts = Course::whereNotNull('category_id')
            ->select('category_id', DB::raw('COUNT(*) AS count'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get();

        $totalCategorised = $categoryCounts->sum('count') ?: 1;

        $categoryDistribution = $categoryCounts->map(fn ($row) => [
            'name' => $row->category->name ?? 'Unknown',
            'count' => (int) $row->count,
            'percent' => round($row->count / $totalCategorised * 100),
        ])->sortByDesc('count')->values();

        // ── Recent courses ────────────────────────────────────────────────
        $recentCourses = Course::with('instructor:id,name')
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'user_id', 'is_active', 'created_at']);

        return response()->json([
            'stats' => [
                'total_users' => $totalUsers,
                'total_courses' => $totalCourses,
                'total_lessons' => $totalLessons,
                'total_categories' => $totalCategories,
            ],
            'monthly_lessons' => $monthlyLessons,
            'category_distribution' => $categoryDistribution,
            'recent_courses' => $recentCourses,
        ]);
    }
}
