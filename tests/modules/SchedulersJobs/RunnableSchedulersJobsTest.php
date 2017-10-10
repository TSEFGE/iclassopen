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


require_once 'modules/SchedulersJobs/SchedulersJob.php';
require_once 'tests/SugarTestUserUtilities.php';
require_once 'tests/SugarTestAccountUtilities.php';

class RunnableSchedulersJobsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $jobs = array();

    public function setUp()
    {
        $this->db = DBManagerFactory::getInstance();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
        if(!empty($this->jobs)) {
            $jobs = implode("','", $this->jobs);
            $this->db->query("DELETE FROM job_queue WHERE id IN ('$jobs')");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $ids = SugarTestAccountUtilities::getCreatedAccountIds();
        if(!empty($ids)) {
            SugarTestAccountUtilities::removeAllCreatedAccounts();
        }
    }

    protected function createJob($data)
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        foreach($data as $key => $val) {
            $job->$key = $val;
        }
        $job->save();
        $this->jobs[] = $job->id;
        return $job;
    }



    public function testRunnableJobRunClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $job = $this->createJob(array("name" => "Test Func", "status" => SchedulersJob::JOB_STATUS_RUNNING,
            "target" => "class::TestRunnableJob", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);

        $this->assertTrue($job->runnable_ran);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals($GLOBALS['current_user']->id, $job->user->id, "Wrong user");

        // function with args
        $job = $this->createJob(array("name" => "Test Func 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
                    "target" => "class::TestRunnableJob",
                    "data" => "function data", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertTrue($job->runnable_ran);
        $this->assertEquals($job->runnable_data, "function data", "Argument 2 doesn't match");
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals($GLOBALS['current_user']->id, $job->user->id, "Wrong user");
    }
}


class TestRunnableJob implements RunnableSchedulerJob
{
    private $job;

    public function run($data)
    {
        $this->job->runnable_ran = true;
        $this->job->runnable_data = $data;
        $this->job->user = $GLOBALS['current_user'];
        return $this->job->succeedJob();
    }

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }
}
