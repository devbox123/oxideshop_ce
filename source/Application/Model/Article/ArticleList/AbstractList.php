<?php
namespace OxidEsales\Eshop\Application\Model\Article\ArticleList;

use OxidEsales\Eshop\Application\Model\Article\ListArticle;
use OxidEsales\Eshop\Core\DatabaseAccessInterface;
use OxidEsales\Eshop\Core\DatabaseInterface;

class AbstractList implements DatabaseAccessInterface
{
    protected $database;
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    protected function yieldByIds($ids)
    {
        foreach ($ids as $id) {
            $article = oxNew(ListArticle::class);
            $article->load(current($id));
            yield $article;
        }
    }
}