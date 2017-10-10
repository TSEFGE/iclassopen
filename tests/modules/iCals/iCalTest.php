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


require_once 'modules/iCals/iCal.php';

class iCalTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $timedate;
    var $project;

    public function setUp()
    {
        $this->timedate = new TimeDate();

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->name = "VeryImportantMeeting";
        $meeting->date_start = $this->timedate->to_display_date_time(gmdate("Y-m-d H:i:s", mktime(12, 30, 00, date("m"), date("d")+1, date("Y"))));
        $meeting->save();
        $GLOBALS['db']->query(sprintf("INSERT INTO meetings_users (id, meeting_id, user_id, required, accept_status, date_modified, deleted) VALUES ('%s', '%s', '%s', '1', 'none', NULL, '0')", create_guid(), $meeting->id, $GLOBALS['current_user']->id));

        $task = SugarTestTaskUtilities::createTask();
        $task->assigned_user_id = $GLOBALS['current_user']->id;
        $task->name = "VeryImportantTask";
        $task->save();

        $this->project = SugarTestProjectUtilities::createProject();
        $projectId = $this->project->id;
        $projectTaskData = array (
            'project_id' => $projectId,
            'parent_task_id' => '',
            'project_task_id' => 1,
            'percent_complete' => 50,
            'name' => 'VeryImportantProjectTask'
        );
        $projectTask = SugarTestProjectTaskUtilities::createProjectTask($projectTaskData);
        $projectTask->assigned_user_id = $GLOBALS['current_user']->id;
        $projectTask->save();

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        unset($this->timedate);
        unset($this->project);
        unset($GLOBALS['current_user']);
    }

    public function testGetVcalIcal()
    {
        $iCal = new iCal();
        $iCalString = $iCal->getVcalIcal($GLOBALS['current_user'], null);

        // echo "***********BEGIN iCalString*************\n".$iCalString."***********END iCalString***************\n";

        $this->assertContains("VeryImportantMeeting", $iCalString, "Cannot find VEVENT: VeryImportantMeeting");
        $this->assertContains("VeryImportantTask", $iCalString, "Cannot find VTODO: VeryImportantTask");
        $this->assertContains("VeryImportantProjectTask", $iCalString, "Cannot find VTODO: VeryImportantProjectTask");
    }

}