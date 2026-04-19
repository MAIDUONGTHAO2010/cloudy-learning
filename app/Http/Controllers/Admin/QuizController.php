<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Quiz\UpdateQuizQuestionRequest;
use App\Services\QuizService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(protected QuizService $quizService) {}

    public function show(int $lessonId)
    {
        return response()->json($this->quizService->showByLesson($lessonId));
    }

    public function store(int $lessonId)
    {
        return response()->json($this->quizService->createForLesson($lessonId), 201);
    }

    public function destroy(int $quizId)
    {
        $this->quizService->deleteQuiz($quizId);

        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    public function addQuestion(int $quizId)
    {
        return response()->json($this->quizService->addQuestion($quizId), 201);
    }

    public function destroyQuestion(int $questionId)
    {
        $this->quizService->deleteQuestion($questionId);

        return response()->json(['message' => 'Question deleted successfully']);
    }

    public function updateQuestion(UpdateQuizQuestionRequest $request, int $questionId)
    {
        return response()->json($this->quizService->updateQuestion($questionId, $request->validated()));
    }

    public function presignMedia(Request $request)
    {
        return response()->json($this->quizService->presignMediaUpload($request->all()));
    }
}
