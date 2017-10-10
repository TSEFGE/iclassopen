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


require_once('include/generic/LayoutManager.php');
require_once('include/generic/SugarWidgets/SugarWidgetReportField.php');

/**
 * Bug #57902
 * click Count on Calls report see message: Database failure. Please refer to sugarcrm.log for details.
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57902
 */
class Bug57902Test extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');


	}

	public static function tearDownAfterClass()
	{
        SugarTestHelper::tearDown();
	}

    public static function dataProvider()
    {
        return array(
            array(
                array(
                    'column_key' => 'self',
                    'group_function' => 'count',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self'
                ),
                'calls__count'
            ),
            array(
                array(
                    'column_function' => 'avg',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'avg',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int'
                ),
                'calls_avg_duration_hours'
            ),
            array(
                array(
                    'column_function' => 'max',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'max',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int'
                ),
                'calls_max_duration_hours'
            ),
            array(
                array(
                    'column_function' => 'min',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'min',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int'
                ),
                'calls_min_duration_hours'
            ),
            array(
                array(
                    'column_function' => 'sum',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'sum',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int'
                ),
                'calls_sum_duration_hours'
            )
        );

    }

    /**
     * Test asserts that for group functions order by is alias instead of table.field
     *
     * @dataProvider dataProvider
     * @group 57902
     * @return void
     */
    public function testQueryOrderBy($layout_def, $expected)
    {
        $layoutManager = new LayoutManager();
        $db = new stdClass();
        $db->db = $GLOBALS['db'];
        $layoutManager->setAttributePtr('reporter', $db);
        $sugarWidgetReportField = new SugarWidgetReportField($layoutManager);

        $actual = $sugarWidgetReportField->queryOrderBy($layout_def);

        $this->assertContains($expected, $actual, 'ORDER BY statement uses wrong field');
    }
}
