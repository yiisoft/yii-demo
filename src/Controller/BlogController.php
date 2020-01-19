<?php
namespace App\Controller;

use App\Controller;
use App\Entity\Post;
use App\Entity\Tag;
use App\Repository\PostRepository;
use App\StdoutQueryLogger;
use Cycle\ORM\ORMInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
    ): Response
    {
        /** @var PostRepository $postRepo */
        $postRepo = $orm->getRepository(Post::class);
        $tagRepo = $orm->getRepository(Tag::class);

        $pageNum = (int)$request->getAttribute('page', 1);
        $year = $request->getAttribute('year', null);
        $month = $request->getAttribute('month', null);
        $isArchive = $year !== null && $month !== null;

        $paginator = $isArchive
            ? $postRepo->findArchivedPublic($year, $month)
                       ->withTokenGenerator(fn($page) => $urlGenerator->generate(
                           'blog/archive',
                           ['year' => $year, 'month' => $month, 'page' => $page]
                       ))
            : $postRepo->findLastPublic()
                       ->withTokenGenerator(fn ($page) => $urlGenerator->generate('blog/index', ['page' => $page]));

        $paginator = $paginator
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        $data = [
            'paginator' => $paginator,
            'archive' => $postRepo->getArchive(),
            'tags' => $tagRepo->getTagMentions(self::POPULAR_TAGS_COUNT),
        ];
        $output = $this->render('index', $data);

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($output);
        return $response;
    }

    public function page(Request $request, ORMInterface $orm, StdoutQueryLogger $logger): Response
    {
        $logger->display();

        $postRepo = $orm->getRepository(Post::class);
        $slug = $request->getAttribute('slug', null);

        $item = $postRepo->fullPostPage($slug, $this->user->getId());
        if ($item === null) {
            return $this->responseFactory->createResponse(404);
        }

        $data = [
            'item' => $item,
        ];
        $output = $this->render('post', $data);

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($output);
        return $response;
    }

    public function tag(
        Request $request,
        ORMInterface $orm,
        UrlGeneratorInterface $urlGenerator
    ): Response
    {
        $tagRepo = $orm->getRepository(Tag::class);
        /** @var PostRepository $postRepo */
        $postRepo = $orm->getRepository(Post::class);
        $label = $request->getAttribute('label', null);
        $pageNum = (int)$request->getAttribute('page', 1);

        $item = $tagRepo->findByLabel($label);

        if ($item === null) {
            return $this->responseFactory->createResponse(404);
        }
        // preloading of posts
        $paginator = $postRepo
            ->findByTag($item->getId())
            ->withTokenGenerator(fn($page) => $urlGenerator->generate(
                'blog/tag',
                ['label' => $label, 'page' => $page]
            ))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        $data = [
            'item' => $item,
            'paginator' => $paginator,
        ];
        $output = $this->render('tag', $data);

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($output);
        return $response;
    }
}