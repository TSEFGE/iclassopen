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


require_once('tests/service/RestTestCase.php');

/**
 * @bug 43368 - Bad Content-Type on SugarCRM REST API Interface
 */

class Bug43368Test extends RestTestCase
{

    public function setUp()
    {
        global $current_user;
        parent::setUp();
        //Create an anonymous user for login purposes/
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->status = 'Active';
        $current_user->is_admin = 1;
        $current_user->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

     /**
     * _makeRestCall
     *
     * This function helps wrap the REST call using the CURL libraries
     *
     * @param $method String name of the method to call
     * @param $parameters Mixed array of arguments depending on the method call
     *
     * @return mixed JSON decoded response made from REST call
     */
    protected function _makeRESTCall($method)
    {
        // specify the REST web service to interact with
        $url = $GLOBALS['sugar_config']['site_url'].'/service/v4/rest.php';
        // Open a curl session for making the call
        $curl = curl_init($url);
        // set URL and other appropriate options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        // build the request URL
        $json = json_encode(array());
        $postArgs = "method=$method&input_type=JSON&response_type=JSON&rest_data=$json";
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        // Make the REST call, returning the result
        $response = curl_exec($curl);
        // Close the connection
        $return = curl_getinfo($curl);
        curl_close($curl);

        // Convert the result from JSON format to a PHP array
        return $return;
    }

    /**
     * @group 41523
     */
    public function testRestReturnContentType()
    {
        $results = $this->_makeRESTCall('get_server_info');
        $this->assertEquals('application/json; charset=UTF-8', $results['content_type']);

    }
}
