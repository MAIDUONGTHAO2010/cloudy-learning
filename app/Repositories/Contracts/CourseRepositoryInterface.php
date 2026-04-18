<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

interface CourseRepositoryInterface extends RepositoryInterface
{
    public function getAllWithRelations();

    public function getPaginatedWithRelations();

    public function getPublicPaginated(array $filters = [], int $perPage = 12);

    public function getPopular(int $limit = 8);

    public function getNewest(int $limit = 8);

    public function findBySlug(string $slug);

    public function getWithRelations(int $id);

    public function reorder(array $items): void;
}
