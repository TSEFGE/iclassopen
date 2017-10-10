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

require_once 'include/SugarQueue/SugarJobQueue.php';
require_once 'include/SugarQueue/SugarCronRemoteJobs.php';

class CronRemoteTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // clean up queue
		$GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
		$GLOBALS['sugar_config']['job_server'] = "http://test.job.server/";
    }

    public static function tearDownAdterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['sugar_config']['job_server']);
    }

    public function setUp()
    {
        $this->jq = $jobq = new SugarCronRemoteJobs();
        $this->client = new CronHttpMock();
        $this->jq->setClient($this->client);
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
    }

    public function testQueueJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->client->return = json_encode(array("ok" => $job->id));

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue($this->jq->runOk());

        $this->assertEquals("http://test.job.server/submitJob", $this->client->call_url);
        parse_str($this->client->call_data, $qdata);
        $data = json_decode($qdata['data'], true);
        $this->assertEquals($jobid, $data['job']);
        $this->assertEquals($this->jq->getMyId(), $data['client']);
        $this->assertEquals($GLOBALS['sugar_config']['site_url'], $data['instance']);

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals($this->jq->getMyId(), $job->client, "Wrong client");
    }

    public function testServerFailure()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->client->return = ''; // return nothing

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk());
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

    public function testServerFailureWithError()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->client->return = 'This is not the server you are looking for';

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk());
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains('This is not the server you are looking for', $job->message, "Wrong message");
    }
}

class CronHttpMock extends SugarHttpClient
{
     public $call_url;
     public $call_data;
     public $return;
     public function callRest($url, $data) {
         $this->call_url = $url;
         $this->call_data = $data;
         return $this->return;
     }
}