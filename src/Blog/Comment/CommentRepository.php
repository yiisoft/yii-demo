<?php

declare(strict_types=1);

namespace App\Blog\Comment;

use Cycle\ORM\Select;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class CommentRepository extends Select\Repository
{
    public function getReader(): DataReaderInterface
    {
        return (new EntityReader($this->select()))
            ->withSort($this->getSort());
    }

    private function getSort(): Sort
    {
        return (new Sort(['id']))->withOrder(['id' => 'asc']);
    }
}
