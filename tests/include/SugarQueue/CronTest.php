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
require_once 'include/SugarQueue/SugarCronJobs.php';
require_once 'modules/SchedulersJobs/SchedulersJob.php';

class CronTest extends Sugar_PHPUnit_Framework_TestCase
{
    static public $jobCalled = false;
    public $cron_config;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        // clean up queue
		$GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
    }

    public static function tearDownAdterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        $this->jq = $jobq = new SugarCronJobs();
        self::$jobCalled = false;
        if(isset($GLOBALS['sugar_config']['cron'])) {
            $this->config_cron = $GLOBALS['sugar_config']['cron'];
        }
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
        if(isset($GLOBALS['sugar_config']['cron'])) {
            $GLOBALS['sugar_config']['cron'] = $this->config_cron;
        } else {
            unset($GLOBALS['sugar_config']['cron']);
        }
    }

    public function testConfig()
    {
        $GLOBALS['sugar_config']['cron'] = array('max_cron_jobs' => 12, 'max_cron_runtime' => 34, 'min_cron_interval' => 56);
        $jobq = new SugarCronJobs();
        $this->assertEquals(12, $jobq->max_jobs, "Wrong setting for max_jobs");
        $this->assertEquals(34, $jobq->max_runtime, "Wrong setting for max_runtime");
        $this->assertEquals(56, $jobq->min_interval, "Wrong setting for min_interval");
    }

    public function testThrottle()
    {
        $this->jq->throttle();
        $this->assertFalse($this->jq->throttle(), "Should prohibit second time");
        // wait a bit
        sleep(2);
        $this->jq->min_interval = 1;
        $this->assertTrue($this->jq->throttle(), "Should allow after delay");
    }

    public static function cronJobFunction()
    {
        self::$jobCalled = true;
        return true;
    }

    public static function cronJobLongFunction()
    {
        self::$jobCalled = true;
        sleep(2);
        return true;
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

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();
        $this->assertTrue(self::$jobCalled, "Job was not called");
        $this->assertTrue($this->jq->runOk(), "Wrong OK flag");
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEmpty(session_id(), "Session not destroyed");
    }

    public function testQueueFailJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::test::test";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk(), "Wrong OK flag");
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEmpty(session_id(), "Session not destroyed");
    }

    public function testJobsCount()
    {
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Unit test Job 1";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;
        // job 2 - newer, should not be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2012-01-01 12:00:00';
        $job->name = "Unit test Job 2";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid2 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->max_jobs = 1; // only one job per cycle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue(self::$jobCalled, "Job was not called");
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        // test that second one wasn't run
        $job = new SchedulersJob();
        $job->retrieve($jobid2);
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
    }

    public function testJobsTimeCutoff()
    {
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Unit test Job 1";
        $job->target = "function::CronTest::cronJobLongFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;
        // job 2 - newer, should not be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2012-01-01 12:00:00';
        $job->name = "Unit test Job 2";
        $job->target = "function::CronTest::cronJobLongFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid2 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->max_jobs = 10;
        $this->jq->max_runtime = 1; // only 1 sec runtime
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue(self::$jobCalled, "Job was not called");
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        // test that second one wasn't run
        $job = new SchedulersJob();
        $job->retrieve($jobid2);
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
    }

    public function testJobsCleanup()
    {
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->update_date_modified = false;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->date_modified = '2010-01-01 12:00:00';
        $job->name = "Unit test Job 1";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse(self::$jobCalled, "Job was called");
        $this->assertFalse($this->jq->runOk(), "Wrong OK flag");
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEmpty(session_id(), "Session not destroyed");
    }

}
