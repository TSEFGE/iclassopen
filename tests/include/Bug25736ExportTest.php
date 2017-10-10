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
 * Test if non-primary emails are being exported properly to a CSV file
 * from Accounts module, or modules based on Person
 *
 * @author avucinic@sugarcrm.com
 *
 */
class Bug25736ExportTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $Accounts;
    private $Contacts;
    private $Leads;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->Accounts = SugarTestAccountUtilities::createAccount();
        $this->Contacts = SugarTestContactUtilities::createContact();
        $this->Leads = SugarTestLeadUtilities::createLead();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }

    /**
     * Check if non-primary mails are being exported properly
     * as semi-colon separated values
     *
     * @dataProvider providerEmailExport
     */
    public function testEmailExport($module, $mails)
    {
        // Add non-primary mails
        foreach ($mails as $mail)
        {
            $this->$module->emailAddress->addAddress($mail);
        }
        $this->$module->emailAddress->save($this->$module->id, $this->$module->module_dir);

        // Export the record
        $content = export($module, $this->$module->id, false, false);

        // Because we can't guess the order of the exported non-primary emails, check for separator if there are 2 or more
        if (count($mails) > 1)
        {
            $this->assertContains(";", $content, "Non-primary mail not exported properly.");
        }
        // Check if the mails got exported properly
        foreach ($mails as $mail)
        {
            $this->assertContains($mail, $content, "Non-primary mail not exported properly: $mail.");
        }
    }

    /**
     * Module to be exported
     * Mails to be added as non-primary
     */
    public function providerEmailExport()
    {
        return array(
            array("Accounts", array("test1@mailmail.mail", "test2@mailmail.mail")),
            array("Leads", array("test3@mailmail.mail", "test4@mailmail.mail")),
            array("Contacts", array("test5@mailmail.mail")),
        );
    }

}
