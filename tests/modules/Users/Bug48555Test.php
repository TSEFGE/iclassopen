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


class Bug48555Test extends Sugar_PHPUnit_Framework_TestCase
{
	protected $_user = null;

	public function setUp() 
    {
    	$this->_user = SugarTestUserUtilities::createAnonymousUser();
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$GLOBALS['current_user']->setPreference('default_locale_name_format', 'l f s');
	}
	
	public function tearDown()
	{
	    unset($GLOBALS['current_user']);
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}
        
    public function testgetListViewData()
    {
        $this->_user->first_name = "FIRST-NAME";
        $this->_user->last_name = "LAST-NAME";
        $test_array =  $this->_user->get_list_view_data();
        
        $this->assertEquals('LAST-NAME FIRST-NAME',$test_array['NAME']);
        $this->assertEquals('LAST-NAME FIRST-NAME',$test_array['FULL_NAME']);

    }
    
	public function testgetUsersNameAndEmail() 
    {
        $this->_user->first_name = "FIRST-NAME";
        $this->_user->last_name = "LAST-NAME";
        $this->_user->emailAddress->addAddress("test@test.test", $primary=true);
        $this->_user->emailAddress->save($this->_user->id, $this->_user->module_dir);;
        $test_array = $this->_user->getUsersNameAndEmail();
        
        $this->assertEquals('LAST-NAME FIRST-NAME',$test_array['name']);
        $this->assertEquals('test@test.test',$test_array['email']);
    }
    
    public function testgetEmailLink2()
    {
        $GLOBALS['sugar_config']['email_default_client'] = "sugar";
        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->id = 'abcdefg';
        $this->_contact->first_name = "FIRST-NAME";
        $this->_contact->last_name = "LAST-NAME";
        $this->_contact->object_name = 'Contact';
        $this->_contact->module_dir = 'module_dir';
        $this->_contact->createLocaleFormattedName = true;
        $test = $this->_user->getEmailLink2('test@test.test',$this->_contact);
        
        $pattern = "/.*\"to_email_addrs\":\"LAST-NAME FIRST-NAME \\\\u003Ctest@test.test\\\\u003E\".*/";
        
        $this->assertRegExp($pattern,$test);

    }
    
    public function testgetEmailLink()
    {
        $GLOBALS['sugar_config']['email_default_client'] = "sugar";
        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->id = 'abcdefg';
        $this->_contact->first_name = "FIRST-NAME";
        $this->_contact->last_name = "LAST-NAME";
        $this->_contact->email1 = "test@test.test";
        $this->_contact->object_name = 'Contact';
        $this->_contact->module_dir = 'module_dir';
        $this->_contact->createLocaleFormattedName = true;
        
        $test = $this->_user->getEmailLink("email1",$this->_contact);
        
        $pattern = "/.*\"to_email_addrs\":\"LAST-NAME FIRST-NAME \\\\u003Ctest@test.test\\\\u003E\".*/";
        
        $this->assertRegExp($pattern,$test);
    }
}

