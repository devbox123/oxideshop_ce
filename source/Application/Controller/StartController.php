<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop CE
 */

namespace OxidEsales\Eshop\Application\Controller;

use OxidEsales\Eshop\Application\Model\Article\ArticleList\Action;
use OxidEsales\Eshop\Application\Model\Article\ArticleList\Newest;
use oxRegistry;
use oxUBase;

/**
 * Starting shop page.
 * Shop starter, manages starting visible articles, etc.
 */
class StartController extends oxUBase
{

    /**
     * List display type
     *
     * @var string
     */
    protected $_sListDisplayType = null;

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'page/shop/start.tpl';

    /**
     * Start page meta description CMS ident
     *
     * @var string
     */
    protected $_sMetaDescriptionIdent = 'oxstartmetadescription';

    /**
     * Start page meta keywords CMS ident
     *
     * @var string
     */
    protected $_sMetaKeywordsIdent = 'oxstartmetakeywords';

    /**
     * Are actions on
     *
     * @var bool
     */
    protected $_blLoadActions = null;

    /**
     * Top article list (OXTOPSTART)
     *
     * @var array
     */
    protected $_aTopArticleList = null;

    /**
     * Newest article list
     *
     * @var array
     */
    protected $_aNewArticleList = null;

    /**
     * First article (OXFIRSTSTART)
     *
     * @var object
     */
    protected $_oFirstArticle = null;

    /**
     * Category offer article (OXCATOFFER)
     *
     * @var object
     */
    protected $_oCatOfferArticle = null;

    /**
     * Category offer article list (OXCATOFFER)
     *
     * @var array
     */
    protected $_oCatOfferArtList = null;

    /**
     * Tag cloud
     *
     * @deprecated v5.3 (2016-05-04); Tags will be moved to own module.
     *             
     * @var array
     */
    protected $_sTagCloud = null;

    /**
     * Sign if to load and show top5articles action
     *
     * @var bool
     */
    protected $_blTop5Action = true;

    /**
     * Sign if to load and show bargain action
     *
     * @var bool
     */
    protected $_blBargainAction = true;

    /**
     * Executes parent::render(), loads action articles
     * (oxarticlelist::loadActionArticles()). Returns name of
     * template file to render.
     *
     * @return  string  cuurent template file name
     */
    public function render()
    {

        if ($this->request->getRequestParameter('showexceptionpage') == '1') {
            return 'message/exception.tpl';
        }

        $myConfig = $this->config;

        $oRss = oxNew('oxrssfeed');
        if ($myConfig->getConfigParam('iTop5Mode') && $myConfig->getConfigParam('bl_rssTopShop')) {
            $this->addRssFeed($oRss->getTopInShopTitle(), $oRss->getTopInShopUrl(), 'topArticles');
        }
        if ($myConfig->getConfigParam('iNewestArticlesMode') && $myConfig->getConfigParam('bl_rssNewest')) {
            $this->addRssFeed($oRss->getNewestArticlesTitle(), $oRss->getNewestArticlesUrl(), 'newestArticles');
        }
        if ($myConfig->getConfigParam('bl_rssBargain')) {
            $this->addRssFeed($oRss->getBargainTitle(), $oRss->getBargainUrl(), 'bargainArticles');
        }

        parent::render();

        return $this->_sThisTemplate;
    }

    /**
     * Returns current view meta data
     * If $sMeta parameter comes empty, sets to it article title and description.
     * It happens if current view has no meta data defined in oxcontent table
     *
     * @param string $sMeta     category path
     * @param int    $iLength   max length of result, -1 for no truncation
     * @param bool   $blDescTag if true - performs additional duplicate cleaning
     *
     * @return string
     */
    protected function _prepareMetaDescription($sMeta, $iLength = 1024, $blDescTag = false)
    {
        if (!$sMeta &&
            $this->config->getConfigParam('bl_perfLoadAktion') &&
            $oArt = $this->getFirstArticle()
        ) {
            $oDescField = $oArt->getLongDescription();
            $sMeta = $oArt->oxarticles__oxtitle->value . ' - ' . $oDescField->value;
        }

        return parent::_prepareMetaDescription($sMeta, $iLength, $blDescTag);
    }

