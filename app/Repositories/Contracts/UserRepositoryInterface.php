<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface UserRepositoryInterface.
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    public function getPaginated(array $filters);

    public function getStats(): array;

    public function getInstructors();

    public function getPublicInstructors();

    public function getTopInstructors(int $limit = 6);
}
