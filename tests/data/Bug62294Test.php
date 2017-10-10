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
 * Bug #62294
 * Read-Only Roles on Addresses Cause Street to Blank on Edit
 *
 * @author ekolotaev@sugarcrm.com
 * @ticked 62294
 */
class Bug62294Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testAddAddressStreets()
    {
        $bean = BeanFactory::getBean('Accounts');
        $bean->billing_address_street = null;
        $bean->billing_address_street_2 = 'Street 2';

        $bean->add_address_streets('billing_address_street');
        $this->assertNull($bean->billing_address_street);
    }
}
