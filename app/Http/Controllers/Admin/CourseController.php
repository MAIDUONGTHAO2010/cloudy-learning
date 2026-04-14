<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('instructor:id,name')
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('order')
            ->get();

        return response()->json($courses);
    }

    public function show(int $id)
    {
        $course = Course::with('instructor:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->findOrFail($id);

        return response()->json($course);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail'   => 'nullable|string|max:255',
            'order'       => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));

        $course = Course::create($data);
        $course->load('instructor:id,name');
        $course->loadCount(['lessons', 'reviews']);
        $course->loadAvg('reviews', 'rating');

        return response()->json($course, 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail'   => 'nullable|string|max:255',
            'order'       => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $id);

        $course = Course::findOrFail($id);
        $course->update($data);
        $course->load('instructor:id,name');
        $course->loadCount(['lessons', 'reviews']);
        $course->loadAvg('reviews', 'rating');

        return response()->json($course);
    }

    public function destroy(int $id)
    {
        Course::findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function reorder(Request $request)
    {
        $items = $request->validate([
            'items'         => 'required|array',
            'items.*.id'    => 'required|integer|exists:courses,id',
            'items.*.order' => 'required|integer|min:0',
        ])['items'];

        foreach ($items as $item) {
            Course::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Reordered successfully']);
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = Course::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}
