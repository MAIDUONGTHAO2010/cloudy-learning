<?php

use App\Enums\Question\AnswerType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->tinyInteger('answer_type')
                ->default(AnswerType::SINGLE)
                ->after('content')
                ->comment('1 = single, 2 = multiple');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('answer_type');
        });
    }
};