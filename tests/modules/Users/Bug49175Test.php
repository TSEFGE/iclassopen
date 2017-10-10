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


require_once('modules/Users/User.php');
require_once('modules/Users/UserViewHelper.php');

/**
 * Bug #49175
 * When user is admin doesn't display on user detailview
 * @ticket 49175
 */
class Bug49175Test extends  Sugar_PHPUnit_Framework_TestCase
{
    private $user;

    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function userTypes()
    {
        return array(
            array('is_admin' => '1', 'is_group' => '0', 'portal_only' => '0', 'type' => 'Administrator'),
            array('is_admin' => '0', 'is_group' => '1', 'portal_only' => '0', 'type' => 'GROUP'),
            array('is_admin' => '0', 'is_group' => '0', 'portal_only' => '0', 'type' => 'RegularUser')
        );
    }

    /**
     * @group 49175
     * @dataProvider userTypes
     */
    public function testGetUserType($is_admin, $is_group, $portal_only=0, $type)
    {
        $this->user->is_admin = $is_admin;
        $this->user->is_group = $is_group;
        $userViewHelper = new MockUserViewHelper();
        $userViewHelper->setUserType($this->user);
        $this->assertEquals($this->user->user_type, $type);
    }
}

class MockUserViewHelper extends UserViewHelper {

    //override the constructor, don't bother passing Smarty instance, etc.
    public function __construct() {

    }
}

?>
