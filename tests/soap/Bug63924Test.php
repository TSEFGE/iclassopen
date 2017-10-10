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


require_once('SugarTestProspectUtilities.php');
require_once('tests/service/SOAPTestCase.php');

/**
 * Mock missing validate_user function
 * @return bool authentication always true
 */
function validate_user($user, $pass)
{
    return true;
}

/**
 * Bug #63924
 * "search_by_module" SOAP API (v1) not returning any results for "Person" type custom modules..
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 63924
 */
class Bug63924Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        SugarTestProspectUtilities::removeAllCreatedProspects();
        parent::tearDown();
    }

    /**
     * Test the function create_ical_array_from_string()
     *
     * @dataProvider prospectProvider
     */
    public function testSearchByModule(array $args, $query)
    {
        if (!function_exists('search_by_module')) {

            // Mock $server and $server->wsdl
            $server = $this->getMock('soap_server', array('register'));
            $server->wsdl = $this->getMock('wsdl', array('addComplexType'));
            // Need name space to be set
            $NAMESPACE = '';
            require_once('soap/SoapSugarUsers.php');
            require_once('soap/SoapError.php');
        }

        $prospect = SugarTestProspectUtilities::createProspect('', $args);
        $actual = search_by_module('', '', $query, array('Prospects'), 0, 30);
        $this->assertEquals($prospect->id, $actual['entry_list'][0]['id']);
    }

    public function prospectProvider()
    {
        $firstname = array(array('first_name' => 'Bug63924TestFirstName', 'last_name' => ''), 'Bug63924TestFirstName');
        $lastname = array(array('first_name' => '', 'last_name' => 'Bug63924TestLastName'), 'Bug63924TestLastName');
        $fullname = array(
            array('first_name' => 'Bug63924TestFirstName', 'last_name' => 'Bug63924TestLastName'),
            'Bug63924TestFirstName Bug63924TestLastName'
        );
        return array($firstname, $lastname, $fullname);
    }

}
