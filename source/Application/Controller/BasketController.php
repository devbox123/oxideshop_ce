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

use oxArticle;
use OxidEsales\Eshop\Application\Command\AddToBasketCommand;
use OxidEsales\Eshop\Core\DiContainer;
use oxRegistry;
use oxList;
use oxBasketContentMarkGenerator;
use oxBasket;

/**
 * Current session shopping cart (basket item list).
 * Contains with user selected articles (with detail information), list of
 * similar products, top offer articles.
 * OXID eShop -> SHOPPING CART.
 */
class BasketController extends \oxUBase
{

    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'page/checkout/basket.tpl';

    /**
     * Order step marker
     *
     * @var bool
     */
    protected $_blIsOrderStep = true;

    /**
     * all basket articles
     *
     * @var object
     */
    protected $_oBasketArticles = null;

    /**
     * Similar List
     *
     * @var object
     */
    protected $_oSimilarList = null;

    /**
     * Recomm List
     *
     * @var object
     */
    protected $_oRecommList = null;

    /**
     * First basket product object. It is used to load
     * recommendation list info and similar product list
     *
     * @var oxArticle
     */
    protected $_oFirstBasketProduct = null;

    /**
     * Current view search engine indexing state
     *
     * @var int
     */
    protected $_iViewIndexState = VIEW_INDEXSTATE_NOINDEXNOFOLLOW;

    /**
     * Wrapping objects list
     *
     * @var oxList
     */
    protected $_oWrappings = null;

    /**
     * Card objects list
     *
     * @var oxList
     */
    protected $_oCards = null;

    /**
     * Array of id to form recommendation list.
     *
     * @var array
     */
    protected $_aSimilarRecommListIds = null;


    /**
     * Last call function name
     *
     * @var string
     */
    protected $_sLastCallFnc = null;


    /**
     * Parameters which are kept when redirecting after user
     * puts something to basket
     *
     * @var array
     */
    public $aRedirectParams = array('cnid', // category id
        'mnid', // manufacturer id
        'anid', // active article id
        'tpl', // spec. template
        'listtype', // list type
        'searchcnid', // search category
        'searchvendor', // search vendor
        'searchmanufacturer', // search manufacturer
        'searchtag', // search tag
        'searchrecomm', // search recomendation
        'recommid' // recomm. list id
    );

    /**
     * Executes parent::render(), creates list with basket articles
     * Returns name of template file basket::_sThisTemplate (for Search
     * engines return "content.tpl" template to avoid fake orders etc).
     *
     * @return  string   $this->_sThisTemplate  current template file name
     */
    public function render()
    {
        if ($this->config->getConfigParam('blPsBasketReservationEnabled')) {
            $this->session->getBasketReservations()->renewExpiration();
        }

        parent::render();

        return $this->_sThisTemplate;
    }

    /**
     * Return the current articles from the basket
     *
     * @return object | bool
     */
    public function getBasketArticles()
    {
        if ($this->_oBasketArticles === null) {
            $this->_oBasketArticles = false;

            // passing basket articles
            if ($oBasket = $this->session->getBasket()) {
                $this->_oBasketArticles = $oBasket->getBasketArticles();
            }
        }

        return $this->_oBasketArticles;
    }

    /**
     * return the basket articles
     *
     * @return object | bool
     */
    public function getFirstBasketProduct()
    {
        if ($this->_oFirstBasketProduct === null) {
            $this->_oFirstBasketProduct = false;

            $aBasketArticles = $this->getBasketArticles();
            if (is_array($aBasketArticles) && $oProduct = reset($aBasketArticles)) {
                $this->_oFirstBasketProduct = $oProduct;
            }
        }

        return $this->_oFirstBasketProduct;
    }

    /**
     * return the similar articles
     *
     * @return object | bool
     */
    public function getBasketSimilarList()
    {
        if ($this->_oSimilarList === null) {
            $this->_oSimilarList = false;

            // similar product info
            if ($oProduct = $this->getFirstBasketProduct()) {
                $this->_oSimilarList = $oProduct->getSimilarProducts();
            }
        }

        return $this->_oSimilarList;
    }

    /**
     * Return array of id to form recommend list.
     *
     * @return array
     */
    public function getSimilarRecommListIds()
    {
        if ($this->_aSimilarRecommListIds === null) {
            $this->_aSimilarRecommListIds = false;

            if ($oProduct = $this->getFirstBasketProduct()) {
                $this->_aSimilarRecommListIds = array($oProduct->getId());
            }
        }

        return $this->_aSimilarRecommListIds;
    }

