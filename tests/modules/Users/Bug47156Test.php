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
/**
 * Bug #47156
 * Reassigning Users With Instance That Has Numeric Ids
 * @ticket 47156
 */
class Bug47156Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $user1;
    private $user2;

    private function createUser($id = '', $status = '')
    {
        $time = mt_rand();
        $userId = 'SugarUser';
        $user = new User();
        $user->user_name = $userId . $time;
        $user->user_hash = md5($userId.$time);
        $user->first_name = $userId;
        $user->last_name = $time;
        if (!empty($status))
        {
            $user->status=$status;
        }
        else
        {
            $user->status='Active';
        }
        
        $user->default_team = '1'; //Set Default Team to Global
        if(!empty($id))
        {
            $user->new_with_id = true;
            $user->id = $id;
        }

        $user->save();
        $user->fill_in_additional_detail_fields();
        
        return $user;
    }
    
    /**
     * @group 47156
     */
    public function testCorrectUserListOutput()
    {
        $this->user1 = $this->createUser(11, 'Active');
        $this->user2 = $this->createUser(12, 'Inactive');
        
        $allUsers = User::getAllUsers(); 
        
        $this->assertArrayHasKey($this->user1->id, $allUsers);
        $this->assertArrayHasKey($this->user2->id, $allUsers);
        
        $dbManager = $GLOBALS['db'];
        $dbManager->query('DELETE FROM users WHERE id IN (' . $dbManager->quoted($this->user1->id) . ', ' . $dbManager->quoted($this->user2->id) . ')');
        $dbManager->query('DELETE FROM user_preferences WHERE assigned_user_id IN (' . $dbManager->quoted($this->user1->id) . ', ' . $dbManager->quoted($this->user2->id) . ')');
        $dbManager->query('DELETE FROM teams WHERE associated_user_id IN (' . $dbManager->quoted($this->user1->id) . ', ' . $dbManager->quoted($this->user2->id) . ')');
        $dbManager->query('DELETE FROM team_memberships WHERE user_id IN (' . $dbManager->quoted($this->user1->id) . ', ' . $dbManager->quoted($this->user2->id) . ')');
    }
}
?>