    /**
     * Returns current view keywords seperated by comma
     * If $sKeywords parameter comes empty, sets to it article title and description.
     * It happens if current view has no meta data defined in oxcontent table
     *
     * @param string $sKeywords               data to use as keywords
     * @param bool   $blRemoveDuplicatedWords remove duplicated words
     *
     * @return string
     */
    protected function _prepareMetaKeyword($sKeywords, $blRemoveDuplicatedWords = true)
    {
        if (!$sKeywords &&
            $this->config->getConfigParam('bl_perfLoadAktion') &&
            $oArt = $this->getFirstArticle()
        ) {
            $oDescField = $oArt->getLongDescription();
            $sKeywords = $oDescField->value;
        }

        return parent::_prepareMetaKeyword($sKeywords, $blRemoveDuplicatedWords);
    }

    /**
     * Template variable getter. Returns if actions are ON
     *
     * @return string
     */
    protected function _getLoadActionsParam()
    {
        if ($this->_blLoadActions === null) {
            $this->_blLoadActions = false;
            if ($this->config->getConfigParam('bl_perfLoadAktion')) {
                $this->_blLoadActions = true;
            }
        }

        return $this->_blLoadActions;
    }

    /**
     * Template variable getter. Returns start page articles (OXSTART)
     *
     * @return array
     */
    public function getArticleList()
    {
        return (new Action())->getById('OXSTART');
    }

    /**
     * Template variable getter. Returns Top article list (OXTOPSTART)
     *
     * @return array
     */
    public function getTopArticleList()
    {
        return (new Action())->getById('OXTOPSTART');
    }


    /**
     * Template variable getter. Returns newest article list
     *
     * @return array
     */
    public function getNewestArticles()
    {
        if ($this->_aNewArticleList === null) {
            $this->_aNewArticleList = array();
            if ($this->_getLoadActionsParam()) {
                // newest articles
                $list = oxNew(Newest::class)->getAll();
                if (count($list)) {
                    $this->_aNewArticleList = $list;
                }
            }
        }

        return $this->_aNewArticleList;
    }

    /**
     * Template variable getter. Returns first article
     *
     * @return object
     */
    public function getFirstArticle()
    {
        return oxNew(Action::class)->getById('OXFIRSTSTART');
    }

    /**
     * Template variable getter. Returns category offer article (OXCATOFFER)
     *
     * @return object
     */
    public function getCatOfferArticle()
    {
        if ($this->_oCatOfferArticle === null) {
            $this->_oCatOfferArticle = false;
            if ($oArtList = $this->getCatOfferArticleList()) {
                $this->_oCatOfferArticle = current($oArtList);
            }
        }

        return $this->_oCatOfferArticle;
    }

    /**
     * Template variable getter. Returns category offer article list (OXCATOFFER)
     *
     * @return array
     */
    public function getCatOfferArticleList()
    {
        return oxNew(Action::class)->getById('OXCATOFFER');
    }

    /**
     * Returns SEO suffix for page title
     *
     * @return string
     */
    public function getTitleSuffix()
    {
        return $this->config->getActiveShop()->oxshops__oxstarttitle->value;
    }

    /**
     * Returns view canonical url
     *
     * @return string
     */
    public function getCanonicalUrl()
    {
        if (oxRegistry::getUtils()->seoIsActive() && ($oViewConf = $this->getViewConfig())) {
            return oxRegistry::get("oxUtilsUrl")->prepareCanonicalUrl($oViewConf->getHomeLink());
        }
    }


    /**
     * Returns active banner list
     *
     * @return objects
     */
    public function getBanners()
    {

        $oBannerList = null;

        if ($this->config->getConfigParam('bl_perfLoadAktion')) {
            $oBannerList = oxNew('oxActionList');
            $oBannerList->loadBanners();
        }

        return $oBannerList;
    }

    /**
     * Returns manufacturer list for manufacturer slider
     *
     * @return objects
     */
    public function getManufacturerForSlider()
    {

        $oList = null;

        if ($this->config->getConfigParam('bl_perfLoadAktion')) {
            $oList = $this->getManufacturerlist();
        }

        return $oList;
    }
}
