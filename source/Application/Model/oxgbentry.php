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
 * Guestbook entry object manager.
 * Loads available guestbook entries, performs some SQL queries.
 *
 */
class oxGbEntry extends oxBase
{

    /**
     * skipped fields
     *
     * @var array containing fields
     */
    //to skip oxcreate we must change this field to 'CURRENT_TIMESTAMP'
    //protected $_aSkipSaveFields = array( 'oxcreate' );

    /**
     * Current class name
     *
     * @var string classname
     */
    protected $_sClassName = 'oxgbentry';

    protected $_sCoreTable = "oxgbentries";

    /**
     * Calls parent::assign and assigns gb entry writer data
     *
     * @param array $dbRecord database record
     *
     * @return bool
     */
    public function assign($dbRecord)
    {
        $blRet = parent::assign($dbRecord);

        if (isset($this->oxgbentries__oxuserid) && $this->oxgbentries__oxuserid->value) {
            $this->oxuser__oxfname = new oxField(
                $this->database->getOne("select oxfname from oxuser where oxid=?", [$this->oxgbentries__oxuserid->value])
            );
        }

        return $blRet;
    }

    /**
     * Inserts new guestbook entry. Returns true on success.
     *
     * @return bool
     */
    protected function _insert()
    {
        // set oxcreate
        $this->oxgbentries__oxcreate = new oxField(date('Y-m-d H:i:s', oxRegistry::get("oxUtilsDate")->getTime()));

        return parent::_insert();
    }

    /**
     * Loads guestbook entries returns them.
     *
     * @param integer $iStart           start for sql limit
     * @param integer $iNrofCatArticles nr of items per page
     * @param string  $sSortBy          order by
     *
     * @return array $oEntries guestbook entries
     */
    public function getAllEntries($iStart, $iNrofCatArticles, $sSortBy)
    {
        $myConfig = $this->config;

        // loading entries
        $sSelect = 'select oxgbentries.*, oxuser.oxfname,
                    `oxuser`.`oxusername` AS `author`, `oxgbentries`.`oxcreate` AS `date`
            from oxgbentries left join oxuser on oxgbentries.oxuserid = oxuser.oxid ';
        $sSelect .= 'where oxuser.oxid is not null and oxgbentries.oxshopid = "' . $myConfig->getShopId() . '" ';

        // setting GB entry view restirction rules
        if ($myConfig->getConfigParam('blGBModerate')) {
            $oUser = $this->getUser();
            $sSelect .= " and ( oxgbentries.oxactive = '1' ";
            $sSelect .= $oUser ? " or oxgbentries.oxuserid = " . $this->database->quote($oUser->getId()) : '';
            $sSelect .= " ) ";
        }

        // setting sort
        if ($sSortBy) {
            $sSelect .= "order by $sSortBy ";
        }


        $oEntries = oxNew('oxlist');
        $oEntries->init('oxgbentry');

        $oEntries->setSqlLimit($iStart, $iNrofCatArticles);
        $oEntries->selectString($sSelect);

        return $oEntries;
    }

    /**
     * Returns count of all entries.
     *
     * @return integer $iRecCnt
     */
    public function getEntryCount()
    {
        $myConfig = $this->config;

        // loading entries
        $sSelect = 'select count(*) from oxgbentries left join oxuser on oxgbentries.oxuserid = oxuser.oxid ';
        $sSelect .= 'where oxuser.oxid is not null and oxgbentries.oxshopid = ? ';

        // setting GB entry view restirction rules
        if ($myConfig->getConfigParam('blGBModerate')) {
            $oUser = $this->getUser();
            $sSelect .= " and ( oxgbentries.oxactive = '1' ";
            $sSelect .= $oUser ? " or oxgbentries.oxuserid = ?";
            $sSelect .= " ) ";
        }

        // loading only if there is some data
        $iRecCnt = (int) $this->database->getOne($sSelect, [$myConfig->getShopId(), $oUser->getId()]);

        return $iRecCnt;
    }

    /**
     * Method protects from massive message flooding. Max number of
     * posts per day is limited in Admin next to max number of posts
     * per page.
     *
     * @param string $sShopid shop`s OXID
     * @param string $sUserId user`s OXID
     *
     * @return  bool    result
     */
    public function floodProtection($sShopid = 0, $sUserId = null)
    {
        $result = true;
        if ($sUserId && $sShopid) {
            $sToday = date('Y-m-d');
            $sSelect = "select count(*) from oxgbentries ";
            $sSelect .= "where oxgbentries.oxuserid = ? and oxgbentries.oxshopid = ? ";
            $sSelect .= "and oxgbentries.oxcreate >= '$sToday 00:00:00' and oxgbentries.oxcreate <= '$sToday 23:59:59' ";
            $iCnt = $this->database->getOne($sSelect, [$sUserId, $sShopid]);

            $myConfig = $this->config;
            if ((!$myConfig->getConfigParam('iMaxGBEntriesPerDay')) || ($iCnt < $myConfig->getConfigParam('iMaxGBEntriesPerDay'))) {
                $result = false;
            }
        }

        return $result;
    }
}
