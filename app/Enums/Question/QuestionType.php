<?php

namespace App\Enums\Question;

use BenSampo\Enum\Enum;

final class QuestionType extends Enum
{
    const TEXT = 1;

    const IMAGE = 2;

    const AUDIO = 3;

    const VIDEO = 4;
}
