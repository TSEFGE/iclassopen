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


require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';

/**
 * Bug 51723
 *  SOAP::get_entries() call fails to export portal_name field
 * @ticket 51723
 * @author arymarchik@sugarcrm.com
 */
class Bug51723Test extends SOAPTestCase
{
    private $_contact;
    private $_opt = null;

    public function setUp()
    {
        $this->markTestIncomplete("Test breaking on CI, working with dev to fix");
        $administration = new Administration();
        $administration->retrieveSettings();
        if(isset($administration->settings['portal_on']))
        {
            $this->_opt = $administration->settings['portal_on'];
        }
        $administration->saveSetting('portal', 'on',  1);

        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php?wsdl';
        parent::setUp();
        $this->_login();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $this->_contact = new Contact();
        $this->_contact->last_name = "Contact #bug51723";
        $this->_contact->id = create_guid();
        $this->_contact->new_with_id = true;
        $this->_contact->team_id = 1;
        $this->_contact->save();
    }

    public function tearDown()
    {
        //$this->_contact->mark_deleted($this->_contact->id);
        parent::tearDown();

        $administration = new Administration();
        $administration->retrieveSettings();
        if($this->_opt === null)
        {
            if(isset($administration->settings['portal_on']))
            {
                $administration->saveSetting('portal', 'on', 0);
            }
        }
        else
        {
            $administration->saveSetting('portal', 'on',  $this->_opt);
        }
    }

    /**
     * Testing SOAP method get_entries for existing "portal_name" field
     * @group 51723
     */
    public function testPortalNameInGetEntries()
    {
        $fields = array('portal_name', 'first_name', 'last_name');
        $result = $this->_soapClient->call(
            'get_entries',
            array('session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'ids' => array($this->_contact->id),
                'select_fields' => $fields
            )
        );
        // replacement of $this->assertCount()
        if(count($result['entry_list']) != 1)
        {
            $this->fail('Can\'t get entry list');
        }

        foreach ($result['entry_list'][0]['name_value_list'] as $key => &$value)
        {
            if(($index = array_search($value['name'], $fields, true)) !== false)
            {
                unset($fields[$index]);
            }
            else
            {
                $this->fail('Wrong field in selected fields:' . $value['name']);
            }
        }
        if(count($fields) != 0)
        {
            $this->fail('Can\'t get expected values:' . implode(',', $fields));
        }
    }

}