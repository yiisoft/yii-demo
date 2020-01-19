<?php

namespace App\Repository;

use App\Constrain\CommentDefault;
use App\CycleDataPaginator;
use App\DataPaginatorInterface;
use App\Entity\Post;
use Cycle\ORM\Select;
use Spiral\Database\Injection\Fragment;

class PostRepository extends Select\Repository
{
    public function findLastPublic(): DataPaginatorInterface
    {
        $query = $this->select()
                ->load(['user', 'tags'])
                ->orderBy('published_at', 'DESC');
        return new CycleDataPaginator($query);
    }

    public function findArchivedPublic(int $year, int $month): DataPaginatorInterface
    {
        $begin = (new \DateTimeImmutable)->setDate($year, $month, 1)->setTime(0, 0, 0);
        $end = $begin->setDate($year, $month + 1, 1)->setTime(0, 0, -1);

        $query = $this->select()
                    ->andWhere('published_at', 'between', $begin, $end)
                    ->orderBy('published_at', 'DESC')
                    ->load(['user', 'tags']);
        return new CycleDataPaginator($query);
    }

    public function findByTag($tagId): DataPaginatorInterface
    {
        $query = $this->select()
                    ->where(['tags.id' => $tagId])
                    ->orderBy('published_at', 'DESC')
                    ->load(['user']);
        return new CycleDataPaginator($query);
    }

    public function fullPostPage(string $slug, ?string $userId = null): ?Post
    {
        $query = $this->select()
                      ->where(['slug' => $slug])
                      ->load('user', [
                          'method' => Select::SINGLE_QUERY,
                      ])
                      ->load('tags', [
                          'method' => Select::OUTER_QUERY,
                      ])
                      // ->load('comments.user') // eager loading
                      ->load('comments', [
                          'method' => Select::OUTER_QUERY,
                          // not works:
                          'load' => new CommentDefault($userId === null ? null : ['user_id' => $userId]),
                      ]);
        /** @var null|Post $post */
        $post = $query->fetchOne();
        // /** @var Select\Repository $commentRepo */
        // $commentRepo = $this->orm->getRepository(Comment::class);
        // $commentRepo->select()->load('user')->where('post_id', $post->getId())->fetchAll();
        return $post;
    }
    /**
     * @return array Array of Array('Count' => '123', 'Month' => '8', 'Year' => '2019')
     */
    public function getArchive(): array
    {
        return $this->select()
            ->buildQuery()
            ->columns([
                'count(post.id) count',
                new Fragment('extract(month from post.published_at) month'),
                new Fragment('extract(year from post.published_at) year'),
            ])
            ->orderBy(new Fragment('year'), 'DESC')
            ->orderBy(new Fragment('month'), 'DESC')
            ->groupBy(new Fragment('year, month'))
            ->run()->fetchAll();
    }
}