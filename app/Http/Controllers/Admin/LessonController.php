<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    public function index(int $courseId)
    {
        $course = Course::findOrFail($courseId);
        $lessons = $course->lessons()->orderBy('order')->get();

        return response()->json([
            'course'  => $course,
            'lessons' => $lessons,
        ]);
    }

    public function store(Request $request, int $courseId)
    {
        Course::findOrFail($courseId);

        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'nullable|string',
            'video_url' => 'nullable|string|max:255',
            'order'     => 'nullable|integer|min:0',
            'duration'  => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['course_id'] = $courseId;
        $data['slug']      = $this->uniqueSlug(Str::slug($data['title']));

        $lesson = Lesson::create($data);

        return response()->json($lesson, 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'nullable|string',
            'video_url' => 'nullable|string|max:255',
            'order'     => 'nullable|integer|min:0',
            'duration'  => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $id);

        $lesson = Lesson::findOrFail($id);
        $lesson->update($data);

        return response()->json($lesson);
    }

    public function destroy(int $id)
    {
        Lesson::findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function reorder(Request $request, int $courseId)
    {
        $items = $request->validate([
            'items'         => 'required|array',
            'items.*.id'    => 'required|integer|exists:lessons,id',
            'items.*.order' => 'required|integer|min:0',
        ])['items'];

        foreach ($items as $item) {
            Lesson::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Reordered successfully']);
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = Lesson::where('slug', $slug);
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
