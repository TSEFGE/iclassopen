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


require_once('modules/Users/UserViewHelper.php');
require_once 'include/Sugar_Smarty.php';

/**
 * Bug49627Test.php
 *
 * This unit test tests the user type dropdown items created from the UserViewHelper class.
 * It runs tests against the normal user, portal and group user types.
 */
class Bug49627Test extends Sugar_PHPUnit_Framework_TestCase
{

var $normalUser;
var $groupUser;


public function setUp()
{
    global $current_user;
    $current_user = SugarTestUserUtilities::createAnonymousUser();

    $this->normalUser = SugarTestUserUtilities::createAnonymousUser(false);
    $this->normalUser->id = create_guid();
    $this->normalUser->user_type = 'RegularUser';


    $this->groupUser = SugarTestUserUtilities::createAnonymousUser(false);
    $this->groupUser->id = create_guid();
    $this->groupUser->is_group = 1;
    $this->groupUser->user_type = 'GROUP';

    $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
}

public function testSetupUserTypeDropdownNormalUser()
{
    $smarty = new Sugar_Smarty();
    $userViewHelper = new UserViewHelperMock($smarty, $this->normalUser);
    $userViewHelper->usertype = 'RegularUser';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/RegularUser/', $dropdown);
    $this->assertRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);

    $this->normalUser->id = '';
    $userViewHelper = new UserViewHelperMock($smarty, $this->normalUser);
    $userViewHelper->usertype = 'RegularUser';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/RegularUser/', $dropdown);
    $this->assertRegExp('/RegularUser/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/GROUP/', $dropdown);
    $this->assertNotRegExp('/GROUP/', $user_type_readonly);
}

public function testSetupUserTypeDropdownGroupUser()
{
    $smarty = new Sugar_Smarty();
    $userViewHelper = new UserViewHelperMock($smarty, $this->groupUser);
    $userViewHelper->usertype = 'GROUP';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/GROUP/', $dropdown);
    $this->assertRegExp('/GROUP/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);

    $userViewHelper = new UserViewHelperMock($smarty, $this->groupUser);
    $this->groupUser->id = '';
    $userViewHelper->usertype = 'GROUP';
    $userViewHelper->setupUserTypeDropdown();
    $dropdown = $userViewHelper->ss->get_template_vars('USER_TYPE_DROPDOWN');
    $user_type_readonly = $userViewHelper->ss->get_template_vars('USER_TYPE_READONLY');
    $this->assertRegExp('/GROUP/', $dropdown);
    $this->assertRegExp('/GROUP/', $user_type_readonly);
    $this->assertNotRegExp('/PORTAL_ONLY/', $dropdown);
    $this->assertNotRegExp('/PORTAL_ONLY/', $user_type_readonly);
    $this->assertNotRegExp('/RegularUser/', $dropdown);
    $this->assertNotRegExp('/RegularUser/', $user_type_readonly);
}


}

//UserViewHelperMock
//This class turns the $ss class variable to have public access
class UserViewHelperMock extends UserViewHelper
{
    var $ss;
}
