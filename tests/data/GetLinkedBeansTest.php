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


require_once("data/BeanFactory.php");
class GetLinkedBeansTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();
    protected $createdFiles = array();

    public function setUp()
	{
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
	}

	public function tearDown()
	{
	    foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
        foreach($this->createdFiles as $file)
        {
            if (is_file($file))
                unlink($file);
        }

        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);
	}

    public function testGetLinkedBeans()
    {
        //Test the accounts_leads relationship
        $account = BeanFactory::newBean("Accounts");
        $account->name = "GetLinkedBeans Test Account";
        $account->save();
        $this->createdBeans[] = $account;

        $case  = BeanFactory::newBean("Cases");
        $case->name = "GetLinkedBeans Test Cases";
        $case->save();
        $this->createdBeans[] = $case;

        $this->assertTrue($account->load_relationship("cases"));
        $this->assertInstanceOf("Link2", $account->cases);
        $this->assertTrue($account->cases->loadedSuccesfully());
        $account->cases->add($case);
        $account->save();

        $where = array(
                 'lhs_field' => 'id',
                 'operator' => ' LIKE ',
                 'rhs_value' => "{$case->id}",
        );

        $cases = $account->get_linked_beans('cases', 'Case', array(), 0, 10, 0, $where);
        $this->assertEquals(1, count($cases), 'Assert that we have found the test case linked to the test account');

        $contact  = BeanFactory::newBean("Contacts");
        $contact->first_name = "First Name GetLinkedBeans Test Contacts";
        $contact->last_name = "First Name GetLinkedBeans Test Contacts";
        $contact->save();
        $this->createdBeans[] = $contact;

        $this->assertTrue($account->load_relationship("contacts"));
        $this->assertInstanceOf("Link2", $account->contacts);
        $this->assertTrue($account->contacts->loadedSuccesfully());
        $account->contacts->add($contact);

        $where = array(
                 'lhs_field' => 'id',
                 'operator' => ' LIKE ',
                 'rhs_value' => "{$contact->id}",
        );

        $contacts = $account->get_linked_beans('contacts', 'Contact', array(), 0, -1, 0, $where);
        $this->assertEquals(1, count($contacts), 'Assert that we have found the test contact linked to the test account');
    }
    
}
?>