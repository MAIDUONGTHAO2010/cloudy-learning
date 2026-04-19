<?php

namespace App\Enums\Course;

use BenSampo\Enum\Enum;

final class EnrollmentStatus extends Enum
{
    const REQUEST = 1;

    const APPROVED = 2;

    const CANCELED = 3;
}
