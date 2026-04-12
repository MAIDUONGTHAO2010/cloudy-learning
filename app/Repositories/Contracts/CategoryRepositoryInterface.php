<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface CategoryRepositoryInterface.
 *
 * @package namespace App\Contracts;
 */
interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function getCategoriesByAdmin();

    public function getChildrenByAdmin(int $parentId);
}
