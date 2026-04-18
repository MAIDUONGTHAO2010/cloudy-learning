<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CourseReviewService;

class CourseReviewController extends Controller
{
    public function __construct(protected CourseReviewService $courseReviewService) {}

    public function index(int $courseId)
    {
        return response()->json($this->courseReviewService->listByCourse($courseId));
    }
}
