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

require_once('include/MVC/View/views/view.detail.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Opportunities/views/view.detail.php');

class Bug51980Test extends Sugar_PHPUnit_Framework_OutputTestCase {
// class Bug51980Test extends  Sugar_PHPUnit_Framework_TestCase{
    private $user;
    private $opp;

	public function setUp()
    {
        $this->markTestIncomplete("Disabling after discussing with Eddy.  Eddy will take a look at why this is breaking Stack 66 build");
        //create user
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->user->default_team_name = 'global';
        $this->user->is_admin = 1;
        $this->user->save();
        $this->user->retrieve($this->user->id);
        $GLOBALS['current_user'] = $this->user;
        //set some global values that will help with the view
        $_REQUEST['action'] = $GLOBALS['action'] = 'DetailView';
        $_REQUEST['module'] = $GLOBALS['module'] = 'Opportunities';

        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], "Opportunities");


        //create opportunity
        $name = 'Test_51980_'.time();
        $this->opp = new Opportunity();
        $this->opp->name = $name;
        $this->opp->amount = '1000000';
        $this->opp->account_id = '1';
        $this->opp->team_id = '1';
        $this->opp->currency_id = -99;
        $this->opp->save();

	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'Test_51980_%'");
        unset($this->user);
        unset($this->opp);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_strings']);
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($GLOBALS['current_user']);
    }

     /**

     */
	public function testDateProperUserFormat()
	{
        //manipulate the date on the bean AFTER it's been created, making sure it is
        //a non standard date format.  We are NOT saving, we just want to mess up the UI presentation.
        $closedate = '2014/12/23';
        $this->opp->date_closed = $closedate;

        //create the view and display opportunity
		$ovd = new OpportunitiesViewDetail();
        $ovd->bean = $this->opp;
        $ovd->action = 'DetailView';
        $ovd->module = 'Opportunities';
        $ovd->type = 'detail';
        $ovd->init($this->opp);
        $ovd->preDisplay();
        $ovd->display();

        //grab the value of what the properly formatted date of the string we injected should be.  Note that this calls the
        //timedate function twice, once to grab the user format, and once to create the string
        $formatted_date = $GLOBALS['timedate']->asUserDate($GLOBALS['timedate']->fromString($closedate, $this->user), $this->user);
        //escape the characters so we can use as a regex.  turn '/' into '\/'
        $formatted_date = str_replace('/','\\/',$formatted_date);
        // lets make sure the date shows up properly formatted in the detail view output.
        $this->expectOutputRegex("/>$formatted_date</");

    }
}