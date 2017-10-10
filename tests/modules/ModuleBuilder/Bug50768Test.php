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


require_once 'modules/DynamicFields/templates/Fields/TemplateCurrency.php';
require_once("modules/ModuleBuilder/controller.php");

/**
 * Bug #50768
 * in Studio once set the visibility function for currency field will miss the currency_Id
 *
 * @author asokol@sugarcrm.com
 * @ticket 50768
 */

class Bug50768Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $targetModule = "Accounts";
    protected $currencyFieldDef1 = array(
        "action" => "saveField",
        "comments" => "",
        "default" => "",
        "dependency" => "",
        "dependency_display" => "",
        "duplicate_merge" => "0",
        "enforced" => "false",
        "formula" => "",
        "formula_display" => "",
        "help" => "",
        "importable" => "true",
        "is_update" => "true",
        "labelValue" => "test_cur_c1",
        "label" => "LBL_TEST_CUR_1",
        "new_dropdown" => "",
        "reportableCheckbox" => "1",
        "reportable" => "1",
        "to_pdf" => "true",
        "type" => "currency",
        "name" => "c1",
        "module" => "ModuleBuilder",
        "view_module" => "Accounts",
    );

    protected $currencyFieldDef2 = array(
        "action" => "saveField",
        "comments" => "",
        "default" => "",
        "dependency" => "",
        "dependency_display" => "",
        "duplicate_merge" => "0",
        "enforced" => "false",
        "formula" => "",
        "formula_display" => "",
        "help" => "",
        "importable" => "true",
        "is_update" => "true",
        "labelValue" => "test_cur_c2",
        "label" => "LBL_TEST_CUR_2",
        "new_dropdown" => "",
        "reportableCheckbox" => "1",
        "reportable" => "1",
        "to_pdf" => "true",
        "type" => "currency",
        "name" => "c2",
        "module" => "ModuleBuilder",
        "view_module" => "Accounts",
    );

    public function setUp()
    {
        $this->markTestIncomplete("This test breaks others tests on 644 on CI.  Disabling for sanity check");
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

     /*   unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_list_strings']);
        unset($_REQUEST);*/

    }

    /**
     * Test checks if currency_id field was deleted with the lasr currency field
     * @group 50768
     */
    public function testSettedCurrencyIdField()
    {
        $mbc = new ModuleBuilderController();
        //Create the new Fields
        $_REQUEST = $this->currencyFieldDef1;
        $mbc->action_SaveField();
        $_REQUEST = $this->currencyFieldDef2;
        $mbc->action_SaveField();

        $this->currencyFieldDef1['name'] = 'c1_c';
        $_REQUEST = $this->currencyFieldDef1;
      //  $mbc->action_DeleteField();
        $this->currencyFieldDef2['name'] = 'c2_c';
        $_REQUEST = $this->currencyFieldDef2;
       // $mbc->action_DeleteField();

        $count = 0;
        $query = "SELECT * FROM fields_meta_data WHERE custom_module='Accounts' AND type='currency_id' AND deleted = 0";
        $result = $GLOBALS['db']->query ( $query );
        while ( $row = $GLOBALS['db']->fetchByAssoc ( $result ) ) {
            $count++;
        }
        $this->assertEquals($count, 0);
    }
}
