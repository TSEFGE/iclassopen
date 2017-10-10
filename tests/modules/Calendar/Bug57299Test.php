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


require_once ('modules/Meetings/MeetingFormBase.php');

/**
 * Bug #57299
 *
 * Calendar  |  Meetings with status held are not displaying in the Calendar
 * @ticket 57299
 * @author imatsiushyna@sugarcrm.com
 */

class Bug57299Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    /**
     * @var FormBase
     */
    protected $formBase = null;

    /**
     * @var Bean
     */
    protected $bean = null;

    /**
     * @var module name
     */
    protected $name = 'Meetings';

    /**
     * @var User
     */
    protected $user = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('mod_strings', array($this->name));

        $this->user = $GLOBALS['current_user'];

        $this->user->setPreference('datef', 'm/d/Y');
        $this->user->setPreference('timef', 'h:ia');
        $this->user->setPreference('timezone', 'UTC');

    }

    public function tearDown()
    {
        $_POST = array();

        $this->bean->db->query("DELETE FROM meetings WHERE id = '". $this->bean->id ."'");
        $this->bean->db->query("DELETE FROM {$this->bean->rel_users_table} WHERE meeting_id = '". $this->bean->id ."'");

        parent::tearDown();
        SugarTestHelper::tearDown();
    }

    public function getPostData()
    {
        return array(
            'module' => 'Calendar',
            'name' => 'Bug57299_'.time(),
            'current_module' => $this->name,
            'record' => '',
            'user_invitees' => '1',
            'contact_invitees' => '',
            'lead_invitees' => '',
            'send_invites' => '',
            'edit_all_recurrences' => true,
            'repeat_parent_id' => '',
            'repeat_type' => '',
            'repeat_interval' => '',
            'repeat_count' => '',
            'repeat_until' => '',
            'repeat_dow' => '',
            'appttype' => $this->name,
            'type' => 'Sugar',
            'date_start' => '11/25/2012 12:00pm',
            'parent_type' => 'Accounts',
            'parent_name' => '',
            'parent_id' => '',
            'date_end' => '11/25/2012 12:15pm',
            'location' => '',
            'duration' => 900,
            'duration_hours' => 0,
            'duration_minutes' => 15,
            'reminder_checked' => 1,
            'reminder_time' => 1800,
            'email_reminder_checked' => 0,
            'email_reminder_time' => 60,
            'assigned_user_name' => 'Administrator',
            'assigned_user_id' => 1,
            'update_fields_team_name_collection' => '',
            'team_name_new_on_update' => false,
            'team_name_allow_update' => '',
            'team_name_allow_new' => true,
            'team_name' => 'team_name',
            'team_name_field' => 'team_name_table',
            'arrow_team_name' => 'hide',
            'team_name_collection_0' => 'Global',
            'id_team_name_collection_0' => 1,
            'primary_team_name_collection' => 0,
            'description' => '',
        );
    }

    /**
     * providerData
     *
     * @return Array values for testing
     */
    public function providerData()
    {
        return array(
            array('Held', true),
            array('Held', false),
        );
    }

    /**
     * @group 57299
     * Test that new Meeting created from module Calendar save in database correctly
     *
     * @dataProvider providerData
     * @return void
     */
    public function testDisplaysMeetingWithStatusHeldInCalendar($status, $return_module)
    {
        $_POST = $this->getPostData();
        $_POST['status'] = $status;
        $_POST['return_module'] = ($return_module) ? 'Calendar' : '';
        $_REQUEST = $_POST;

        $this->formBase = new MeetingFormBase();
        $this->bean = $this->formBase->handleSave('', false, false);

        $sql = "SELECT * FROM {$this->bean->rel_users_table} WHERE meeting_id = '". $this->bean->id . "'";
        $result = $this->bean->db->query($sql);
        $rows = $this->bean->db->fetchByAssoc($result);

        //assert that if we return name of Calendar module
        //create relation between created Meeting and current User
        if($return_module)
        {
            $this->assertNotNull($rows);
        }
        else
        {
            $this->assertFalse($rows);
        }
    }
}
