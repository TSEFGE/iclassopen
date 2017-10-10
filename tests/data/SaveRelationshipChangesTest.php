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



class SaveRelationshipChangesTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', array(true, 1));
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function setRelationshipInfoDataProvider()
    {
        return array(
            array(
                1,
                'accounts_contacts',
                array(1, 'contacts'),
            ),
            array(
                1,
                'member_accounts',
                array(1, 'member_of'),
            ),
            array(
                1,
                'accounts_opportunities',
                array(1, 'opportunities'),
            ),
        );
    }


    /**
     * @dataProvider setRelationshipInfoDataProvider
     */
    public function testSetRelationshipInfoViaRequestVars($id, $rel, $expected)
    {
        $bean = new MockAccountSugarBean();

        $_REQUEST['relate_to'] = $rel;
        $_REQUEST['relate_id'] = $id;

        $return = $bean->set_relationship_info();

        $this->assertSame($expected, $return);
    }

    /**
     * @dataProvider setRelationshipInfoDataProvider
     */
    public function testSetRelationshipInfoViaBeanProperties($id, $rel, $expected)
    {
        $bean = new MockAccountSugarBean();

        $bean->not_use_rel_in_req = true;
        $bean->new_rel_id = $id;
        $bean->new_rel_relname = $rel;

        $return = $bean->set_relationship_info();

        $this->assertSame($expected, $return);
    }

    public function testHandlePresetRelationshipsAdd()
    {
        $acc = SugarTestAccountUtilities::createAccount();

        $macc = new MockAccountSugarBean();
        $macc->disable_row_level_security = true;
        $macc->retrieve($acc->id);

        // create an contact
        $contact = SugarTestContactUtilities::createContact();

        // set the contact id from the bean.
        $macc->contact_id = $contact->id;

        $new_rel_id = $macc->handle_preset_relationships($contact->id, 'contacts');

        $this->assertFalse($new_rel_id);

        // make sure the relationship exists

        $sql = "SELECT account_id, contact_id from accounts_contacts where account_id = '" . $macc->id . "' AND contact_id = '" . $contact->id . "' and deleted = 0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertSame(array('account_id' => $macc->id, 'contact_id' => $contact->id), $row);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        unset($macc);

    }

    public function testHandlePresetRelationshipsDelete()
    {
        $acc = SugarTestAccountUtilities::createAccount();

        $macc = new MockAccountSugarBean();
        $macc->disable_row_level_security = true;
        $macc->retrieve($acc->id);

        // create an contact
        $contact = SugarTestContactUtilities::createContact();


        // insert a dummy row
        $rel_row_id = create_guid();
        $sql = "INSERT INTO accounts_contacts (id, account_id, contact_id) VALUES ('" . $rel_row_id . "','" . $macc->id . "','" . $contact->id . "')";
        $GLOBALS['db']->query($sql);
        $GLOBALS['db']->commit();

        // set the contact id from the bean.
        $macc->rel_fields_before_value['contact_id'] = $contact->id;

        $new_rel_id = $macc->handle_preset_relationships($contact->id, 'contacts');

        $this->assertEquals($contact->id, $new_rel_id);

        // make sure the relationship exists

        $sql = "SELECT account_id, contact_id from accounts_contacts where account_id = '" . $macc->id . "' AND contact_id = '" . $contact->id . "' and deleted = 0";
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertFalse($row);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        unset($macc);

    }

    public function testHandleRemainingRelateFields()
    {
        // create a test relationship
        // save cache reset value
        $_cacheResetValue = SugarCache::$isCacheReset;
        //$rel = $this->createRelationship('Accounts');

        $rel = SugarTestRelationshipUtilities::createRelationship(array(
                    'relationship_type' => 'one-to-many',
                    'lhs_module' => 'Accounts',
                    'rhs_module' => 'Accounts',
                ));

        if($rel == false) {
            $this->fail('Relationship Not Created');
        }

        $rel_name = $rel->getName();
        $id = $rel->getIDName('Accounts');

        $acc1 = SugarTestAccountUtilities::createAccount();
        $acc2 = SugarTestAccountUtilities::createAccount();

        $macc = new MockAccountSugarBean();
        $macc->disable_row_level_security = true;
        $macc->retrieve($acc2->id);

        $macc->$id = $acc1->id;

        $ret = $macc->handle_remaining_relate_fields();
        $this->assertContains($rel_name, $ret['add']['success']);

        $macc->rel_fields_before_value[$id] = $acc1->id;
        $macc->$id = '';
        $ret = $macc->handle_remaining_relate_fields();

        $this->assertContains($rel_name, $ret['remove']['success']);

        // variable cleanup
        // delete the test relationship
        //$this->removeRelationship($rel_name, 'Accounts');
        SugarTestRelationshipUtilities::removeAllCreatedRelationships();

        unset($macc);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        // reset the isCacheReset Value since this is all one request.
        SugarCache::$isCacheReset = $_cacheResetValue;
    }

    public function handleRequestRelateProvider()
    {
        return array(
            array('member_of', true),
            array('MEMBER_OF', true),
            array(time(), false),
        );
    }

    /**
     *
     * @dataProvider handleRequestRelateProvider
     * @param $rel_link_name
     */
    public function testHandleRequestRelate($rel_link_name, $expected)
    {
        $acc1 = SugarTestAccountUtilities::createAccount();
        $acc2 = SugarTestAccountUtilities::createAccount();

        $macc = new MockAccountSugarBean();
        $macc->retrieve($acc2->id);


        $ret = $macc->handle_request_relate($acc1->id, $rel_link_name);

        $this->assertSame($expected, $ret);

        unset($macc);
        SugarTestAccountUtilities::removeAllCreatedAccounts();

    }
}

class MockAccountSugarBean extends Account
{
    public function set_relationship_info(array $exclude = array())
    {
        return parent::set_relationship_info($exclude);
    }

    public function handle_preset_relationships($new_rel_id, $new_rel_name, $exclude = array())
    {
        return parent::handle_preset_relationships($new_rel_id, $new_rel_name, $exclude);
    }

    public function handle_remaining_relate_fields($exclude = array())
    {
        return parent::handle_remaining_relate_fields($exclude);
    }

    public function handle_request_relate($new_rel_id, $new_rel_link)
    {
        return parent::handle_request_relate($new_rel_id, $new_rel_link);
    }
}
