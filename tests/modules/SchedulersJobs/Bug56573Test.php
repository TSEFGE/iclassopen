<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'modules/SchedulersJobs/SchedulersJob.php';

/**
 * Bug #56537 : Schedule Jobs don't work with classes
 *
 * @ticket 56537
 */
class Bug56573Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string job id
     */
    protected $id;

    public function setUp()
    {
        $this->id = null;
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        if (!empty($this->id)) {
            $job = new SchedulersJob();
            $job->mark_deleted($this->id);
        }
        SugarTestHelper::tearDown();
    }

    protected function execJob($name, $data)
    {
        require_once('include/SugarQueue/SugarJobQueue.php');

        $job = new SchedulersJob();
        $job->name = "Bug56573Test Alert Job - '{$name}'";
        $job->target = "class::Bug56573TestJob";
        $job->data = $data;
        $job->assigned_user_id = $GLOBALS['current_user']->id;

        // Add the Job the the job Queue
        $jq = new SugarJobQueue();
        $jq->submitJob($job);
        // Run the job
        $job->runJob();

        // Save id for cleaning
        $this->id = $job->id;

        return $job;
    }

    public static function provider()
    {
        return array(
            array(
                'Success',
                implode(
                    ',',
                    array(
                        'status' =>  SchedulersJob::JOB_STATUS_RUNNING,
                        'resolution' => SchedulersJob::JOB_SUCCESS,
                        'return' => true
                    )
                ),
                SchedulersJob::JOB_STATUS_DONE,
                SchedulersJob::JOB_SUCCESS
            ),
            array(
                'Failure',
                implode(
                    ',',
                    array(
                        'status' => SchedulersJob::JOB_STATUS_RUNNING,
                        'resolution' => SchedulersJob::JOB_FAILURE,
                        'return' => false
                    )
                ),
                SchedulersJob::JOB_STATUS_DONE,
                SchedulersJob::JOB_FAILURE

            ),
            array(
                'Queue',
                implode(
                    ',',
                    array(
                        'status' => SchedulersJob::JOB_STATUS_QUEUED,
                        'resolution' => SchedulersJob::JOB_PARTIAL,
                        'return' => false
                    )
                ),
                SchedulersJob::JOB_STATUS_QUEUED,
                SchedulersJob::JOB_PARTIAL
            ),
        );
    }

    /**
     * Test if runJob() sets proper values of status/resolution
     *
     * @dataProvider provider
     * @group 56537
     */
    public function testJob($name, $data, $status, $resolution)
    {
        $job = $this->execJob($name, $data);
        $this->assertEquals($resolution, $job->resolution, "Wrong resolution");
        $this->assertEquals($status, $job->status, "Wrong status");
    }
}

/**
 * Job Class for testing SchedulersJob
 */
class Bug56573TestJob implements RunnableSchedulerJob
{
    public function run($data)
    {
        // Pull all the test data
        $data = explode(',', $data);

        // Set status and resolution
        $this->job->status = $data[0];
        $this->job->resolution = $data[1];

        return $data[2];
    }

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }
}
