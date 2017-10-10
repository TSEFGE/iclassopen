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
 * Bug #63559
 * Check if SOAP Sync from Outlook for Calls/Meetings
 * returns proper IDs whether we use outlook_id for sync,
 * or by matching given fields
 *
 * @author avucinic@sugarcrm.com
 * @ticket 63559
 */
class Bug63559Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();

        SugarTestHelper::tearDown();
    }

    /**
     * Test if set_entries returns proper IDs for Calls/Meetings
     * with set outlook_id or with equal fields
     *
     * @param $module - Module name
     * @param $outlookID - Outlook ID (must always be set, for the OPI Sync check to be initiated)
     * @param $nameValueLists - name-value lists for set_entries call
     *
     * @dataProvider dataProvider
     * @group 63559
     */
    public function testSetEntriesOPISync($module, $outlookID, $nameValueLists)
    {
        $meeting = SugarTestMeetingUtilities::createMeeting();
        // Set the fields using $nameValueLists, and resave the meeting
        foreach ($nameValueLists[0] as $nameValue) {
            $meeting->$nameValue['name'] = $nameValue['value'];
        }
        $meeting->outlook_id = $outlookID;
        $meeting->save();

        // Mock $server and $server->wsdl
        $server = $this->getMock('soap_server', array('register'));
        $server->wsdl = $this->getMock('wsdl', array('addComplexType'));
        // Need name space to be set
        $NAMESPACE = '';
        require_once('soap/SoapSugarUsers.php');
        require_once('soap/SoapError.php');
        // Unset $NAMESPACE, it was only needed in SoapSugarUsers.php
        unset($NAMESPACE);

        $actual = handle_set_entries($module, $nameValueLists);

        $this->assertEquals($meeting->id, $actual['ids'][0], 'Meeting not synced properly.');
    }

    public static function dataProvider()
    {
        return array(
            // Test case that will be synced by outlook_id
            array(
                'Meetings', 'outlook_id_test_same',
                array(
                    0 => array(
                        array(
                            'name' => 'outlook_id',
                            'value' => 'outlook_id_test_same',
                        ),
                        array(
                            'name' => 'name',
                            'value' => 'OPI Test',
                        ),
                        array (
                            'name' => 'date_start',
                            'value' => '2013-05-05 21:00:00',
                        ),
                        array (
                            'name' => 'date_end',
                            'value' => '2013-05-05',
                        ),
                        array (
                            'name' => 'duration_hours',
                            'value' => '0',
                        ),
                        array (
                            'name' => 'duration_minutes',
                            'value' => '30',
                        ),
                        array (
                            'name' => 'status',
                            'value' => 'Held',
                        ),
                    ),
                )
            ),
            // Test case that will compare fields for sync
            array(
                'Meetings', 'outlook_id_test_different',
                array(
                    0 => array(
                        array(
                            'name' => 'outlook_id',
                            'value' => 'outlook_id_test',
                        ),
                        array(
                            'name' => 'name',
                            'value' => 'OPI Test',
                        ),
                        array (
                            'name' => 'date_start',
                            'value' => '2013-05-05 21:00:00',
                        ),
                        array (
                            'name' => 'date_end',
                            'value' => '2013-05-05',
                        ),
                        array (
                            'name' => 'duration_hours',
                            'value' => '0',
                        ),
                        array (
                            'name' => 'duration_minutes',
                            'value' => '30',
                        ),
                        array (
                            'name' => 'status',
                            'value' => 'Held',
                        ),
                    ),
                )
            )
        );
    }
}
