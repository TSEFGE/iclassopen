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
require_once 'modules/SchedulersJobs/SchedulersJob.php';

class JobQueueTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->jq = new TestSugarJobQueue();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testSubmitJob()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $now = $GLOBALS['timedate']->nowDb();
        $job->name = "Unit test Job 1";
        $job->target = "test::test";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $id = $this->jq->submitJob($job);

        $this->assertNotEmpty($id, "Bad job ID");
        $job = new SchedulersJob();
        $job->retrieve($id);
        $this->assertEquals(SchedulersJob::JOB_PENDING, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
        $this->assertEquals($now, $job->execute_time_db, "Wrong execute time");
    }

    public function testGetJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $now = $GLOBALS['timedate']->nowDb();
        $job->name = "Unit test Job 1";
        $job->target = "test::test";
        $id = $this->jq->submitJob($job);

        $this->assertNotEmpty($id, "Bad job ID");
        $job = $this->jq->getJob($id);
        $this->assertEquals(SchedulersJob::JOB_PENDING, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
        $this->assertEquals($now, $job->execute_time_db, "Wrong execute time");

        $job = $this->jq->getJob("nosuchjob");
        $this->assertNull($job, "Bad return on non-existing job");
    }

    public function testCleanup()
    {
        $job = new SchedulersJob();
        $job->update_date_modified = false;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $GLOBALS['timedate']->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->date_modified = '2010-01-01 12:00:00';
        $job->name = "Unit test Job 1";
        $job->target = "test::test";
        $job->save();
        $jobid = $job->id;
        $this->jq->cleanup();

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
    }

    public function testDelete()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->name = "Unit test Job 1";
        $job->target = "test::test";
        $job->save();
        $jobid = $job->id;
        $this->jq->deleteJob($jobid);

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEmpty($job->id, "Job not deleted");
    }

    public function testGetNextJob()
    {
        // should get only jobs with status QUEUED, in date_entered order, and mark them as running
        // Clean up the queue
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE status='".SchedulersJob::JOB_STATUS_QUEUED."'");
        $job = $this->jq->nextJob("unit test");
        $this->assertNull($job, "Extra job found");
        // older job
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Old Job";
        $job->target = "test::test";
        $job->save();
        $jobid1 = $job->id;
        // another job, later date
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2012-01-01 12:00:00';
        $job->name = "Newer Job";
        $job->target = "test::test";
        $job->save();
        $jobid2 = $job->id;
        // job with execute date in the future
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $GLOBALS['timedate']->getNow()->modify("+3 days")->asDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Future Job";
        $job->target = "test::test";
        $job->save();
        $jobid3 = $job->id;
        //running job
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Running Job";
        $job->target = "test::test";
        $job->save();
        $jobid4 = $job->id;
        // done job
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_DONE;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Done Job";
        $job->target = "test::test";
        $job->save();
        $jobid5 = $job->id;
        // get the first one
        $job = $this->jq->nextJob("unit test");
        $this->assertEquals($jobid1, $job->id, "Wrong job fetched");
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals("unit test", $job->client, "Wrong client");
        // check that DB record matches
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals("unit test", $job->client, "Wrong client");
        // get the second one
        $job = $this->jq->nextJob("unit test");
        $this->assertEquals($jobid2, $job->id, "Wrong job fetched");
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals("unit test", $job->client, "Wrong client");
        // try to get the third one, should get null
        $job = $this->jq->nextJob("unit test");
        $this->assertNull($job, "Extra job found");
    }

}

class TestSugarJobQueue extends SugarJobQueue
{
    public function getJob($jobId)
    {
        return parent::getJob($jobId);
    }
}
