<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


/**
 * @ticket 63490
 */
class Bug63490Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarBean
     */
    private static $bean;

    public static function setUpBeforeClass()
    {
        self::$bean = new SugarBean();
        self::$bean->table_name = 'bean';
        self::$bean->field_defs = array(
            'name' => array(),
        );
    }

    /**
     * @param string $raw
     * @param string $expected
     * @param bool $suppress_table_name
     *
     * @dataProvider provider
     */
    public function testProcessOrderBy($raw, $expected, $suppress_table_name = false)
    {
        $actual = self::$bean->process_order_by($raw, null, $suppress_table_name);
        $this->assertEquals($expected, $actual);
    }

    public static function provider()
    {
        return array(
            // existing field is accepted
            array('name', 'bean.name'),
            // valid order is accepted
            array('name asc', 'bean.name asc'),
            // order is case-insensitive
            array('name DeSc', 'bean.name DeSc'),
            // any white spaces are accepted
            array("\tname\t\nASC\n\r", 'bean.name ASC'),
            // invalid order is ignored
            array('name somehow', 'bean.name'),
            // everything after the first white space considered order
            array('name desc asc', 'bean.name'),
            // non-existing field is removed
            array('title', ''),
            // non-existing field is removed together with order
            array('title asc', ''),
            // field name containing table name is removed
            array('bean.name', ''),
            // $suppress_table_name usage
            array('name', 'name', true),
        );
    }
}
