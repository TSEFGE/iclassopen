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

require_once('tests/service/SOAPTestCase.php');

/**
 * Bug #38858
 * set_relationships will not delete relationships via API
 *
 * @author mgusev@sugarcrm.com
 * @ticked 38858
 */
class Bug38858Test extends SOAPTestCase
{
    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var Contact
     */
    protected $contact = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->_soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::_setupTestUser();
        parent::setUp();

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact();

        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact->id);
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        parent::tearDown();
        parent::_tearDownTestUser();

        SugarTestHelper::tearDown();
    }

    /**
     * Test checks that relationship between account & contact can be removed by soap
     * 
     * @group 38858
     * @return void
     */
    public function testDeletionBySetRelationships()
    {
        $this->_login();

        $result = $this->_soapClient->call('get_relationships', array(
                'session' => $this->_sessionId,
                'module_name' => 'Accounts',
                'module_id' => $this->account->id,
                'link_field_name' => 'contacts',
                'related_module_query' => "",
                'link_module_fields' => array('id'),
                'deleted' => '1',
        ));
        $this->assertEquals(1, count($result['entry_list']), 'Response is incorrect');

        $contact = reset($result['entry_list']);
        $this->assertEquals($this->contact->id, $contact['id'], 'Contact is incorrect');

        $result = $this->_soapClient->call('set_relationship', array(
            'session' => $this->_sessionId,
            'module_name' => $this->account->module_dir,
            'module_id' => $this->account->id,
            'link_field_name' => 'contacts',
            'related_ids' => array($this->contact->id),
            'name_value_list' => array(),
            'delete' => 1
        ));
        $this->assertEquals(1, $result['deleted'], 'Contact is not deleted');

        $result = $this->_soapClient->call('get_relationships', array(
            'session' => $this->_sessionId,
            'module_name' => 'Accounts',
            'module_id' => $this->account->id,
            'link_field_name' => 'contacts',
            'related_module_query' => "",
            'link_module_fields' => array('id'),
            'deleted' => '1',
        ));

        $this->assertEquals(0, count($result['entry_list']), 'Contact is present');
    }
}
