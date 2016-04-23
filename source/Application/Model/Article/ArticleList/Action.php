<?php
namespace OxidEsales\Eshop\Application\Model\Article\ArticleList;

use OxidEsales\Eshop\Application\Model\Article\ListArticle;

class Action extends AbstractList
{
    public function getById($sActionID)
    {
        $article = oxNew(ListArticle::class);
        $iLimit = 5;

        $sShopID = \oxRegistry::getConfig()->getShopId();

        //echo $sSelect;
        $sArticleTable = $article->getViewName();

        $oBase = oxNew("oxActions");
        $sActiveSql = $oBase->getSqlActiveSnippet();
        $sViewName = $oBase->getViewName();

        $sLimit = ($iLimit > 0) ? "limit " . $iLimit : '';

        $sSelect = "select $sArticleTable.oxid from oxactions2article
                      left join $sArticleTable on $sArticleTable.oxid = oxactions2article.oxartid
                      left join $sViewName on $sViewName.oxid = oxactions2article.oxactionid
                      where oxactions2article.oxshopid = ? and oxactions2article.oxactionid = ? and $sActiveSql
                      and $sArticleTable.oxid is not null and " . $article->getSqlActiveSnippet() . "
                      order by oxactions2article.oxsort $sLimit";

        $ids = $this->database->getAll($sSelect, [$sShopID, $sActionID]);

        return $this->yieldByIds($ids);
    }
}