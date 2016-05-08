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

/**
 * Main shopping basket manager. Arranges shopping basket
 * contents, updates amounts, prices, taxes etc.
 *
 * @subpackage oxcmp
 */
class oxcmp_basket extends oxView
{

    /**
     * Marking object as component
     *
     * @var bool
     */
    protected $_blIsComponent = true;



    /**
     * Initiates component.
     */
    public function init()
    {
        $oConfig = $this->config;
        if ($oConfig->getConfigParam('blPsBasketReservationEnabled')) {
            if ($oReservations = $this->session->getBasketReservations()) {
                if (!$oReservations->getTimeLeft()) {
                    $oBasket = $this->session->getBasket();
                    if ($oBasket && $oBasket->getProductsCount()) {
                        $this->emptyBasket($oBasket);
                    }
                }
                $iLimit = (int) $oConfig->getConfigParam('iBasketReservationCleanPerRequest');
                if (!$iLimit) {
                    $iLimit = 200;
                }
                $oReservations->discardUnusedReservations($iLimit);
            }
        }

        parent::init();

        // Basket exclude
        if ($this->config->getConfigParam('blBasketExcludeEnabled')) {
            if ($oBasket = $this->session->getBasket()) {
                $this->getParent()->setRootCatChanged($this->isRootCatChanged() && $oBasket->getContents());
            }
        }
    }

    /**
     * Loads basket ($oBasket = $mySession->getBasket()), calls oBasket->calculateBasket,
     * executes parent::render() and returns basket object.
     *
     * @return object   $oBasket    basket object
     */
    public function render()
    {
        parent::render();
            return $this->getBasket();
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
     * Returns true if active root category was changed
     *
     * @return bool
     */
    public function isRootCatChanged()
    {
        // in Basket
        $oBasket = $this->getBasket();
        if ($oBasket->showCatChangeWarning()) {
            $oBasket->setCatChangeWarningState(false);

            return true;
        }

        // in Category, only then category is empty ant not equal to default category
        $sDefCat = $this->config->getActiveShop()->oxshops__oxdefcat->value;
        $sActCat = $this->request->getRequestParameter('cnid');
        $oActCat = oxnew('oxcategory');
        if ($sActCat && $sActCat != $sDefCat && $oActCat->load($sActCat)) {
            $sActRoot = $oActCat->oxcategories__oxrootid->value;
            if ($oBasket->getBasketRootCatId() && $sActRoot != $oBasket->getBasketRootCatId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Executes user choice:
     *
     * - if user clicked on "Proceed to checkout" - redirects to basket,
     * - if clicked "Continue shopping" - clear basket
     *
     * @return mixed
     */
    public function executeUserChoice()
    {
        // redirect to basket
        if ($this->request->getRequestParameter("tobasket")) {
            return "basket";
        } else {
            // clear basket
            $this->getBasket()->deleteBasket();
            $this->getParent()->setRootCatChanged(false);
        }
    }

    /**
     * Deletes user basket object from session and saved one from DB if needed.
     *
     * @param oxBasket $oBasket
     */
    protected function emptyBasket($oBasket)
    {
        $oBasket->deleteBasket();
    }
}
