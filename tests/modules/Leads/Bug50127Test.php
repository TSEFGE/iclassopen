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


require_once('modules/Leads/views/view.convertlead.php');

/**
 * 
 * Test if Contact is properly linked to Lead if we are not creating a contact
 * but linking an existing one.
 * Check if Account is linked with Contact.
 * 
 * @author avucinic@sugarcrm.com
 *
 */
class Bug50127Test extends Sugar_PHPUnit_Framework_OutputTestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }
    
    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        
        SugarTestHelper::tearDown();

        $_REQUEST = array();
    }
 
    /**
     * Create a lead and convert it to an existing Account and Contact
     */
    public function testConvertLinkingExistingContact() {
        // Create records 
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();

        // ConvertLead to an existing Contact and Account
        $_REQUEST = array (
            'module' => 'Leads',
            'record' => $lead->id,
            'isDuplicate' => 'false',
            'action' => 'ConvertLead',
            // Existing Contact
            'convert_create_Contacts' => 'false',
            'report_to_name' => $contact->name,
            'reports_to_id' => $contact->id,
            // Existing Account
            'convert_create_Accounts' => 'false',
            'account_name' => $account->name,
            'account_id' => $account->id,
            // Save
            'handle' => 'save',
        );

        // Call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->display();

        // Refresh Lead
        $leadId = $lead->id;
        $lead = new Lead();
        $lead->retrieve($leadId);
        // Refresh Contact
        $contactId = $contact->id;
        $contact = new Contact();
        $contact->retrieve($contactId);

        // Check if contact it's linked properly
        $this->assertEquals($contact->id, $lead->contact_id, 'Contact not linked with Lead successfully.');
        // Check if account is linked with lead properly
        $this->assertEquals($account->id, $lead->account_id, 'Account not linked with Lead successfully.');
        // Check if account is linked with contact properly        
        $this->assertEquals($account->id, $contact->account_id, 'Account not linked with Contact successfully.');
        // Check Lead Status, should be converted
        $this->assertEquals('Converted', $lead->status, "Lead status should be 'Converted'.");
    }
}