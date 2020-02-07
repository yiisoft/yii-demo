<?php

/**
 * @var \Yiisoft\Data\Reader\DataReaderInterface|string[][] $archive
 * @var \Yiisoft\Data\Reader\DataReaderInterface|string[][] $tags
 * @var \App\Pagination\PaginationSet $paginationSet;
 * @var \Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var \Yiisoft\View\WebView $this
 */

use App\Blog\Entity\Post;
use Yiisoft\Html\Html;

?>
<h1>Blog</h1>
<div class="row">
    <div class="col-sm-8 col-md-8 col-lg-9">
        <?php
        $pageSize = $paginationSet->getPaginator()->getCurrentPageSize();
        if ($pageSize > 0) {
            echo Html::tag(
                'p',
                sprintf('Showing %s out of %s posts', $pageSize, $paginationSet->getPaginator()->getTotalItems()),
                ['class' => 'text-muted']
            );
        } else {
            echo Html::tag('p', 'No records');
        }
        /** @var Post $item */
        foreach ($paginationSet->getPaginator()->read() as $item) {
            $url = $urlGenerator->generate('blog/page', ['slug' => $item->getSlug()]);

            echo Html::beginTag('div', ['class' => 'card mb-4']);
            echo Html::beginTag('div', ['class' => 'card-body d-flex flex-column align-items-start']);

            echo Html::a(
                Html::encode($item->getTitle()),
                $url,
                ['class' => 'mb-0 h4 text-decoration-none'] // stretched-link
            );
            echo Html::tag(
                'div',
                $item->getPublishedAt()->format('M, d') . ' by ' . Html::a(
                    Html::encode($item->getUser()->getLogin()),
                    $urlGenerator->generate('user/profile', ['login' => $item->getUser()->getLogin()])
                ),
                ['class' => 'mb-1 text-muted']
            );
            echo Html::tag(
                'p',
                Html::encode(mb_substr($item->getContent(), 0, 400))
                    . (mb_strlen($item->getContent()) > 400 ? '…' : ''),
                ['class' => 'card-text mb-auto']
            );
            if ($item->getTags()->count()) {
                echo Html::beginTag('div', ['class' => 'mt-3']);
                foreach ($item->getTags() as $tag) {
                    echo Html::a(
                        Html::encode($tag->getLabel()),
                        $urlGenerator->generate('blog/tag', ['label' => $tag->getLabel()]),
                        ['class' => 'btn btn-outline-secondary btn-sm mx-1 mt-1']
                    );
                }
                echo Html::endTag('div');
            }

            echo Html::endTag('div'); # .card-body
            echo Html::endTag('div'); # .card
        }
        if ($paginationSet->needToPaginate()) {
            echo $this->render('_pagination', ['paginationSet' => $paginationSet]);
        }
        ?>
    </div>
    <div class="col-sm-4 col-md-4 col-lg-3">
        <?php echo $this->render('_topTags', ['tags' => $tags]) ?>
        <?php echo $this->render('_archive', ['archive' => $archive]) ?>
    </div>
</div>