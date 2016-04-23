<?php
namespace OxidEsales\Eshop\Application\Model\Article\ArticleList;

use OxidEsales\Eshop\Application\Model\Article\ListArticle;

class Accessoires extends AbstractList
{
    public function getById($sArticleId)
    {
        $article = oxNew(ListArticle::class);
        $sArticleTable = $article->getViewName();

        $sSelect = "select $sArticleTable.oxid from oxaccessoire2article left join $sArticleTable on oxaccessoire2article.oxobjectid=$sArticleTable.oxid ";
        $sSelect .= "where oxaccessoire2article.oxarticlenid = ? ";
        $sSelect .= " and $sArticleTable.oxid is not null and " . $article->getSqlActiveSnippet();
        $sSelect .= " order by oxaccessoire2article.oxsort";

        $ids = $this->database->getAll($sSelect, [$sArticleId]);

        return $this->yieldByIds($ids);
    }
}