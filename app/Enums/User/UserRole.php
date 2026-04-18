<?php

namespace App\Enums\User;

use BenSampo\Enum\Enum;

final class UserRole extends Enum
{
    const STUDENT = 1;

    const INSTRUCTOR = 2;

    const ADMIN = 3;
}
