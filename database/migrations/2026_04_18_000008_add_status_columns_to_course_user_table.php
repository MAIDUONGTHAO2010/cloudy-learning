<?php

use App\Enums\Course\EnrollmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(EnrollmentStatus::REQUEST)->after('user_id');
            $table->text('note')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('note');
            $table->timestamp('cancelled_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            $table->dropColumn(['status', 'note', 'approved_at', 'cancelled_at']);
        });
    }
};
