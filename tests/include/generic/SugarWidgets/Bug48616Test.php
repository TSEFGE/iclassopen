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


require_once "include/generic/LayoutManager.php";
require_once "include/generic/SugarWidgets/SugarWidgetFielddatetime.php";

class Bug48616Test extends PHPUnit_Framework_TestCase
{
    var $sugarWidgetField;

    public function setUp()
    {
        $this->sugarWidgetField = new SugarWidgetFieldDateTime48616Mock(new LayoutManager());
        global $current_user, $timedate;
        $timedate = TimeDate::getInstance();
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        //$this->setOutputBuffering = false;

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testQueryFilterBefore()
    {
        $layout_def =  array ('name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'before', 'input_name0' => 'Today', 'input_name1' => '01:00am', 'input_name2' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'datetimecombo');
        $filter = $this->sugarWidgetField->queryFilterBefore($layout_def);
        if($GLOBALS['db']->getScriptName() == 'mysql')
        {
            $this->assertRegExp("/pordr_purchaseorders_cstm\.donotinvoiceuntil_c < \'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\'/", $filter);
        }
        /*
        else if($GLOBALS['db']->getScriptName() == 'db2') {

        }
        */
    }

    public function testQueryFilterAfter()
    {
        $layout_def =  array ('name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'after', 'input_name0' => 'Today', 'input_name1' => '01:00am', 'input_name2' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'datetimecombo');
        $filter = $this->sugarWidgetField->queryFilterAfter($layout_def);
        if($GLOBALS['db']->getScriptName() == 'mysql')
        {
            $this->assertRegExp("/pordr_purchaseorders_cstm\.donotinvoiceuntil_c > \'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\'/", $filter);
        }
    }

    public function testQueryFilterNotEqualsStr()
    {
        $layout_def =  array ('name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'not_equals', 'input_name0' => 'Today', 'input_name1' => '01:00am', 'input_name2' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'datetimecombo');
        $filter = $this->sugarWidgetField->queryFilterNot_Equals_str($layout_def);
        $filter = preg_replace('/\s{2,}/', ' ', $filter);
        $filter = str_replace("\n", '', $filter);
        $filter = str_replace("\r", '', $filter);
        if($GLOBALS['db']->getScriptName() == 'mysql')
        {
            $this->assertRegExp("/\(pordr_purchaseorders_cstm\.donotinvoiceuntil_c IS NULL OR pordr_purchaseorders_cstm\.donotinvoiceuntil_c < \'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\' OR pordr_purchaseorders_cstm\.donotinvoiceuntil_c > \'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\'\)/", $filter);
        }
        /*
        else if($GLOBALS['db']->getScriptName() == 'db2') {
            $this->assertRegExp("/\(pordr_purchaseorders_cstm\.donotinvoiceuntil_c IS NULL OR pordr_purchaseorders_cstm\.donotinvoiceuntil_c < CONVERT\(datetime\,'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\',\d+?\) OR pordr_purchaseorders_cstm\.donotinvoiceuntil_c > CONVERT\(datetime\,'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\',120\)\)/", $filter);
        }
        */
    }


}

class SugarWidgetFieldDateTime48616Mock extends SugarWidgetFieldDateTime
{
    protected function queryDateOp($arg1, $arg2, $op, $type)
    {
        global $timedate;
        if($arg2 instanceof DateTime) {
            $arg2 = $timedate->asDbType($arg2, $type);
        }
        return "$arg1 $op ".$GLOBALS['db']->convert($GLOBALS['db']->quoted($arg2), $type)."\n";
    }
}

?>