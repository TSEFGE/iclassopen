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


require_once 'tests/service/SOAPTestCase.php';

class Bug51617Test extends SOAPTestCase
{
    protected $_account;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->field = get_widget('varchar');
        $this->field->id = 'Accountstest_custom_c';
        $this->field->name = 'test_custom_c';
        $this->field->vanme = 'LBL_TEST_CUSTOM_C';
        $this->field->comments = NULL;
        $this->field->help = NULL;
        $this->field->custom_module = 'Accounts';
        $this->field->type = 'varchar';
        $this->field->label = 'LBL_TEST_CUSTOM_C';
        $this->field->len = 255;
        $this->field->required = 0;
        $this->field->default_value = NULL;
        $this->field->date_modified = '2009-09-14 02:23:23';
        $this->field->deleted = 0;
        $this->field->audited = 0;
        $this->field->massupdate = 0;
        $this->field->duplicate_merge = 0;
        $this->field->reportable = 1;
        $this->field->importable = 'true';
        $this->field->ext1 = NULL;
        $this->field->ext2 = NULL;
        $this->field->ext3 = NULL;
        $this->field->ext4 = NULL;

        $this->df = new DynamicField('Accounts');
        $this->mod = new Account();
        $this->df->setup($this->mod);
        $this->df->addFieldObject($this->field);
        $this->df->buildCache('Accounts');
        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Accounts', 'Account');
        $this->mod->field_defs = $GLOBALS['dictionary']['Account']['fields'];

        $this->_account = SugarTestAccountUtilities::createAccount();

        $this->_account->test_custom_c = 'Custom Field';
        $this->_account->save();

        $GLOBALS['db']->commit(); // Making sure we commit any changes

        parent::setUp();
    }

    public function tearDown()
    {
        $this->df->deleteField($this->field);

        SugarTestAccountUtilities::removeAllCreatedAccounts();

        parent::tearDown();

        global $soap_version_test_accountId, $soap_version_test_opportunityId, $soap_version_test_contactId;
        unset($soap_version_test_accountId);
        unset($soap_version_test_opportunityId);
        unset($soap_version_test_contactId);

        SugarTestHelper::tearDown();
    }

    public function testGetEntryListWithCustomField()
    {
        $this->_login();
        $result = $this->_soapClient->call('get_entry_list',
            array(
                 'session'=>$this->_sessionId,
                 "module_name" => 'Accounts',
                 "accounts.id = '{$this->_account->id}'",
                 '',
                 0,
                 "select_fields" => array('id', 'name', 'test_custom_c'),
                 null,
                 'max_results' => 1
            )
        );

        $this->assertTrue($result['result_count'] > 0,
            'Get_entry_list failed: Fault code: '.$this->_soapClient->faultcode.', fault string:'.$this->_soapClient->faultstring.', fault detail: '.$this->_soapClient->faultdetail);

        $row = array();
        $row = $result['entry_list'][0]['name_value_list'];

        // find the custom field
        if (!empty($row))
        {
            foreach($row as $r) {
                // just make sure they are all not empty
                $this->assertNotEmpty($r['value']);
                // make sure that the test field has our value in it
                if($r['name'] == "test_custom_c") {
                    $this->assertEquals("Custom Field", $r['value']);
                }
            }
        } // if

    } // fn
}
