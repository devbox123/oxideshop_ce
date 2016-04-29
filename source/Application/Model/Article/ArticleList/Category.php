<?php
namespace OxidEsales\Eshop\Application\Model\Article\ArticleList;

use OxidEsales\Eshop\Application\Model\Article\ListArticle;

class Category extends AbstractList
{
    public function getById($sCatId)
    {
        $aSessionFilter = \oxRegistry::getSession()->getVariable('session_attrfilter');

        $article = oxNew(ListArticle::class);
        $sArticleFields = $article->getSelectFields();

        $sSelect = $this->_getCategorySelect($sArticleFields, $sCatId, $aSessionFilter);

        //if ($iLimit = (int) $iLimit) {
        //    $sSelect .= " LIMIT $iLimit";
        //}

        $ids = $this->database->getAll($sSelect);

        return $this->yieldByIds($ids);
    }

    public function getIds($sCatId)
    {
        $aSessionFilter = \oxRegistry::getSession()->getVariable('session_attrfilter');

        $article = oxNew(ListArticle::class);
        $sArticleFields = $article->getSelectFields();

        $sSelect = $this->_getCategorySelect($sArticleFields, $sCatId, $aSessionFilter);

        //if ($iLimit = (int) $iLimit) {
        //    $sSelect .= " LIMIT $iLimit";
        //}

        $ids = $this->database->getAll($sSelect);
        if ($ids->valid()) {
            return array_column($ids->current(), 'OXID');
        }
    }

    public function getCountById($sCatId)
    {
        $aSessionFilter = \oxRegistry::getSession()->getVariable('session_attrfilter');

        $article = oxNew(ListArticle::class);
        $sArticleFields = $article->getSelectFields();

        $sSelect = $this->_getCategorySelect($sArticleFields, $sCatId, $aSessionFilter);

        $sArticleTable = getViewName('oxarticles');
        $sSelect = str_replace("SELECT $sArticleTable.oxid FROM ", "SELECT count($sArticleTable.oxid) FROM ", $sSelect);

        //if ($iLimit = (int) $iLimit) {
        //    $sSelect .= " LIMIT $iLimit";
        //}

        return $this->database->getOne($sSelect);
    }

    /**
     * Creates SQL Statement to load Articles, etc.
     *
     * @param string $sFields        Fields which are loaded e.g. "oxid" or "*" etc.
     * @param string $sCatId         Category tree ID
     * @param array  $aSessionFilter Like array ( catid => array( attrid => value,...))
     *
     * @return string SQL
     */
    protected function _getCategorySelect($sFields, $sCatId, $aSessionFilter)
    {
        $sArticleTable = getViewName('oxarticles');
        $sO2CView = getViewName('oxobject2category');

        $article = oxNew(ListArticle::class);

        // ----------------------------------
        // sorting
        $sSorting = '';
        //if ($this->_sCustomSorting) {
        //    $sSorting = " {$this->_sCustomSorting} , ";
        //}

        // ----------------------------------
        // filtering ?
        $sFilterSql = '';
        $iLang = \oxRegistry::getLang()->getBaseLanguage();
        if ($aSessionFilter && isset($aSessionFilter[$sCatId][$iLang])) {
            $sFilterSql = $this->_getFilterSql($sCatId, $aSessionFilter[$sCatId][$iLang]);
        }

        $sSelect = "SELECT $sArticleTable.oxid FROM $sO2CView as oc left join $sArticleTable
                    ON $sArticleTable.oxid = oc.oxobjectid
                    WHERE " . $article->getSqlActiveSnippet() . " and $sArticleTable.oxparentid = ''
                    and oc.oxcatnid = " . $this->database->quote($sCatId) . " $sFilterSql ORDER BY $sSorting oc.oxpos, oc.oxobjectid ";

        return $sSelect;
    }

    /**
     * Returns filtered articles sql "oxid in (filtered ids)" part
     *
     * @param string $sCatId  category id
     * @param array  $aFilter filters for this category
     *
     * @return string
     */
    protected function _getFilterSql($sCatId, $aFilter)
    {
        $sArticleTable = getViewName('oxarticles');
        $aIds = $this->database->getAll($this->_getFilterIdsSql($sCatId, $aFilter));
        $sIds = '';

        if ($aIds) {
            foreach ($aIds as $aArt) {
                if ($sIds) {
                    $sIds .= ', ';
                }
                $sIds .= $this->database->quote(current($aArt));
            }

            if ($sIds) {
                $sFilterSql = " and $sArticleTable.oxid in ( $sIds ) ";
            }
            // bug fix #0001695: if no articles found return false
        } elseif (!(current($aFilter) == '' && count(array_unique($aFilter)) == 1)) {
            $sFilterSql = " and false ";
        }

        return $sFilterSql;
    }

    /**
     * Returns sql to fetch ids of articles fitting current filter
     *
     * @param string $sCatId  category id
     * @param array  $aFilter filters for this category
     *
     * @return string
     */
    protected function _getFilterIdsSql($sCatId, $aFilter)
    {
        $sO2CView = getViewName('oxobject2category');
        $sO2AView = getViewName('oxobject2attribute');

        $sFilter = '';
        $iCnt = 0;

        foreach ($aFilter as $sAttrId => $sValue) {
            if ($sValue) {
                if ($sFilter) {
                    $sFilter .= ' or ';
                }
                $sValue = $this->database->quote($sValue);
                $sAttrId = $this->database->quote($sAttrId);

                $sFilter .= "( oa.oxattrid = {$sAttrId} and oa.oxvalue = {$sValue} )";
                $iCnt++;
            }
        }
        if ($sFilter) {
            $sFilter = "WHERE $sFilter ";
        }

        $sFilterSelect = "select oc.oxobjectid as oxobjectid, count(*) as cnt from ";
        $sFilterSelect .= "(SELECT * FROM $sO2CView WHERE $sO2CView.oxcatnid = '$sCatId' GROUP BY $sO2CView.oxobjectid, $sO2CView.oxcatnid) as oc ";
        $sFilterSelect .= "INNER JOIN $sO2AView as oa ON ( oa.oxobjectid = oc.oxobjectid ) ";

        return $sFilterSelect . "{$sFilter} GROUP BY oa.oxobjectid HAVING cnt = $iCnt ";
    }
}