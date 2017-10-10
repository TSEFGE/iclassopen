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
 * Bug50780 - Due to the recent security fixes for the web services, subqueries on tables no longer work as expected.
 * This has presented a problem to some of our partner's since they were relying on the subqueries to return
 * relationship information on records. We could resolve this by adding back more tables to the allowed list of tables
 * to be queried in a subquery, but this has been voted against from most of the engineering staff. Another approach
 * would be to add enhancements to our API to allow for querying limited result sets for relationship data. Adding
 * offset and limit support to the get_relationships call is one such approach.
 */

require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';

class Bug50780Test extends SOAPTestCase
{
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::setUp();
        $this->_login(); // Logging in just before the SOAP call as this will also commit any pending DB changes


        for ($x = 0; $x < 4; $x++) {
            $mid = SugarTestMeetingUtilities::createMeeting();
            SugarTestMeetingUtilities::addMeetingUserRelation($mid->id, self::$_user->id);
        }

        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingUsers();

        parent::tearDown();
    }


    public function testGetRelationshipReturnAllMeetings()
    {
        $result = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 0,
                'limit' => false)
        );

        $this->assertEquals(4, count($result['entry_list']));
    }

    public function testGetRelationshipReturnNothingWithOffsetSetHigh()
    {
        $result = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 5,
                'limit' => 4)
        );

        $this->assertEquals(0, count($result['entry_list']));
    }

    public function testGetRelationshipReturnThirdMeeting()
    {
        $result = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 2,
                'limit' => 1)
        );

        $this->assertEquals(1, count($result['entry_list']));
    }

    public function testGetRelationshipOffsetDoesntReturnSameRecords()
    {
        $result1 = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name', 'date_entered'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 0,
                'limit' => 2)
        );

        $this->assertEquals(2, count($result1['entry_list']));

        $result2 = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Users',
                'module_id' => self::$_user->id,
                'link_field_name' => 'meetings',
                'related_module_query' => '',
                'related_fields' => array('id', 'name', 'date_entered'),
                'related_module_link_name_to_fields_array' => '',
                'deleted' => 0,
                'order_by' => 'date_entered',
                'offset' => 2,
                'limit' => 2)
        );

        $this->assertEquals(2, count($result2['entry_list']));
    }
}