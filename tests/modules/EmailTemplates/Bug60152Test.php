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


/**
 * Bug #60152
 * Using Alert Template Variables in the TinyMCE Editor Link replaces '{' and '}' with '%7B' and '%7D'
 *
 * @author mgusev@sugarcrm.com
 * @ticked 60152
 */
class Bug60152Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test asserts that body_html has variables after cleanBean call
     *
     * @group 60152
     * @dataProvider dataProvider
     * @return void
     */
    public function testCleanBean($html, $needle)
    {
        $bean = new EmailTemplate();
        $bean->body_html = $html;
        $bean->cleanBean();
        $this->assertContains($needle, $bean->body_html);
    }

    static public function dataProvider()
    {
        return array(
            array(
                '<a href="{::test::}">test</a>',
                '{::test::}'
            )
        );
    }
}
