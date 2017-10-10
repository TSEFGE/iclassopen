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


class Bug51679Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $account;
    private $account2;
    private $contact;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact);
        $this->account2 = SugarTestAccountUtilities::createAccount();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * @outputBuffering disabled
     */
    public function testM2MRelationships()
    {
        require_once('data/Relationships/M2MRelationship.php');
        $def = array(
            'table'=>'accounts_contacts',
            'join_table'=>'accounts_contacts',
            'name'=>'accounts_contacts',
            'lhs_module' => 'accounts',
            'rhs_module' => 'contacts'
        );
        $m2mRelationship = new M2MRelationship($def);
        $m2mRelationship->join_key_lhs = 'account_id';
        $m2mRelationship->join_key_rhs = 'contact_id';
        $result = $m2mRelationship->relationship_exists($this->account, $this->contact);

        $entry_id = $GLOBALS['db']->getOne("SELECT id FROM accounts_contacts WHERE account_id='{$this->account->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals($entry_id, $result);

        $result = $m2mRelationship->relationship_exists($this->account2, $this->contact);
        $this->assertEmpty($result);
    }
}