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


require_once('data/SugarBean.php');
require_once("modules/Administration/QuickRepairAndRebuild.php");
require_once('modules/DynamicFields/FieldCases.php');

//This Unit test makes sure that no php fatal errors are output when merging two records that have calculated fields
//pointing to a related value
class Bug61734Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public $acc;
    public $acc2;
    public $custFileDirPath = 'Extension/modules/Accounts/Ext/Vardefs/';
    public $custFieldName = 'cf_61734_c';
    public $custField;
    public $df;


    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Administration'));
        SugarTestHelper::setUp('dictionary');
        parent::setUp();
        $this->createCustom();

        $this->acc = SugarTestAccountUtilities::createAccount();
        $this->acc2 = SugarTestAccountUtilities::createAccount();


    }

	public function tearDown()
	{
        $this->custField->delete($this->df);
        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("clearAll", "rebuildExtensions"), array("Accounts"),  false, false);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        $GLOBALS['reload_vardefs'] = true;
        $o = new Account();
        $GLOBALS['reload_vardefs'] = false;
	}

    public function createCustom(){
        //create new varchar widget and associate with Accounts
        $this->custField = get_widget('varchar');
        $this->custField->id = 'Accounts'.$this->custFieldName;
        $this->custField->name = $this->custFieldName;
        $this->custField->type = 'varchar';
        $this->custField->label = 'LBL_' . strtoupper($this->custFieldName);
        $this->custField->vname = 'LBL_' . strtoupper($this->custFieldName);
        $this->custField->len = 255;
        $this->custField->custom_module = 'Accounts';
        $this->custField->required = 0;
        $this->custField->default = 'goofy';

        $this->acc = new Account();
        $this->df = new DynamicField('Accounts');
        $this->df->setup($this->acc);

        $this->df->addFieldObject($this->custField);
        $this->df->buildCache('Accounts');
        $this->custField->save($this->df);

        VardefManager::clearVardef();
        VardefManager::refreshVardefs('Accounts', 'Account');

        //Now create the meta files to make this a Calculated Field.
        $fn = $this->custFieldName;
        $extensionContent = <<<EOQ
<?php
\$dictionary['Account']['fields']['$fn']['duplicate_merge_dom_value']=0;
\$dictionary['Account']['fields']['$fn']['calculated']='true';
\$dictionary['Account']['fields']['$fn']['formula']='related(\$assigned_user_link,"name")';
\$dictionary['Account']['fields']['$fn']['enforced']='true';
\$dictionary['Account']['fields']['$fn']['dependency']='';
\$dictionary['Account']['fields']['$fn']['type']='varchar';
\$dictionary['Account']['fields']['$fn']['name']='$fn';


EOQ;
        //create custom field file
        $this->custFileDirPath = create_custom_directory($this->custFileDirPath);
        $fileLoc = $this->custFileDirPath.'sugarfield_'.$this->custFieldName.'.php';
        file_put_contents($fileLoc, $extensionContent);

        //run repair and clear to make sure the meta gets picked up
        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("clearAll", "rebuildExtensions"), array("Accounts"),  false, false);
        $fn = $this->custFieldName;
    }

    public function testMergeWithCalculatedField()
    {
        //recreate expected request and post superglobals for savemerge.php
        $_REQUEST = $_POST = array(
            'module' => 'MergeRecords',
            'record' => $this->acc->id,
            'merge_module' => 'Accounts',
            'action' => 'SaveMerge',
            'return_module' => 'Accounts',
            'return_action' => 'index',
            'change_parent' => 0,
            'remove' => 0,
            'merged_links' => 'assigned_user_link,member_of',
            'merged_ids' => array
                ($this->acc2->id),

            'button' =>   'Save Merge',
            'name' => 'SugarAccount1091847479',
            'date_entered' => '04/08/2013 03:18pm',
            'date_modified' => '04/08/2013 03:18pm',
            'team_name' => 'team_name',
            'team_name_field' => 'team_name_table',
            'team_name_collection_0' => 'Global',
            'id_team_name_collection_0' => 1,
            'primary_team_name_collection' => 0,
            'noRedirect' => 1,
        );
        //call SaveMerge so the account beans can be merged
        require_once('modules/MergeRecords/SaveMerge.php');

        //make sure fatal error is not being output
        $this->expectOutputNotRegex('/fatal/i', 'Failure message is appearing during merge');


    }
}
