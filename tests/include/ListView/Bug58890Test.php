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


require_once('include/ListView/ListViewData.php');

/**
 * Bug #58890
 * ListView Does Not Retain Sort Order
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58890
 */
class Bug58890Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts order by value
     *
     * @group 58890
     * @return void
     */
    public function testOrderBy()
    {
        $bean = new SugarBean58890();
        $listViewData = new ListViewData();
        $listViewData->listviewName = $bean->module_name;
        $listViewData->setVariableName($bean->object_name, '', $listViewData->listviewName);
        if (!empty($listViewData->var_order_by) && !empty($_SESSION[$listViewData->var_order_by])) {
            unset($_SESSION[$listViewData->var_order_by]);
        }

        $listViewData->getListViewData($bean, '', -1, -1, array('name' => array()));
        $this->assertEquals('date_entered DESC', $bean->orderByString58890, 'Order by date_entered DESC should be used');

        $GLOBALS['current_user']->setPreference('listviewOrder', array(
            'orderBy' => 'name',
            'sortOrder' => 'ASC'
        ), 0, $listViewData->var_name);

        $listViewData->getListViewData($bean, '', -1, -1, array('name' => array()));
        $this->assertEquals('name ASC', $bean->orderByString58890, 'User\'s preference should be used');
    }
}

class SugarBean58890 extends Account
{
    /**
     * @var string
     */
    public $orderByString58890 = '';

    public function create_new_list_query($order_by, $where, $filter = array(), $params = array(), $show_deleted = 0, $join_type = '', $return_array = false, $parentbean = null, $singleSelect = false, $ifListForExport = false)
    {
        $this->orderByString58890 = $order_by;
        return parent::create_new_list_query($order_by, $where, $filter, $params, $show_deleted, $join_type, $return_array, $parentbean, $singleSelect, $ifListForExport);
    }
}
