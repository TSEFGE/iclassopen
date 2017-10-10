<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once('include/generic/LayoutManager.php');
require_once('include/generic/SugarWidgets/SugarWidgetFieldrelate.php');

/**
 * Bug #52173
 *
 * Dashlets | Adding relationships (Accounts, Contacts, custom modules) to dashlet filters do not work
 * @ticket 52173
 */

class Bug52173Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Account */
    protected $account = null;

    /** @var Contact */
    protected $contact1 = null;

    /** @var Contact */
    protected $contact2 = null;

    /** @var SugarWidgetFieldrelate */
    protected $sugarWidget = null;

    /** @var DynamicField */
    protected $df = null;

    /** @var RepairAndClear */
    protected $rc = null;

    /** @var TemplateRelatedTextField */
    protected $relateField = null;

    /** @var string name */
    protected $field_name_c = 'Bug58931_relateField';

    /** @var LayoutManager */
    protected $layoutManager = null;

    public function setup()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
        parent::setUp();

        $this->createCustomField();
        $this->getSugarWidgetFieldRelate();

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact2 = SugarTestContactUtilities::createContact();
    }

    public function tearDown()
    {
        $this->relateField->delete($this->df);
        $this->rc->repairAndClearAll(array("rebuildExtensions", "clearVardefs"), array("Contact"),  false, false);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /**
     * Create the custom field with type 'relate'
     */
    protected function createCustomField()
    {
        $field = get_widget('relate');
        $field->id = 'Contacts'. $this->field_name_c;
        $field->name = $this->field_name_c;
        $field->type = 'relate';
        $field->label = 'LBL_' . strtoupper($this->field_name_c);
        $field->ext2 = 'Accounts';
        $field->view_module = 'Contacts';
        $this->relateField = $field;

        $this->bean =BeanFactory::getBean('Contacts');
        $this->df = new DynamicField($this->bean->module_name);
        $this->df->setup($this->bean);
        $field->save($this->df);

        $this->rc = new RepairAndClear();
        $this->rc->repairAndClearAll(array("rebuildExtensions", "clearVardefs"), array('Contact'),  false, false);
    }

    /**
     * Create SugarWidget for relate field
     */
    public function getSugarWidgetFieldRelate()
    {
        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('context', 'Report');
        $db = new stdClass();
        $db->db = $GLOBALS['db'];
        $db->report_def_str = '';
        $layoutManager->setAttributePtr('reporter', $db);
        $this->sugarWidget = new SugarWidgetFieldrelate($layoutManager);
    }

    /*
    * Check correct execution of the query for Dashlets if filter contains default bean's relate field
    * @return void
    */
    public function testDefaultRelateField()
    {
        $this->contact2->account_id = $this->account->id;
        $this->contact2->save();
        $layoutDef = array( 'name'        => 'account_name',
                            'id_name'     => 'account_id',
                            'type'        => 'relate',
                            'link'        => 'accounts_contacts',
                            'table'       => 'contacts',
                            'table_alias' => 'contacts',
                            'module'      => 'Contacts',
                            'input_name0' => array( 0 => $this->account->id ),
        );
        $out = $this->sugarWidget->queryFilterone_of($layoutDef);
        $this->assertContains($this->contact2->id, $out, 'The request for existing relate field was made incorrectly');
    }

    /*
    * Check correct execution of the query for Dashlets
    * if filter contains default bean's relate field with same LHS and RHS modules
    * @return void
    */
    public function testDefaultRelateFieldForSameLHSAndRHSModules()
    {
        $this->contact2->reports_to_id = $this->contact1->id;
        $this->contact2->save();
        $layoutDef = array( 'name'        => 'report_to_name',
                            'id_name'     => 'reports_to_id',
                            'type'        => 'relate',
                            'link'        => 'contact_direct_reports',
                            'table'       => 'contacts',
                            'table_alias' => 'contacts',
                            'module'      => 'Contacts',
                            'input_name0' => array( 0 => $this->contact2->reports_to_id ),
        );
        $out = $this->sugarWidget->queryFilterone_of($layoutDef);
        $this->assertContains($this->contact2->id, $out, 'The request for existing relate field which has same LHS and RHS modules was made incorrectly');
    }

    /*
    * Check correct execution of the query for Dashlets if filter contains custom relate field
    * @return void
    */
    public function testCustomRelateFieldInDashlet()
    {
        $id = $this->relateField->ext3;
        $this->contact2->$id = $this->account->id;
        $this->contact2->save();
        $layoutDef = array( 'name'          => $this->relateField->name,
                            'id_name'       => $this->relateField->ext3,
                            'type'          => 'relate',
                            'ext2'          => 'Accounts',
                            'custom_module' => 'Contacts',
                            'table'         => 'contacts_cstm',
                            'table_alias'   => 'contacts',
                            'module'        => 'Accounts',
                            'input_name0'   => array( 0 => $this->account->id ),
        );
        $out = $this->sugarWidget->queryFilterone_of($layoutDef);
        $this->assertContains($this->contact2->id, $out, 'The request for custom relate field was made incorrectly');
    }
}
