<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'rating' => 5,
                'comment' => 'Khóa học rất hay, giải thích rõ ràng và dễ hiểu. Tôi đã áp dụng ngay vào dự án thực tế!',
            ],
            [
                'rating' => 4,
                'comment' => 'Nội dung tốt, giảng viên nhiệt tình. Chỉ cần thêm vài bài tập thực hành nữa thì sẽ tuyệt hơn.',
            ],
            [
                'rating' => 5,
                'comment' => 'Xuất sắc! Tôi đã học rất nhiều điều hữu ích từ khóa học này.',
            ],
            [
                'rating' => 3,
                'comment' => 'Nội dung ổn nhưng tốc độ giảng hơi nhanh, khó theo kịp với người mới bắt đầu.',
            ],
            [
                'rating' => 4,
                'comment' => 'Khóa học bổ ích, tài liệu đầy đủ. Recommend cho ai muốn học nhanh.',
            ],
            [
                'rating' => 5,
                'comment' => 'Cực kỳ chuyên sâu và thực tế. Giảng viên giải thích từng bước rất rõ ràng.',
            ],
            [
                'rating' => 2,
                'comment' => 'Kỳ vọng cao hơn. Nội dung còn sơ sài, cần bổ sung thêm ví dụ thực tế.',
            ],
            [
                'rating' => 4,
                'comment' => 'Học xong thấy tự tin hơn hẳn. Cảm ơn giảng viên!',
            ],
        ];

        $courses = Course::all();
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($courses as $course) {
            // Mỗi course nhận 3-5 review ngẫu nhiên từ các user khác nhau
            $usedUserIds = [];
            $reviewSample = collect($reviews)->shuffle()->take(rand(3, 5));

            foreach ($reviewSample as $review) {
                // Chọn user chưa review course này
                $user = $users->whereNotIn('id', $usedUserIds)->first();

                if (! $user) {
                    break;
                }

                $usedUserIds[] = $user->id;

                CourseReview::create([
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                ]);
            }
        }
    }
}
