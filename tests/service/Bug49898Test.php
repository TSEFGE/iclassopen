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
 * Bug49898Test.php
 *
 * This test is for bug 49898.  Basically the plugin code is still dependent on the legacy versions of the SOAP api (soap.php).  As a result,
 * the search_by_module call is expecting a username and password combination.  However, the plugin code cannot supply a username and password, but
 * only has the session id information.  Therefore, as an alternative, it was proposed to have a workaround where if a username is empty, then the password
 * is assumed to be the session id.  This test replicates that check by searching on two modules (Accounts and Contacts) based on an email address
 * derived from the Contact.
 *
 * @author Collin Lee
 *
 */

require_once 'tests/service/SOAPTestCase.php';

class Bug49898Test extends SOAPTestCase
{
    var $contact;
    var $account;
    var $lead;

    /**
     * setUp
     * Override the setup from SoapTestCase to also create the seed search data for Accounts and Contacts.
     */
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
   		parent::setUp();
        $this->_login(); // Logging in just before the SOAP call as this will also commit any pending DB changes
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->contacts_users_id = $GLOBALS['current_user']->id;
        $this->contact->save();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->email1 = $this->contact->email1;
        $this->account->save();
        $this->lead = SugarTestLeadUtilities::createLead();
        $this->lead->email1 = $this->contact->email1;
        $this->lead->save();
        $GLOBALS['db']->commit(); // Making sure these changes are committed to the database
    }

    public function testSearchByModuleWithSessionIdHack()
    {
        //Assert that the plugin fix to use a blank user_name and session id as password works
        $modules = array('Contacts', 'Accounts', 'Leads');
        $result = $this->_soapClient->call('search_by_module', array('user_name' => '', 'password' => $this->_sessionId, 'search_string' => $this->contact->email1, 'modules' => $modules, 'offset' => 0, 'max_results' => 10));
        $this->assertTrue(!empty($result) && count($result['entry_list']) == 3, 'Incorrect number of results returned. HTTP Response: '.$this->_soapClient->response);

        //Assert that the traditional method of using user_name and password also works
        $result = $this->_soapClient->call('search_by_module', array('user_name' => $GLOBALS['current_user']->user_name, 'password' => $GLOBALS['current_user']->user_hash, 'search_string' => $this->contact->email1, 'modules' => $modules, 'offset' => 0, 'max_results' => 10));
        $this->assertTrue(!empty($result) && count($result['entry_list']) == 3, 'Incorrect number of results returned. HTTP Response: '.$this->_soapClient->response);
    }


}

