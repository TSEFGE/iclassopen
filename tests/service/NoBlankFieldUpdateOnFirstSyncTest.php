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
 * NoBlankFieldUpdateOnFirstSyncTest.php
 *
 * This unit test was written to test an Outlook Plugin Hotfix.  It is attempting to mimic
 * what would happen if a new Contact record was created in Sugar.  Then a record with the same
 * first and last name and a matching email was created in Outlook.  With the Outlook settings
 * set so that the Sugar server wins on conflicts, what was happening was that the new (blank) values
 * from the Outlook plugin were overriding the SugarCRM record values. Under the new test what should
 * happen is that blank values from the Outlook side do NOT wipe out the SugarCRM values on first sync.
 * 
 * @author Collin Lee
 */

require_once('include/nusoap/nusoap.php');
require_once('tests/service/SOAPTestCase.php');

class NoBlankFieldUpdateOnFirstSyncTest extends SOAPTestCase
{
	public $_soapClient = null;
    var $_sessionId;
    var $_resultId;
    var $_resultId2;
    var $c = null;
    var $c2 = null;

	public function setUp()
    {
        global $current_user;
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';

        //Clean up any possible contacts not deleted
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name = 'NoBlankFieldUpdate' AND last_name = 'OnFirstSyncTest'");

        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $contact = SugarTestContactUtilities::createContact();
        $contact->first_name = 'NoBlankFieldUpdate';
        $contact->last_name = 'OnFirstSyncTest';
        $contact->phone_mobile = '867-5309';
        $contact->email1 = 'noblankfieldupdateonfirstsync@example.com';
        $contact->title = 'Jenny - I Got Your Number';
        $contact->disable_custom_fields = true;
        $contact->save();
		$this->c = $contact;

        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name = 'Collin' AND last_name = 'Lee'");

        //Manually create a contact entry
        $contact2 = new Contact();
        $contact2->title = 'Jenny - I Got Your Number';
        $contact2->first_name = 'Collin';
        $contact2->last_name = 'Lee';
        $contact2->phone_mobile = '867-5309';
        $contact2->disable_custom_fields = true;
        $contact2->email1 = '';
        $contact2->email2 = '';
        $contact2->save();
		$this->c2 = $contact2;
        //DELETE contact_users entries that may have remained
        $GLOBALS['db']->query("DELETE FROM contacts_users WHERE user_id = '{$current_user->id}'");
        parent::setUp();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        global $current_user;
        SugarTestContactUtilities::removeAllCreatedContacts();
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id in ('{$this->_resultId}', '{$this->_resultId2}')");
        $GLOBALS['db']->query("DELETE FROM contacts_users WHERE user_id = '{$current_user->id}'");
        $GLOBALS['db']->commit();
        unset($this->c);
        unset($this->c2);
        parent::tearDown();
    }


    public function testNoBlankFieldUpdateOnFirstSyncTest()
    {
        global $current_user;
        $this->_login();
        $contacts_list=array(
                              'session'=>$this->_sessionId, 'module_name' => 'Contacts',
				              'name_value_lists' => array(
                                        array(
                                            array('name'=>'assigned_user_id' , 'value'=>"{$current_user->id}"),
                                            array('name'=>'first_name' , 'value'=>"{$this->c->first_name}"),
                                            array('name'=>'last_name' , 'value'=>"{$this->c->last_name}"),
                                            array('name'=>'email1' , 'value'=>'noblankfieldupdateonfirstsync@example.com'),
                                            array('name'=>'phone_mobile', 'value'=>''),
                                            array('name'=>'contacts_users_id', 'value'=>"{$current_user->id}"),
                                            array('name'=>'title', 'value'=>''),
                                            array('name'=>'do_not_call', 'value'=>'1'),
                                        )
                              )
                        );

        $result = $this->_soapClient->call('set_entries',$contacts_list);
        $this->_resultId = $result['ids'][0];
        $this->assertEquals($this->c->id, $result['ids'][0], 'Found duplicate');

        $existingContact = new Contact();
        $existingContact->retrieve($this->c->id);

        $this->assertEquals('867-5309', $existingContact->phone_mobile, 'Assert that we have not changed the phone_mobile field from first sync');
        $this->assertEquals('Jenny - I Got Your Number', $existingContact->title, 'Assert that we have not changed the title field from first sync');
        $this->assertEquals(1, $existingContact->do_not_call, 'Assert the field "do_not_call" checkbox was checked and has value of 1');

        $result = $GLOBALS['db']->query("SELECT count(id) AS total FROM contacts WHERE first_name = '{$existingContact->first_name}' AND last_name = '{$existingContact->last_name}'");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals(1, $row['total'], 'Assert we only have one Contact with the first and last name');

        //Now sync a second time
        $this->_login();
        $contacts_list=array(
                              'session'=>$this->_sessionId, 'module_name' => 'Contacts',
				              'name_value_lists' => array(
                                        array(
                                            array('name'=>'assigned_user_id' , 'value'=>"{$current_user->id}"),
                                            array('name'=>'first_name' , 'value'=>"{$this->c->first_name}"),
                                            array('name'=>'last_name' , 'value'=>"{$this->c->last_name}"),
                                            array('name'=>'email1' , 'value'=>'noblankfieldupdateonfirstsync@example.com'),
                                            array('name'=>'phone_mobile', 'value'=>'1-800-SUGARCRM'),
                                            array('name'=>'contacts_users_id', 'value'=>"{$current_user->id}"),
                                            array('name'=>'title', 'value'=>''),
                                            array('name'=>'do_not_call', 'value'=>'0'),
                                        )
                              )
                        );

        $result = $this->_soapClient->call('set_entries',$contacts_list);
        $this->_resultId = $result['ids'][0];
        $this->assertEquals($this->c->id, $result['ids'][0], 'Found duplicate');
        
        $existingContact = new Contact();
        $existingContact->retrieve($this->c->id);

        $this->assertEquals('1-800-SUGARCRM', $existingContact->phone_mobile, 'Assert that we have changed the phone_mobile field from second sync');
        $this->assertEquals('', $existingContact->title, 'Assert that we have changed the title field to be (blank) from second sync');
        $this->assertEquals(0, $existingContact->do_not_call, 'Assert the field "do_not_call" checkbox was UN-checked and has value of 0');

        $result = $GLOBALS['db']->query("SELECT count(id) AS total FROM contacts WHERE first_name = '{$existingContact->first_name}' AND last_name = '{$existingContact->last_name}'");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals(1, $row['total'], 'Assert we only have one Contact with the first and last name');
    }
    

