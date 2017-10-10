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



require_once('include/utils/LogicHook.php');
require_once('modules/InboundEmail/InboundEmail.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Cases/Case.php');
require_once('modules/Emails/Email.php');


/*
 * This simulates the processing of an inbound email that is to be linked to a case.
 * We create a logic hook attached to after_relationship_add, which should only run once.
 * Logic is borrowed heavily from Bug46122Test.php
 * @ticket 49784
 */
class Bug49784Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $hasCustomCasesLogicHookFile = false;
    var $casesHookFile = 'custom/modules/Cases/logic_hooks.php';
    var $casesCountFile = 'custom/modules/Cases/count.php';
    var $user ='';
    var $case ='';
    var $account='';
    var $email ='';

    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->user;

        //Setup logichook files
        if(file_exists($this->casesHookFile))
        {
            $this->hasCustomCasesLogicHookFile = true;
            copy($this->casesHookFile, $this->casesHookFile.'.bak');
        }
        $hook_array['after_relationship_add'][] = Array(1, 'Cases increment count', $this->casesCountFile,'CaseCount', 'countMe');
        write_array_to_file("hook_array", $hook_array, $this->casesHookFile);
        $this->useOutputBuffering = false;
        LogicHook::refreshHooks();

        //now  write out the script that the logichook executes.  This will keep track of times called
        global $hookRunCount;
        $hookRunCount = 0;
        $fileCont = '<?php class CaseCount {
            function countMe($bean, $event, $arguments){
                global $hookRunCount;
                if($event =="after_relationship_add" && $arguments["module"]=="Cases" && $arguments["related_module"]=="Emails")
                    $hookRunCount++;
                }}?>';
        file_put_contents($this->casesCountFile, $fileCont);


    	//setup test account for case
		$this->account = new Account();
        $this->account->name = 'test account for bug 39855';
        $this->account->assigned_user_id = 'SugarUser';
        $this->account->save();

        //create case
        $this->case = new aCase();
        $this->case->name = 'test case for unitTest 49784';
        $this->case->account_id = $this->account->id;
        $this->case->status = 'New';
        $this->case->save();
        //retrieve so we have latest info (case number)
        $this->case->retrieve($this->case->id);


        //create email with case in subject
        $this->email = new Email();
        $this->email->type = 'inbound';
        $this->email->status = 'unread';
        $this->email->from_addr_name = $this->email->cleanEmails("sender@domain.eu");
        $this->email->to_addrs_names = $this->email->cleanEmails("to@domain.eu");
        $this->email->cc_addrs_names = $this->email->cleanEmails("cc@domain.eu");
        $this->email->name = 'RE: [CASE:'.$this->case->case_number.'] '.$this->case->name;
        $this->email->save();

    }

    public function tearDown()
    {
        //Remove the custom logic hook files
        if($this->hasCustomCasesLogicHookFile && file_exists($this->casesHookFile.'.bak'))
        {
            copy($this->casesHookFile.'.bak', $this->casesHookFile);
            unlink($this->casesHookFile.'.bak');
        } else if(file_exists($this->casesHookFile)) {
            unlink($this->casesHookFile);
        }
        unlink($this->casesCountFile);
        $GLOBALS['db']->query("delete from emails where id='{$this->email->id}'");
        $GLOBALS['db']->query("delete from accounts where id='{$this->account->id}'");
        $GLOBALS['db']->query("delete from cases where id='{$this->case->id}'");


        unset($GLOBALS['logic_hook']);
        unset($this->account);
        unset($this->case);
        unset($this->email);
        unset($this->user);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testCaseHandlingOfInboundEmail()
    {
        //lets make sure the file exists
        $this->assertFileExists($this->casesCountFile, 'file to be run from logic hook that keeps track of execution count was not written');

        //create a new inbound email and call process that links to case
        $i = new InboundEmail();
        $i->handleCaseAssignment($this->email);

        //make sure the logic hook only ran once
        $this->assertEquals(1, $GLOBALS['hookRunCount'], 'Logic hook should only have run once during the inbound email processing.');
    }

}

?>