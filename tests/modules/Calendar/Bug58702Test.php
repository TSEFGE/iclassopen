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


/**
 *
 * Test if new parameter show_completed works properly
 * If set to true, it should show all Meetings, Calls and Tasks
 * If set to false, it should show only Meetings, Calls and Tasks that are not completed
 * 
 * @ticket 58702
 * @author avucinic
 */

class Bug58702Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        
        SugarTestHelper::tearDown();
    }

    public function dataProvider()
    {
        return array(
                    0 => array(true),
                    1 => array(false)
                );
    }
    
    /**
     * @group 58702
     * Test if Meetings/Calls/Tasks are shown properly when
     * show completed flag is set
     *
     * @dataProvider dataProvider
     */
    public function testShowCompleted($showCompleted)
    {
        // Create Held Meeting
        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->date_end = $GLOBALS['timedate']->nowDb();
        $meeting->status = 'Held';
        $meeting->save();
        $meeting->set_accept_status($GLOBALS['current_user'], 'accept');

        // Create Held Call
        $call = SugarTestCallUtilities::createCall();
        $call->date_start = $GLOBALS['timedate']->nowDb();
        $call->date_end = $GLOBALS['timedate']->nowDb();
        $call->status = 'Held';
        $call->save();
        $call->set_accept_status($GLOBALS['current_user'], 'accept');
        
        // Create Completed Task
        $task = SugarTestTaskUtilities::createTask();
        $task->date_due = $GLOBALS['timedate']->nowDb();
        $task->status = 'Completed';
        $task->assigned_user_id = $GLOBALS['current_user']->id;
        $task->save();
        
        // Set query dates
        $start_date_time = $GLOBALS['timedate']->fromString(date("Y-m-d"));
        $end_date_time = $start_date_time->get("+7 days");
        $start_date_time = $start_date_time->get("-7 days");

        // Get all activities for the user
        $result = CalendarActivity::get_activities($GLOBALS['current_user']->id, true, $start_date_time, $end_date_time, 'month', true, $showCompleted);
        
        // Depending on show completed, get_activities should return 3 entries, the ones we created above
        if ($showCompleted)
        {
            $this->assertEquals(3, sizeof($result), 'get_activities did not return the Metting, Call and Task as it should have');
            $this->assertEquals($result[0]->sugar_bean->id, $meeting->id, 'Meeting not returned properly');
            $this->assertEquals($result[1]->sugar_bean->id, $call->id, 'Call not returned properly');
            $this->assertEquals($result[2]->sugar_bean->id, $task->id, 'Task not returned properly');
        }
        // Or it shouldn't return anything since all the activities are completed
        else
        {
            $this->assertEquals(0, sizeof($result), 'get_activities should be empty');
        }
    }
}