    public function testNoEmailsFindsDuplicates()
    {
        global $current_user;
        $this->_login();
        $contacts_list=array(
                              'session'=>$this->_sessionId, 'module_name' => 'Contacts',
				              'name_value_lists' => array(
                                        array(
                                            array('name'=>'assigned_user_id' , 'value'=>"{$current_user->id}"),
                                            array('name'=>'first_name' , 'value'=>"{$this->c2->first_name}"),
                                            array('name'=>'last_name' , 'value'=>"{$this->c2->last_name}"),
                                            array('name'=>'email1' , 'value'=>''),
                                            array('name'=>'email2', 'value'=>''),
                                            array('name'=>'phone_mobile', 'value'=>''),
                                            array('name'=>'contacts_users_id', 'value'=>"{$current_user->id}"),
                                            array('name'=>'title', 'value'=>''),
                                        )
                              )
                        );

        $result = $this->_soapClient->call('set_entries',$contacts_list);
        $this->_resultId2 = $result['ids'][0];
        $this->assertEquals($this->c2->id, $result['ids'][0], 'Found duplicate when both records have no email');

        $existingContact = new Contact();
        $existingContact->retrieve($this->c2->id);

        $this->assertEquals('867-5309', $existingContact->phone_mobile, 'Assert that we have not changed the phone_mobile field from first sync');
        $this->assertEquals('Jenny - I Got Your Number', $existingContact->title, 'Assert that we have not changed the title field from first sync');

        $result = $GLOBALS['db']->query("SELECT count(id) AS total FROM contacts WHERE first_name = '{$existingContact->first_name}' AND last_name = '{$existingContact->last_name}'");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals(1, $row['total'], 'Assert we only have one Contact with the first and last name');

        //Now sync a second time
        $this->_login();
        $contacts_list=array(
                              'session'=>$this->_sessionId, 'module_name' => 'Contacts',
				              'name_value_lists' => array(
                                        array(
                                            array('name'=>'assigned_user_id' , 'value'=>"{$current_user->id}"),
                                            array('name'=>'first_name' , 'value'=>"{$this->c2->first_name}"),
                                            array('name'=>'last_name' , 'value'=>"{$this->c2->last_name}"),
                                            array('name'=>'email1' , 'value'=>''),
                                            array('name'=>'email2', 'value'=>''),
                                            array('name'=>'phone_mobile', 'value'=>'1-800-SUGARCRM'),
                                            array('name'=>'contacts_users_id', 'value'=>"{$current_user->id}"),
                                            array('name'=>'title', 'value'=>''),
                                        )
                              )
                        );

        $result = $this->_soapClient->call('set_entries',$contacts_list);

        $existingContact = new Contact();
        $existingContact->retrieve($this->c2->id);

        $this->assertEquals('1-800-SUGARCRM', $existingContact->phone_mobile, 'Assert that we have changed the phone_mobile field from second sync');
        $this->assertEquals('', $existingContact->title, 'Assert that we have changed the title field to be (blank) from second sync');
        $result = $GLOBALS['db']->query("SELECT count(id) AS total FROM contacts WHERE first_name = '{$existingContact->first_name}' AND last_name = '{$existingContact->last_name}'");
        $row = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals(1, $row['total'], 'Assert we only have one Contact with the first and last name');
    }

}

?>