    /**
     * return the Link back to shop
     *
     * @return bool
     */
    public function showBackToShop()
    {
        $iNewBasketItemMessage = $this->config->getConfigParam('iNewBasketItemMessage');
        $sBackToShop = $this->session->getVariable('_backtoshop');

        return ($iNewBasketItemMessage == 3 && $sBackToShop);
    }

    /**
     * Assigns voucher to current basket
     *
     * @return null
     */
    public function addVoucher()
    {
        if (!$this->getViewConfig()->getShowVouchers()) {
            return;
        }

        $oBasket = $this->session->getBasket();
        $oBasket->addVoucher($this->request->getRequestParameter('voucherNr'));
    }

    /**
     * Removes voucher from basket (calls oxBasket::removeVoucher())
     *
     * @return null
     */
    public function removeVoucher()
    {
        if (!$this->getViewConfig()->getShowVouchers()) {
            return;
        }

        $oBasket = $this->session->getBasket();
        $oBasket->removeVoucher($this->request->getRequestParameter('voucherId'));
    }

    /**
     * Redirects user back to previous part of shop (list, details, ...) from basket.
     * Used with option "Display Message when Product is added to Cart" set to "Open Basket"
     * ($myConfig->iNewBasketItemMessage == 3)
     *
     * @return string   $sBackLink  back link
     */
    public function backToShop()
    {
        if ($this->config->getConfigParam('iNewBasketItemMessage') == 3) {
            $oSession = $this->session;
            if ($sBackLink = $oSession->getVariable('_backtoshop')) {
                $oSession->deleteVariable('_backtoshop');

                return $sBackLink;
            }
        }
    }

    /**
     * Returns a name of the view variable containing the error/exception messages
     *
     * @return null
     */
    public function getErrorDestination()
    {
        return 'basket';
    }

    /**
     * Returns wrapping options availability state (TRUE/FALSE)
     *
     * @return bool
     */
    public function isWrapping()
    {
        if (!$this->getViewConfig()->getShowGiftWrapping()) {
            return false;
        }

        if ($this->_iWrapCnt === null) {
            $this->_iWrapCnt = 0;

            $oWrap = oxNew('oxwrapping');
            $this->_iWrapCnt += $oWrap->getWrappingCount('WRAP');
            $this->_iWrapCnt += $oWrap->getWrappingCount('CARD');
        }

        return (bool) $this->_iWrapCnt;
    }

    /**
     * Return basket wrappings list if available
     *
     * @return oxlist
     */
    public function getWrappingList()
    {
        if ($this->_oWrappings === null) {
            $this->_oWrappings = oxNew('oxlist');

            // load wrapping papers
            if ($this->getViewConfig()->getShowGiftWrapping()) {
                $this->_oWrappings = oxNew('oxwrapping')->getWrappingList('WRAP');
            }
        }

        return $this->_oWrappings;
    }

    /**
     * Returns greeting cards list if available
     *
     * @return oxlist
     */
    public function getCardList()
    {
        if ($this->_oCards === null) {
            $this->_oCards = oxNew('oxlist');

            // load gift cards
            if ($this->getViewConfig()->getShowGiftWrapping()) {
                $this->_oCards = oxNew('oxwrapping')->getWrappingList('CARD');
            }
        }

        return $this->_oCards;
    }

    /**
     * Updates wrapping data in session basket object
     * (oxSession::getBasket()) - adds wrapping info to
     * each article in basket (if possible). Plus adds
     * gift message and chosen card ( takes from GET/POST/session;
     * oBasket::giftmessage, oBasket::chosencard). Then sets
     * basket back to session (oxSession::setBasket()).
     */
    public function changeWrapping()
    {
        if ($this->getViewConfig()->getShowGiftWrapping()) {
            $oBasket = $this->session->getBasket();

            $this->_setWrappingInfo($oBasket, $this->request->getRequestParameter('wrapping'));

            $oBasket->setCardMessage($this->request->getRequestParameter('giftmessage'));
            $oBasket->setCardId($this->request->getRequestParameter('chosencard'));
            $oBasket->onUpdate();
        }
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        $aPaths = array();
        $aPath = array();

        $iBaseLanguage = oxRegistry::getLang()->getBaseLanguage();
        $aPath['title'] = oxRegistry::getLang()->translateString('CART', $iBaseLanguage, false);
        $aPath['link']  = $this->getLink();
        $aPaths[] = $aPath;

        return $aPaths;
    }

    /**
     * Method returns object with explanation marks for articles in basket.
     *
     * @return oxBasketContentMarkGenerator
     */
    public function getBasketContentMarkGenerator()
    {
        /** @var oxBasketContentMarkGenerator $oBasketContentMarkGenerator */
        $oBasketContentMarkGenerator = oxNew('oxBasketContentMarkGenerator', $this->session->getBasket());

        return $oBasketContentMarkGenerator;
    }

