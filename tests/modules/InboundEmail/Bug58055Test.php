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


require_once("modules/InboundEmail/InboundEmail.php");

/**
 * Bug #58055
 * Inbound Email to Case creation does not automatically link up to the Account
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 58055
 */
class Bug58055Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $ie;
    private $account;
    private $contact;

    public function setUp()
    {
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("app_strings");

        SugarTestHelper::setUp("current_user");

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->name = "Boro SugarTest 58055";
        $this->account->save();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->first_name = "Boro";
        $this->contact->last_name = "SugarTest 58055";
        $this->contact->email1 = "bsitnikovskiBug58055Test@sugarcrm.com";
        $this->contact->save();

        $this->ie = new InboundEmail();
        $this->ie->name = $this->ie->casename = "[CASE:58055] Bug58055 Test";
        $this->ie->description = "This is a test for Bug58055";
        $this->ie->mailbox_type = "createcase";
        $this->ie->groupfolder_id = "non-empty";
        $this->ie->from_addr = $this->contact->email1;


        $this->ie->save();
    }

    public function tearDown()
    {
        $GLOBALS["db"]->query("DELETE FROM inbound_email WHERE id = '{$this->ie->id}'");
        $GLOBALS["db"]->query("DELETE FROM cases WHERE name = '{$this->ie->casename}'");
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeCreatedContactsEmailAddresses();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    private function getCase()
    {
        // Cache intentionally bypassed
        $case = BeanFactory::newBean("Cases");
        $case->retrieve($this->ie->parent_id);

        $this->assertTrue($case->load_relationship("accounts"));

        $this->assertTrue($case->load_relationship("contacts"));

        return $case;
    }

    public function testContactWithAccountLink()
    {
        // link contact to accounts
        $this->assertTrue($this->contact->load_relationship("accounts"));
        $this->contact->accounts->add($this->account->id);

        $this->ie->handleCreateCase($this->ie, $GLOBALS["current_user"]->id);

        $case = $this->getCase();

        $this->assertContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }

    public function testContactWithoutAccountLink()
    {
        $this->ie->handleCreateCase($this->ie, $GLOBALS["current_user"]->id);
        $case = $this->getCase();

        $this->assertNotContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }

    public function testContactAccountEmail()
    {
        // set same e-mail address
        $this->account->email1 = $this->contact->email1;
        $this->account->save();

        $this->ie->handleCreateCase($this->ie, $GLOBALS["current_user"]->id);

        $case = $this->getCase();

        $this->assertContains($this->account->id, $case->accounts->get());
        $this->assertContains($this->contact->id, $case->contacts->get());
    }
}
