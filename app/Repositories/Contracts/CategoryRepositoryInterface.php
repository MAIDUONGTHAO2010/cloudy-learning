<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface CategoryRepositoryInterface.
 */
interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function getCategoriesByAdmin();

    public function getChildrenByAdmin(int $parentId);

    public function getPaginatedByAdmin();

    public function getPublic();
}
