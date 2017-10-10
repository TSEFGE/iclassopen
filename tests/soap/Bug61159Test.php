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


require_once('tests/service/SOAPTestCase.php');
require_once('soap/SoapHelperFunctions.php');

/**
 * Bug #61159
 * Soap API add_create_account bug
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 61159
 */
class Bug61159Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $_account1, $_account2, $_contact;
    private $_deletedAcc, $_deletedAccContact;
    private $_contact2;
    private $_tmpacc;

    /**
     * Create contact function
     */
    public function createContact($account_id, $account_name)
    {
        $contact = SugarTestContactUtilities::createContact();
        $contact->account_id = $account_id;
        $contact->account_name = $account_name;
        $contact->save();
        $GLOBALS['db']->commit();
        return $contact;
    }

    /**
     * Create account function
     */
    public function createAccount($name, $deleted = 0)
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->name = $account->account_name = $name;
        $account->deleted = $deleted;
        $account->save();
        $GLOBALS['db']->commit();
        return $account;
    }

    /**
     * Create user, account, contact
     */
    public function setUp()
    {
        $this->_account1 = $this->createAccount("Account Bug61159Test");
        $this->_account2 = $this->createAccount("Account Bug61159Test");
        $this->_contact = $this->createContact($this->_account2->id, "Account Bug61159Test");
        $this->_contact2 = $this->createContact(0, "Account Bug61159Test");

        $this->_deletedAcc = $this->createAccount("Account Bug61159Test 2", 1);
        $this->_deletedAccContact = $this->createContact($this->_deletedAcc->id, "Account Bug61159Test 2");

        parent::setUp();
    }

    /**
     * Remove account, contact, user
     */
    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->_account1->id}' OR id = '{$this->_account2->id}' OR id = '{$this->_deletedAcc->id}' OR id = '{$this->_deletedAccContact->account_id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->_contact->id}' OR id = '{$this->_deletedAccContact->id}' OR id = '{$this->_contact2->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id = '{$this->_contact->id}' OR contact_id = '{$this->_deletedAccContact->id}' OR contact_id = '{$this->_contact2->id}'");
        if (isset($this->_tmpacc))
        {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->_tmpacc->id}'");
        }
    }

    /**
     * Test add_create_account() to see if it reassigns the contact's account (by Account id)
     */
    public function testReassignContactById()
    {
        add_create_account($this->_contact);
        $this->assertEquals($this->_contact->account_id, $this->_account2->id);
    }

    /**
     * Test add_create_account() to see if it will assign an account to a contact without accounts
     */
    public function testAssignAccountToContact()
    {
        add_create_account($this->_contact2);
        $this->assertNotEquals($this->_contact2->account_id, 0);
    }

    /**
     * Test add_create_account() to see if it will delete a deleted account and create a new one
     */
    public function testDeletedAccountCreateNew()
    {
        add_create_account($this->_deletedAccContact);
        $this->assertNotEquals($this->_deletedAcc->id, $this->_deletedAccContact->account_id);
    }

    /**
     * Test add_create_account() to see if it will create a new account (non-existent by ID and non-existent by Name)
     */
    public function testNotFoundCreateNew()
    {
        $dummyContact = new Contact();
        $dummyContact->account_name = "UniqueAccountNameTest123";
        add_create_account($dummyContact);
        $this->_tmpacc = new Account();
        $this->_tmpacc->retrieve_by_string_fields(array('name' => 'UniqueAccountNameTest123'));
        $this->assertNotNull($this->_tmpacc->id);
    }

}
