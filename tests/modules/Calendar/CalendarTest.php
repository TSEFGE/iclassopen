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




require_once "modules/Calendar/Calendar.php";
require_once "modules/Calendar/CalendarUtils.php";
require_once('modules/Meetings/Meeting.php');

class CalendarTest extends Sugar_PHPUnit_Framework_TestCase {

   	/**
	 * @var TimeDate
	 */
	protected $time_date;
	
	protected $meeting_id = null;
    
	public static function setUpBeforeClass(){
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass(){
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        	unset($GLOBALS['current_user']);
    	}

	public function setUp(){
        $this->time_date = new TimeDate();
		$_REQUEST['module'] = 'Calendar';
		$_REQUEST['year'] = '2012';
		$_REQUEST['month'] = '01';
		$_REQUEST['day'] = '02';
	}

	public function tearDown(){
		unset($_REQUEST['module']);
		unset($_REQUEST['year']);
		unset($_REQUEST['month']);
		unset($_REQUEST['day']);
		
		if(isset($this->meeting_id)){
			$GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting_id}'");
			$GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id = '{$this->meeting_id}'");
			unset($this->meeting_id);
		}		
	}

	public function testCalendarDate(){
		$cal = new Calendar('week');	
		$this->assertEquals('2012',$cal->date_time->year);
	}
	
	public function testCalendarAddActivity(){
		$cal = new Calendar('week');
		$cal->add_activities($GLOBALS['current_user']);
		$count1 = count($cal->acts_arr[$GLOBALS['current_user']->id]);		
		$cal->acts_arr = array();
				
		$this->meeting_id = uniqid();

        $db = $GLOBALS['db'];
        $db->query("INSERT INTO meetings (id,date_start,assigned_user_id) VALUES(".
                                            $db->quoted($this->meeting_id) . ", " .
                                            $db->convert($db->quoted("2012-01-02 00:00:00"), 'datetime') . ", " .
                                            $db->quoted($GLOBALS['current_user']->id) .")");

		$db->query("INSERT INTO meetings_users (id,meeting_id,user_id) VALUES (".
                                                $db->quoted(uniqid()).", ".
                                                $db->quoted($this->meeting_id).", ".
                                                $db->quoted($GLOBALS['current_user']->id).")");

		$cal->add_activities($GLOBALS['current_user']);	
		$count2 = count($cal->acts_arr[$GLOBALS['current_user']->id]);
		
		$this->assertEquals($count1 + 1, $count2, "Count of records should be one more after meeting added");
	}
	
	public function testCalendarLoadActivities(){        
		$cal = new Calendar('month');
		$cal->add_activities($GLOBALS['current_user']);
		$format = $GLOBALS['current_user']->getUserDateTimePreferences();
		$meeting = new Meeting();
		$meeting->meeting_id = uniqid();
		$meeting->date_start = $this->time_date->swap_formats("2012-01-01 11:00pm" , 'Y-m-d h:ia', $format['date'].' '.$format['time']);
		$meeting->name = "test";
		$cal->acts_arr = array();		
		$cal->acts_arr[$GLOBALS['current_user']->id] = array();
		$cal->acts_arr[$GLOBALS['current_user']->id][] = new CalendarActivity($meeting);	
		$cal->load_activities();
			
		$this->assertEquals($cal->items[0]['time_start'],$this->time_date->swap_formats("2012-01-01 11:00pm" , 'Y-m-d h:ia', $format['time']),"Time should remain the same after load_activities");		
	}

	public function testHandleOffset(){
		$gmt_today =  $this->time_date->nowDb();
		$date1 = $this->time_date->handle_offset($gmt_today, $GLOBALS['timedate']->get_db_date_time_format());
		$date2 = $this->time_date->nowDb();
		$this->assertEquals($date1, $date2, "HandleOffset should be equaivalent to nowDb");
	}

	public function testUserDateFormat(){
		$gmt_default_date_start = $this->time_date->get_gmt_db_datetime();
		$date1 = $this->time_date->handle_offset($gmt_default_date_start, $GLOBALS['timedate']->get_date_time_format());
		$date2 = $this->time_date->asUser($this->time_date->getNow());
		$this->assertEquals($date1, $date2, "HandleOffset should be equaivalent to nowDb");
	}	
	


}
