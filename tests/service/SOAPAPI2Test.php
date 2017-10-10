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
require_once('include/TimeDate.php');
/**
 * This class is meant to test everything SOAP
 *
 */
class SOAPAPI2Test extends SOAPTestCase
{
    static protected $_contactId = '';
    static protected $_opportunities = array();

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $contact = SugarTestContactUtilities::createContact();
        self::$_contactId = $contact->id;
    }

	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
		parent::setUp();
		$this->_login();
    }

    public function tearDown() {
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        if(!empty(self::$_opportunities)) {
            $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", self::$_opportunities) . '\')');
        }
        parent::tearDownAfterClass();
    }

    /**
	 * Ensure we can create a session on the server.
	 *
	 */
    public function testCanLogin(){
		$result = $this->_login();
    	$this->assertTrue(!empty($result['id']) && $result['id'] != -1,
            'SOAP Session not created. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    }

    public function testSetEntryForContact()
    {
    	$result = $this->_setEntryForContact();
    	$this->assertTrue(!empty($result['id']) && $result['id'] != -1,
            'Can not create new contact. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    } // fn

    public function testGetEntryForContact() {
    	$setresult = $this->_setEntryForContact();
        $result = $this->_getEntryForContact($setresult['id']);
    	if (empty($this->_soapClient->faultcode)) {
    		if (($result['entry_list'][0]['name_value_list'][2]['value'] == 1) &&
    			($result['entry_list'][0]['name_value_list'][3]['value'] == "Cold Call")) {

    			$this->assertEquals($result['entry_list'][0]['name_value_list'][2]['value'],1,"testGetEntryForContact method - Get Entry For contact is not same as Set Entry");
    		} // else
    	} else {
    		$this->fail('Can not retrieve newly created contact. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    	}
    } // fn

    /**
     * @ticket 38986
     */
    public function testGetEntryForContactNoSelectFields(){
        $result = $this->_soapClient->call('get_entry',array('session'=>$this->_sessionId,'module_name'=>'Contacts','id'=>self::$_contactId,'select_fields'=>array(), 'link_name_to_fields_array' => array()));
		$this->assertTrue(!empty($result['entry_list'][0]['name_value_list']), "testGetEntryForContactNoSelectFields returned no field data");

    }

    public function testSetEntriesForAccount() {
    	$result = $this->_setEntriesForAccount();
    	$this->assertTrue(!empty($result['ids']) && $result['ids'][0] != -1,
            'Can not create new account using testSetEntriesForAccount. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    } // fn

    public function testSetEntryForOpportunity() {
    	$result = $this->_setEntryForOpportunity();
    	$this->assertTrue(!empty($result['id']) && $result['id'] != -1,
            'Can not create new account using testSetEntryForOpportunity. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    } // fn

    public function testSetRelationshipForOpportunity() {
    	$setresult = $this->_setEntryForOpportunity();
        $result = $this->_setRelationshipForOpportunity($setresult['id']);
    	$this->assertTrue(($result['created'] > 0), 'testSetRelationshipForOpportunity method - Relationship for opportunity to Contact could not be created');

    } // fn


    public function testGetRelationshipForOpportunity()
    {
    	$setresult = $this->_setEntryForOpportunity();
        $this->_setRelationshipForOpportunity($setresult['id']);
        $result = $this->_getRelationshipForOpportunity($setresult['id']);
    	$this->assertEquals(
    	    $result['entry_list'][0]['id'],
    	    self::$_contactId,
    	    "testGetRelationshipForOpportunity - Get Relationship of Opportunity to Contact failed"
            );
    } // fn

    public function testSearchByModule() {
    	$result = $this->_searchByModule();
    	$this->assertTrue(($result['entry_list'][0]['records'] > 0 && $result['entry_list'][1]['records'] && $result['entry_list'][2]['records']), "testSearchByModule - could not retrieve any data by search");
    } // fn

    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/

    public function _setEntryForContact() {
		global $timedate;
		$current_date = $timedate->nowDb();
        $time = mt_rand();
    	$first_name = 'SugarContactFirst' . $time;
    	$last_name = 'SugarContactLast';
    	$email1 = 'contact@sugar.com';
		$result = $this->_soapClient->call('set_entry',array('session'=>$this->_sessionId,'module_name'=>'Contacts', 'name_value_list'=>array(array('name'=>'last_name' , 'value'=>"$last_name"), array('name'=>'first_name' , 'value'=>"$first_name"), array('name'=>'do_not_call' , 'value'=>"1"), array('name'=>'birthdate' , 'value'=>"$current_date"), array('name'=>'lead_source' , 'value'=>"Cold Call"), array('name'=>'email1' , 'value'=>"$email1"))));
		SugarTestContactUtilities::setCreatedContact(array($result['id']));
		return $result;
    } // fn

    public function _getEntryForContact($id)
    {
		$result = $this->_soapClient->call('get_entry',array('session'=>$this->_sessionId,'module_name'=>'Contacts','id'=>$id,
			'select_fields'=>array('last_name', 'first_name', 'do_not_call', 'lead_source', 'email1'),
			'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))));
		return $result;
    }

    public function _setEntriesForAccount()
    {
		$this->_login();
		global $timedate;
		$current_date = $timedate->nowDb();
        $time = mt_rand();
    	$name = 'SugarAccount' . $time;
        $email1 = 'account@'. $time. 'sugar.com';
		$result = $this->_soapClient->call('set_entries',array('session'=>$this->_sessionId,'module_name'=>'Accounts', 'name_value_lists'=>array(array(array('name'=>'name' , 'value'=>"$name"), array('name'=>'email1' , 'value'=>"$email1")))));
		$soap_version_test_accountId = $result['ids'][0];
		SugarTestAccountUtilities::setCreatedAccount(array($soap_version_test_accountId));
		return $result;
    } // fn

    public function _setEntryForOpportunity() {
		global $timedate;
		$date_closed = $timedate->getNow()->get("+1 week")->asDb();
        $time = mt_rand();
    	$name = 'SugarOpportunity' . $time;
    	$account = SugarTestAccountUtilities::createAccount();
    	$sales_stage = 'Prospecting';
    	$probability = 10;
    	$amount = 1000;
		$result = $this->_soapClient->call('set_entry',array('session'=>$this->_sessionId,'module_name'=>'Opportunities',
			'name_value_lists'=>array(array('name'=>'name' , 'value'=>"$name"), array('name'=>'amount' , 'value'=>"$amount"),
		        array('name'=>'probability' , 'value'=>"$probability"), array('name'=>'sales_stage' , 'value'=>"$sales_stage"),
		        array('name'=>'account_id' , 'value'=>$account->id))));
		self::$_opportunities[] = $result['id'];
		return $result;
    } // fn

  public function _getEntryForOpportunity($id) {
		$result = $this->_soapClient->call('get_entry',array('session'=>$this->_sessionId,'module_name'=>'Opportunities','id'=>$id,'select_fields'=>array('name', 'amount'), 'link_name_to_fields_array' => array(array('name' =>  'contacts', 'value' => array('id', 'first_name', 'last_name')))));
		return $result;
    }

    public function _setRelationshipForOpportunity($id) {
		$result = $this->_soapClient->call('set_relationship',array('session'=>$this->_sessionId,'module_name' => 'Opportunities',
			'module_id' => $id, 'link_field_name' => 'contacts',
			'related_ids' =>array(self::$_contactId), 'name_value_list' => array(array('name' => 'contact_role', 'value' => 'testrole')), 'delete'=>0));
		return $result;
    } // fn

    public function _getRelationshipForOpportunity($id)
    {
		$result = $this->_soapClient->call('get_relationships',
				array(
                'session' => $this->_sessionId,
                'module_name' => 'Opportunities',
                'module_id' => $id,
                'link_field_name' => 'contacts',
                'related_module_query' => '',
                'related_fields' => array('id'),
                'related_module_link_name_to_fields_array' => array(array('name' =>  'contacts', 'value' => array('id', 'first_name', 'last_name'))),
            	'deleted'=>0,
				)
			);
		return $result;
    } // fn

    public function _searchByModule() {
		$result = $this->_soapClient->call('search_by_module',
				array(
                'session' => $this->_sessionId,
                'search_string' => 'Sugar',
				'modules' => array('Accounts', 'Contacts', 'Opportunities'),
                'offset' => '0',
                'max_results' => '10')
            );

		return $result;
    } // fn
}
