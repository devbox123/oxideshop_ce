<?php
namespace OxidEsales\Eshop\Application\Model\Article\ArticleList;

use OxidEsales\Eshop\Application\Model\Article\ListArticle;

class CrossSelling extends AbstractList
{
    public function getById($sArticleId)
    {
        $article = oxNew(ListArticle::class);

        $sArticleTable = $article->getViewName();

        $sSelect = "SELECT $sArticleTable.oxid FROM $sArticleTable INNER JOIN oxobject2article ON oxobject2article.oxobjectid=$sArticleTable.oxid ";
        $sSelect .= "WHERE oxobject2article.oxarticlenid = ?";
        $sSelect .= " AND " . $article->getSqlActiveSnippet();

        // #525 bidirectional cross selling
        /*if ($myConfig->getConfigParam('blBidirectCross')) {
            $sSelect = "
                (
                    SELECT $sArticleTable.* FROM $sArticleTable
                        INNER JOIN oxobject2article AS O2A1 on
                            ( O2A1.oxobjectid = $sArticleTable.oxid AND O2A1.oxarticlenid = $sArticleId )
                    WHERE 1
                    AND " . $oBaseObject->getSqlActiveSnippet() . "
                    AND ($sArticleTable.oxid != $sArticleId)
                )
                UNION
                (
                    SELECT $sArticleTable.* FROM $sArticleTable
                        INNER JOIN oxobject2article AS O2A2 ON
                            ( O2A2.oxarticlenid = $sArticleTable.oxid AND O2A2.oxobjectid = $sArticleId )
                    WHERE 1
                    AND " . $oBaseObject->getSqlActiveSnippet() . "
                    AND ($sArticleTable.oxid != $sArticleId)
                )";
        }*/

        $ids = $this->database->getAll($sSelect, [$sArticleId]);

        return $this->yieldByIds($ids);
    }
}