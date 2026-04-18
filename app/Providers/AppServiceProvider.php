<?php

namespace App\Providers;

use App\Repositories\CategoryRepositoryEloquent;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\CourseReviewRepositoryInterface;
use App\Repositories\Contracts\LessonRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\QuizRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\CourseRepositoryEloquent;
use App\Repositories\CourseReviewRepositoryEloquent;
use App\Repositories\LessonRepositoryEloquent;
use App\Repositories\NotificationRepositoryEloquent;
use App\Repositories\QuizRepositoryEloquent;
use App\Repositories\UserRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepositoryEloquent::class);
        $this->app->bind(CourseRepositoryInterface::class, CourseRepositoryEloquent::class);
        $this->app->bind(CourseReviewRepositoryInterface::class, CourseReviewRepositoryEloquent::class);
        $this->app->bind(LessonRepositoryInterface::class, LessonRepositoryEloquent::class);
        $this->app->bind(QuizRepositoryInterface::class, QuizRepositoryEloquent::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepositoryEloquent::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepositoryEloquent::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
