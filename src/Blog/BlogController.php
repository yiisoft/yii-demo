<?php

namespace App\Blog;

use App\Controller;
use App\Blog\Entity\Post;
use App\Blog\Entity\Tag;
use App\Blog\Post\PostRepository;
use App\Blog\Tag\TagRepository;
use App\Pagination\PaginationSet;
use Cycle\ORM\ORMInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Router\UrlGeneratorInterface;

class BlogController extends Controller
{
    private const POSTS_PER_PAGE = 3;
    private const POPULAR_TAGS_COUNT = 10;

    protected function getId(): string
    {
        return 'blog';
    }

    public function index(
        Request $request,
        ORMInterface $orm,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        /** @var PostRepository $postRepo */
        $postRepo = $orm->getRepository(Post::class);
        /** @var TagRepository $postRepo */
        $tagRepo = $orm->getRepository(Tag::class);

        $pageNum = (int)$request->getAttribute('page', 1);
        $year = $request->getAttribute('year', null);
        $month = $request->getAttribute('month', null);
        $isArchive = $year !== null && $month !== null;

        if ($isArchive) {
            $dataReader = $postRepo->findArchivedPublic($year, $month);
            $pageUrlGenerator = fn ($page) => $urlGenerator->generate(
                'blog/archive',
                ['year' => $year, 'month' => $month, 'page' => $page]
            );
        } else {
            $dataReader = $postRepo->findAllPreloaded();
            $pageUrlGenerator = fn ($page) => $urlGenerator->generate('blog/index', ['page' => $page]);
        }

        $paginationSet = new PaginationSet(
            (new OffsetPaginator($dataReader))
                ->withPageSize(self::POSTS_PER_PAGE)
                ->withCurrentPage($pageNum),
            $pageUrlGenerator
        );

        $data = [
            'paginationSet' => $paginationSet,
            'archive' => $postRepo->getArchive()->withLimit(12),
            'tags' => $tagRepo->getTagMentions(self::POPULAR_TAGS_COUNT),
        ];
        $output = $this->render(__FUNCTION__, $data);

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($output);
        return $response;
    }
}