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

 
require_once('modules/Emails/Email.php');

/**
 * Test case for Bugs 50972, 50973 and 50979
 */
class BugNullAssignedUserIdTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $old_current_user;
    private $current_user;
    private $email;
	
	public function setUp()
	{
	    global $current_user;
        
        if (!empty($current_user)) {
            $this->old_current_user = $current_user;
        }
		
	    $this->current_user = SugarTestUserUtilities::createAnonymousUser();
        
        $GLOBALS['current_user'] = $this->current_user;
	    $this->email = new Email();
	    $this->email->email2init();

        // Set some values for some fields so the query is actually built
        $this->email->id = '1';
        $this->email->created_by = $this->current_user->id;
        $this->email->date_modified = date('Y-m-d H:i:s');

        // Specify an empty assigned user id for testing nulls
        $this->email->assigned_user_id = '';
	}
	
	public function tearDown()
	{
		unset($this->email);
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($this->current_user);
        
        if ($this->old_current_user) {
            $GLOBALS['current_user'] = $this->old_current_user;
        }
	}

    public function testNullAssignedUserIdConvertedToEmptyInSave() {
        $query = $this->email->db->updateSQL($this->email);
        $this->assertContains("assigned_user_id=''", $query, 'Assigned user id set to empty string not found');
    }

    public function testNullAssignedUserIdInSave() {
        $this->email->setFieldNullable('assigned_user_id');
        $query = $this->email->db->updateSQL($this->email);
        $this->email->revertFieldNullable('assigned_user_id');
        $this->assertContains('assigned_user_id=NULL', $query, 'Assigned user id set to DB NULL value not found');
    }
}
?>