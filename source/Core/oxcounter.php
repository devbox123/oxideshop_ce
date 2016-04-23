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
use OxidEsales\Eshop\Core\DatabaseAccessInterface;
use OxidEsales\Eshop\Core\DatabaseInterface;

/**
 * Counter class
 *
 */
class oxCounter implements DatabaseAccessInterface
{

    /**
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * Class constructor, sets active shop.
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    /**
     * Returns next counter value
     *
     * @param string $sIdent counter ident
     *
     * @return int
     */
    public function getNext($sIdent)
    {
        $this->database->startTransaction();

        $sQ = 'SELECT `oxcount` FROM `oxcounters` WHERE `oxident` = ? FOR UPDATE';

        if (($iCnt = $this->database->getOne($sQ, [$sIdent])) === false) {
            $sQ = "INSERT INTO `oxcounters` (`oxident`, `oxcount`) VALUES (?, '0')";
            $this->database->execute($sQ, [$sIdent]);
        }

        $iCnt = ((int) $iCnt) + 1;
        $sQ = 'UPDATE `oxcounters` SET `oxcount` = ? WHERE `oxident` = ?';
        $this->database->execute($sQ, [$iCnt, $sIdent]);
        $this->database->commitTransaction();

        return $iCnt;
    }

    /**
     * update counter value, only when it is greater than old one,
     * if counter ident not exist creates counter and sets value
     *
     * @param string  $sIdent counter ident
     * @param integer $iCount value
     *
     * @return int
     */
    public function update($sIdent, $iCount)
    {
        $this->database->startTransaction();

        $sQ = "SELECT `oxcount` FROM `oxcounters` WHERE `oxident` = ? FOR UPDATE";

        if (($iCnt = $this->database->getOne($sQ, [$sIdent])) === false) {
            $sQ = "INSERT INTO `oxcounters` (`oxident`, `oxcount`) VALUES (?, ?)";
            $blResult = $this->database->execute($sQ, [$sIdent, $iCount]);
        } else {
            $sQ = "UPDATE `oxcounters` SET `oxcount` = ? WHERE `oxident` = ? AND `oxcount` < ?";
            $blResult = $this->database->execute($sQ, [$iCount, $sIdent, $iCount]);
        }

        $this->database->commitTransaction();

        return $blResult;
    }
}