    /**
     * Sets basket wrapping
     *
     * @param oxBasket $oBasket
     * @param array    $aWrapping
     */
    protected function _setWrappingInfo($oBasket, $aWrapping)
    {
        if (is_array($aWrapping) && count($aWrapping)) {
            foreach ($oBasket->getContents() as $sKey => $oBasketItem) {
                if (isset($aWrapping[$sKey])) {
                    $oBasketItem->setWrapping($aWrapping[$sKey]);
                }
            }
        }
    }




    /////////////

    /**
     * Basket content update controller.
     * Before adding article - check if client is not a search engine. If
     * yes - exits method by returning false. If no - executes
     * oxcmp_basket::_addItems() and puts article to basket.
     * Returns position where to redirect user browser.
     *
     * @param string $sProductId Product ID (default null)
     * @param double $dAmount    Product amount (default null)
     * @param array  $aSel       (default null)
     * @param array  $aPersParam (default null)
     * @param bool   $blOverride If true amount in basket is replaced by $dAmount otherwise amount is increased by $dAmount (default false)
     *
     * @return mixed
     */
    public function toBasket($sProductId = null, $dAmount = null, $aSel = null, $aPersParam = null, $blOverride = false)
    {
        // adding to basket is not allowed ?
        $myConfig = $this->config;
        if (oxRegistry::getUtils()->isSearchEngine()) {
            return;
        }

        // adding articles
        if ($aProducts = $this->_getItems($sProductId, $dAmount, $aSel, $aPersParam, $blOverride)) {

            $bus = DiContainer::getInstance()->get(DiContainer::CONTAINER_CORE_COMMAND_BUS);

            /* @var \oxBasketItem $oBasketItem */
            $oBasketItem = $bus->handle(
                new AddToBasketCommand($aProducts, $this->getBasket())
            );

            $this->_setLastCallFnc('tobasket');

            // new basket item marker
            if ($oBasketItem && $myConfig->getConfigParam('iNewBasketItemMessage') != 0) {
                $oNewItem = new \stdClass();
                $oNewItem->sTitle = $oBasketItem->getTitle();
                $oNewItem->sId = $oBasketItem->getProductId();
                $oNewItem->dAmount = $oBasketItem->getAmount();
                $oNewItem->dBundledAmount = $oBasketItem->getdBundledAmount();

                // passing article
                $this->session->setVariable('_newitem', $oNewItem);
            }

            // redirect to basket
            return $this->_getRedirectUrl();
        }
    }

    /**
     * Similar to tobasket, except that as product id "bindex" parameter is (can be) taken
     *
     * @param string $sProductId Product ID (default null)
     * @param double $dAmount    Product amount (default null)
     * @param array  $aSel       (default null)
     * @param array  $aPersParam (default null)
     * @param bool   $blOverride If true means increase amount of chosen article (default false)
     *
     * @return mixed
     */
    public function changebasket(
        $sProductId = null,
        $dAmount = null,
        $aSel = null,
        $aPersParam = null,
        $blOverride = true
    ) {
        // adding to basket is not allowed ?
        if (oxRegistry::getUtils()->isSearchEngine()) {
            return;
        }

        // fetching item ID
        if (!$sProductId) {
            $sBasketItemId = $this->request->getRequestParameter('bindex');

            if ($sBasketItemId) {
                $oBasket = $this->getBasket();
                //take params
                $aBasketContents = $oBasket->getContents();

                /* @var \oxBasketItem $oItem */
                $oItem = $aBasketContents[$sBasketItemId];

                $sProductId = isset($oItem) ? $oItem->getProductId() : null;
            } else {
                $sProductId = $this->request->getRequestParameter('aid');
            }
        }

        // fetching other needed info
        $dAmount = isset($dAmount) ? $dAmount : $this->request->getRequestParameter('am');
        $aSel = isset($aSel) ? $aSel : $this->request->getRequestParameter('sel');
        $aPersParam = $aPersParam ? : $this->request->getRequestParameter('persparam');

        // adding articles
        if ($aProducts = $this->_getItems($sProductId, $dAmount, $aSel, $aPersParam, $blOverride)) {
            $this->_setLastCallFnc('changebasket');
            $bus = DiContainer::getInstance()->get(DiContainer::CONTAINER_CORE_COMMAND_BUS);
            $bus->handle(
                new AddToBasketCommand($aProducts, $this->getBasket())
            );

        }

    }



    /**
     * @return oxbasket
     */
    protected function getBasket()
    {
        return $this->session->getBasket();

        /* #var \oxbasket $basket */
        $basket = oxNew('oxbasket');
        $basket->load($this->session->getId());
        $basket->setId($this->session->getId());

        return $basket;
    }




