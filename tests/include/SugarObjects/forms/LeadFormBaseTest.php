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
 * LeadFormBaseTest.php
 *
 */

require_once('modules/Leads/LeadFormBase.php');

class LeadFormBaseTest extends Sugar_PHPUnit_Framework_TestCase {

var $form;
var $lead1;

public function setup()
{
    $GLOBALS['db']->query("DELETE FROM leads WHERE first_name = 'Mike' AND last_name = 'TheSituationSorrentino'");
    $this->form = new LeadFormBase();
    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Leads');

    //Create a test Lead
    $this->lead1 = SugarTestLeadUtilities::createLead();
    $this->lead1->first_name = 'Collin';
    $this->lead1->last_name = 'Lee';
    $this->lead1->save();
    $this->lead1->emailAddress->addAddress('clee@sugarcrm.com', true, false);
    $this->lead1->emailAddress->save($this->lead1->id, $this->lead1->module_dir);
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    SugarTestLeadUtilities::removeAllCreatedLeads();
    SugarTestLeadUtilities::removeCreatedLeadsEmailAddresses();
    unset($this->form);
    unset($this->lead1);
}


/**
 * leadsProvider
 *
 */
public function leadsProvider()
{
    return array(
        array('Collin', 'Lee', true),
        array('', 'Lee', true),
        array('Mike', 'TheSituationSorrentino', false)
    );
    
}


/**
 * testCreatingDuplicateLead
 *
 * @dataProvider leadsProvider
 */
public function testCreatingDuplicateLead($first_name, $last_name, $hasDuplicate)
{
    $_POST['first_name'] = $first_name;
    $_POST['last_name'] = $last_name;
    $_POST['Leads0emailAddresss0'] = 'clee@sugarcrm.com';

    $rows = $this->form->checkForDuplicates();

    if($hasDuplicate)
    {
        $this->assertTrue(count($rows) > 0, 'Assert that checkForDuplicates returned matches');
        $this->assertEquals($rows[0]['last_name'], $last_name, 'Assert duplicate row entry last_name is ' . $last_name);
        $output = $this->form->buildTableForm($rows);
        $this->assertRegExp('/\&action\=DetailView\&record/', $output, 'Assert we have the DetailView links to records');
    } else {
        $this->assertTrue(empty($rows), 'Assert that checkForDuplicates returned no matches');
    }
}

}