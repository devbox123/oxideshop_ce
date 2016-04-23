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
 * Manages object (users, discounts, deliveries...) assignment to groups.
 */
class oxObject2Group extends oxBase
{
    /**
     * Load the relation even if from other shop
     *
     * @var boolean
     */
    protected $_blDisableShopCheck = true;

    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'oxobject2group';

    protected $_sCoreTable = "oxobject2group";

    /**
     * Class constructor, initiates parent constructor (parent::oxBase()).
     */
    public function __construct($config, $database)
    {
        parent::__construct($config, $database);
        $this->oxobject2group__oxshopid = new oxField($this->config->getShopId(), oxField::T_RAW);
    }

    /**
     * Extends the default save method.
     * Saves only if this kind of entry do not exists.
     *
     * @return bool
     */
    public function save()
    {
        $sQ = "select 1 from oxobject2group where oxgroupsid = ? and oxobjectid = ?";

        // does not exist
        if (!$this->database->getOne($sQ, [$this->oxobject2group__oxgroupsid->value, $this->oxobject2group__oxobjectid->value])) {
            return parent::save();
        }
    }
}
