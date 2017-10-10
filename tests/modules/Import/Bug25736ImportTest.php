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


require_once('modules/Import/Importer.php');
require_once('modules/Import/sources/ImportFile.php');
require_once('include/export_utils.php');

/**
 *
 * Test if non-primary emails are being imported properly from a CSV file
 * on Accounts module, or modules based on Person
 *
 * @author avucinic@sugarcrm.com
 *
 */
class Bug25736ImportTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_importObject;
    private $_file;
    private $_cleanId;

    public function setUp()
    {
        $this->_file = $GLOBALS['sugar_config']['upload_dir'] . 'Bug25736Test.csv';

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM email_addr_bean_rel WHERE bean_id = '{$this->_cleanId}' AND bean_module = '{$this->_importObject->module_dir}'");
        $GLOBALS['db']->query("DELETE FROM email_addresses WHERE email_address IN ('testmail1@test.com', 'testmail2@test.com', 'testmail3@test.com')");
        $GLOBALS['db']->query("DELETE FROM {$this->_importObject->table_name} WHERE created_by = '{$GLOBALS['current_user']->id}'");

        SugarTestHelper::tearDown();
    }

    /**
     * Check if semi-colon separated non-primary mails
     * are being imported properly
     *
     * @dataProvider providerEmailImport
     */
    public function testEmailImport($module, $lastName, $expected, $test)
    {
        $fileCreated = sugar_file_put_contents($this->_file, $test);
        $this->assertGreaterThan(0, $fileCreated, 'Failed to write to ' . $this->_file);

        // Create the ImportFile the Importer uses from our CSV
        $importSource = new ImportFile($this->_file, ',', '"');

        // Create the bean type we're importing
        $this->_importObject = $bean = new $module;

        // Setup needed $_REQUEST data 
        $_REQUEST['columncount'] = 2;
        $_REQUEST['colnum_0'] = 'email_addresses_non_primary';
        $_REQUEST['colnum_1'] = 'last_name';
        // A few changed for Accounts module
        if ($module == "Account") {
            $_REQUEST['columncount'] = 3;
            $_REQUEST['colnum_1'] = 'name';
            $_REQUEST['colnum_2'] = 'team_id';
        }

        $_REQUEST['import_module'] = $bean->module_dir;
        $_REQUEST['importlocale_charset'] = 'UTF-8';
        $_REQUEST['importlocale_dateformat'] = "m/d/Y";
        $_REQUEST['importlocale_timeformat'] = "h:i a";
        $_REQUEST['importlocale_timezone'] = 'GMT';
        $_REQUEST['importlocale_default_currency_significant_digits'] = '2';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_dec_sep'] = '.';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_default_locale_name_format'] = 's f l';
        $_REQUEST['importlocale_num_grp_sep'] = ',';

        // Create the Importer and try importing
        $importer = new Importer($importSource, $bean);
        $importer->import();

        // Check if the Lead is created
        $query = "SELECT id FROM $bean->table_name WHERE {$_REQUEST['colnum_1']} = '$lastName'";
        $result = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertNotEmpty($row['id'], $module . ' not created');
        // Save Lead id for easier cleanup after test
        $this->_cleanId = $row['id'];

        // Check if all of the mails got created and linked properly
        foreach ($expected as $mail)
        {
            // Check if the mail got created
            $query = "SELECT id FROM email_addresses WHERE email_address = '$mail'";
            $result = $GLOBALS['db']->query($query);
            $row = $GLOBALS['db']->fetchByAssoc($result);

            $this->assertNotEmpty($row['id'], 'Mail not created');
            $mailId = $row['id'];

            // Check if the mail is linked
            $query = "SELECT id FROM email_addr_bean_rel WHERE email_address_id = '$mailId' AND bean_module = '$bean->module_dir' AND deleted = 0 AND primary_address = 0";
            $result = $GLOBALS['db']->query($query);
            $row = $GLOBALS['db']->fetchByAssoc($result);

            $this->assertNotEmpty($row['id'], 'Mail not linked');
        }
    }

    public function providerEmailImport()
    {
        /*
        * Last name for getting created Lead
        * Array of mails that should be created and linked to Lead
        * CSV data to be used for import
        */
        return array(
            array("Lead", "Random Guy 1", array("testmail1@test.com", "testmail2@test.com"), array('"testmail1@test.com;testmail2@test.com", "Random Guy 1"')),
            array("Contact", "Random Guy 2", array("testmail2@test.com"), array('"testmail2@test.com", "Random Guy 2"')),
            array("Account", "Random Guy 3", array("testmail3@test.com", "testmail1@test.com"), array('"testmail3@test.com;testmail1@test.com", "Random Guy 3", "West"')),
        );
    }

}