    /**
     * Formats and returns redirect URL where shop must be redirected after
     * storing something to basket
     *
     * @return string   $sClass.$sPosition  redirection URL
     */
    protected function _getRedirectUrl()
    {

        // active class
        $sClass = $this->request->getRequestParameter('cl');
        $sClass = $sClass ? $sClass . '?' : 'start?';
        $sPosition = '';

        // setting redirect parameters
        foreach ($this->aRedirectParams as $sParamName) {
            $sParamVal = $this->request->getRequestParameter($sParamName);
            $sPosition .= $sParamVal ? $sParamName . '=' . $sParamVal . '&' : '';
        }

        // special treatment
        // search param
        $sParam = rawurlencode($this->request->getRequestParameter('searchparam', true));
        $sPosition .= $sParam ? 'searchparam=' . $sParam . '&' : '';

        // current page number
        $iPageNr = (int) $this->request->getRequestParameter('pgNr');
        $sPosition .= ($iPageNr > 0) ? 'pgNr=' . $iPageNr . '&' : '';

        // reload and backbutton blocker
        if ($this->config->getConfigParam('iNewBasketItemMessage') == 3) {

            // saving return to shop link to session
            $this->session->setVariable('_backtoshop', $sClass . $sPosition);

            // redirecting to basket
            $sClass = 'basket?';
        }

        return $sClass . $sPosition;
    }



    /**
     * Collects and returns array of items to add to basket. Product info is taken not only from
     * given parameters, but additionally from request 'aproducts' parameter
     *
     * @param string $sProductId product ID
     * @param double $dAmount    product amount
     * @param array  $aSel       product select lists
     * @param array  $aPersParam product persistent parameters
     * @param bool   $blOverride amount override status
     *
     * @return mixed
     */
    protected function _getItems(
        $sProductId = null,
        $dAmount = null,
        $aSel = null,
        $aPersParam = null,
        $blOverride = false
    ) {
        // collecting items to add
        $aProducts = $this->request->getRequestParameter('aproducts');

        // collecting specified item
        $sProductId = $sProductId ? : $this->request->getRequestParameter('aid');
        if ($sProductId) {

            // additionally fetching current product info
            $dAmount = isset($dAmount) ? $dAmount : $this->request->getRequestParameter('am');

            // select lists
            $aSel = isset($aSel) ? $aSel : $this->request->getRequestParameter('sel');

            // persistent parameters
            if (empty($aPersParam)) {
                $aPersParam = $this->getPersistedParameters();
            }

            $sBasketItemId = $this->request->getRequestParameter('bindex');

            $aProducts[$sProductId] = array('am'           => $dAmount,
                'sel'          => $aSel,
                'persparam'    => $aPersParam,
                'override'     => $blOverride,
                'basketitemid' => $sBasketItemId
            );
        }

        if (is_array($aProducts) && count($aProducts)) {

            if ($this->request->getRequestParameter('removeBtn') !== null) {
                //setting amount to 0 if removing article from basket
                foreach ($aProducts as $sProductId => $aProduct) {
                    if (isset($aProduct['remove']) && $aProduct['remove']) {
                        $aProducts[$sProductId]['am'] = 0;
                    } else {
                        unset ($aProducts[$sProductId]);
                    }
                }
            }

            return $aProducts;
        }

        return false;
    }



    /**
     * Setting last call data to session (data used by econda)
     *
     * @param string $sCallName    name of action ('tobasket', 'changebasket')
     * @param array  $aProductInfo data which comes from request when you press button "to basket"
     * @param array  $aBasketInfo  array returned by oxbasket::getBasketSummary()
     */
    protected function _setLastCall($sCallName, $aProductInfo, $aBasketInfo)
    {
        $this->session->setVariable('aLastcall', array($sCallName => $aProductInfo));
    }

    /**
     * Setting last call function name (data used by econda)
     *
     * @param string $sCallName name of action ('tobasket', 'changebasket')
     */
    protected function _setLastCallFnc($sCallName)
    {
        $this->_sLastCallFnc = $sCallName;
    }

    /**
     * Getting last call function name (data used by econda)
     *
     * @return string
     */
    protected function _getLastCallFnc()
    {
        return $this->_sLastCallFnc;
    }



    /**
     * Cleans and returns persisted parameters.
     *
     * @param array $persistedParameters key-value parameters (optional). If not passed - takes parameters from request.
     *
     * @return array|null cleaned up parameters or null, if there are no non-empty parameters
     */
    protected function getPersistedParameters($persistedParameters = null)
    {
        $persistedParameters = ($persistedParameters ?: $this->request->getRequestParameter('persparam'));
        if (!is_array($persistedParameters)) {
            return null;
        }
        return array_filter($persistedParameters, 'trim') ?: null;
    }
}
