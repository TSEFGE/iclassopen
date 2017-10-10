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
 * Bug #58863
 * associated emails showing in parent record
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58863
 */
class Bug58863Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var Contact
     */
    protected $contact = null;

    /**
     * @var Email
     */
    protected $email = null;

    /**
     * @var Opportunity
     */
    protected $opportunity = null;

    /**
     * @var SugarApplication
     */
    protected $application = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->email = SugarTestEmailUtilities::createEmail();
        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity('', $this->account);

        $this->contact->account_id = $this->account->id;
        $this->contact->save();

        $this->opportunity->load_relationship('contacts');
        $this->opportunity->contacts->add($this->contact);

        $this->email->parent_id = $this->contact->id;
        $this->email->parent_type = $this->contact->module_name;
        $this->email->save();

        if (isset($GLOBALS['app'])) {
            $this->application = $GLOBALS['app'];
        }
        $GLOBALS['app'] = new SugarApplication();
        $GLOBALS['app']->controller = new SugarController();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        $GLOBALS['app'] = $this->application;
    }

    /**
     * Test asserts that contact's email is present for account and isn't present for opportunity
     *
     * @group 58863
     * @return void
     */
    public function testGetEmailsByAssignOrLink()
    {
        /**
         * @var DBManager $db
         */
        global $db;
        $GLOBALS['app']->controller->bean = $this->account;
        $query = get_emails_by_assign_or_link(array('import_function_file' => 'include/utils.php', 'link' => 'contacts'));
        $email = $db->fetchByAssoc($db->query($query['select'] . $query['from'] . $query['join'] . $query['where']));
        $this->assertEquals($this->email->id, $email['id'], 'Email should be present for Account');

        $GLOBALS['app']->controller->bean = $this->opportunity;
        $query = get_emails_by_assign_or_link(array('import_function_file' => 'include/utils.php', 'link' => 'contacts'));
        $email = $db->fetchByAssoc($db->query($query['select'] . $query['from'] . $query['join'] . $query['where']));
        $this->assertEmpty($email, 'Email should not be present for Opportunity');
    }
}
