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
require_once 'modules/OAuthTokens/OAuthToken.php';

class OAuthTokenTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        OAuthToken::deleteByUser($GLOBALS['current_user']->id);
        SugarTestHelper::tearDown();
    }

    /**
     * @ticket 62822
     */
    public function testCleanup()
    {
        // create request token
        $tok = OAuthToken::generate();
        $tok->consumer = create_guid();
        $tok->setState(OAuthToken::REQUEST);
        $tok->assigned_user_id = $GLOBALS['current_user']->id;
        $tok->save();
        // create invalid token
        $tok = OAuthToken::generate();
        $tok->consumer = create_guid();
        $tok->setState(OAuthToken::INVALID);
        $tok->assigned_user_id = $GLOBALS['current_user']->id;
        $tok->save();

        $cnt = $GLOBALS['db']->getOne("SELECT count(*) c FROM {$tok->table_name} WHERE assigned_user_id=".$GLOBALS['db']->quoted($GLOBALS['current_user']->id));
        $this->assertEquals(2, $cnt, "Wrong number of tokens in the table");

        // set time way in the past
        $GLOBALS['db']->query("UPDATE {$tok->table_name} SET token_ts=1 WHERE assigned_user_id=".$GLOBALS['db']->quoted($GLOBALS['current_user']->id));

        // run cleanup
        OAuthToken::cleanup();

        // ensure tokens are gone
        $cnt = $GLOBALS['db']->getOne("SELECT count(*) c FROM {$tok->table_name} WHERE assigned_user_id=".$GLOBALS['db']->quoted($GLOBALS['current_user']->id));
        $this->assertEquals(0, $cnt, "Tokens were not deleted");
    }
}
