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



require_once('modules/Meetings/MeetingFormBase.php');


class Bug58011Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    public function testAcceptanceAfterDateUpdate()
    {
        global $current_user;
        global $db;

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $user = SugarTestUserUtilities::createAnonymousUser();

        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $current_user->id);
        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $user->id);

        // set this to 'accept' before handleSave and make sure it gets set to 'none' after handleSave
        $meeting->set_accept_status($user, 'accept');
        $meeting->save();

        $_POST['record'] = $_REQUEST['record'] = $meeting->id;
        $_POST['user_invitees'] = $current_user->id.','.$user->id;
        $_POST['module'] = 'Meetings';
        $_POST['action'] = 'Save';
        $_POST['assigned_user_id'] = $current_user->id;
        $_POST['send_invites'] = $_REQUEST['send_invites'] = 1;
        $_POST['date_start'] = $GLOBALS['timedate']->getNow()->asDb();
        $_POST['date_end'] = $GLOBALS['timedate']->getNow()->modify("+900 seconds")->asDb();

        $formBase = new MeetingFormBase();
        $formBase->handleSave('', false, false);

        $sql = "SELECT accept_status FROM meetings_users WHERE meeting_id='{$meeting->id}' AND user_id='{$user->id}'";
        $result = $db->query($sql);
        if ($row = $db->fetchByAssoc($result)) {
            $this->assertEquals('none', $row['accept_status'], 'Should be none after date changed and invite sent.');
        }
    }
}
