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
 * Bug #60442
 * ACL - Unable to reassign record between the users within the same role
 *
 * @author mgusev@sugarcrm.com
 * @ticked 60442
 */
class Bug60442Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test asserts that fetched row has more priority then property
     *
     * @group 60442
     * @return void
     */
    public function testIsOwner()
    {
        $bean = new SugarBean();
        $bean->id = create_guid();
        $bean->fetched_row['assigned_user_id'] = 1;
        $bean->assigned_user_id = 2;
        $this->assertTrue($bean->isOwner(1), 'Incorrect ownership');
    }
}
