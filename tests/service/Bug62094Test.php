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


require_once 'tests/service/SOAPTestCase.php';
require_once 'modules/ModuleBuilder/parsers/relationships/AbstractRelationship.php';
require_once 'modules/ModuleBuilder/parsers/relationships/ActivitiesRelationship.php';

class Bug62094Test extends SOAPTestCase
{
    protected $definition;

    public function setUp()
    {
        $this->definition = Array(
            'id' => 'a3468352-8fd0-ec13-708a-517087f79ada',
            'relationship_name' => 'accounts_meetings_1',
            'lhs_module' => 'Accounts',
            'lhs_table' => 'accounts',
            'lhs_key' => 'id',
            'rhs_module' => 'Meetings',
            'rhs_table' => 'meetings',
            'rhs_key' => 'id',
            'join_table' => 'accounts_meetings_1_c',
            'join_key_lhs' => 'accounts_meetings_1accounts_ida',
            'join_key_rhs' => 'accounts_meetings_1meetings_idb',
            'relationship_type' => 'one-to-many',
            'relationship_role_column' => '',
            'relationship_role_column_value' =>'',
            'reverse' => 0,
            'deleted' => 0,
            'readonly' => 1,
            'rhs_subpanel' => '',
            'lhs_subpanel' => '',
            'from_studio' => 1,
            'is_custom' => 1
        );

        parent::setUp();

    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetModuleFields()
    {
        $relationship = new AbstractRelationship62094($this->definition);
        $vardef = $relationship->getLinkFieldDefinition('Meetings', 'accounts_meetings_1');

        $this->assertNotEmpty($vardef['module'], 'get_module_fields failed: empty module returned');
        $this->assertNotEmpty($vardef['bean_name'], 'get_module_fields failed: empty bean_name returned');

        $relationship = new ActivitiesRelationship62094($this->definition);
        $vardef = $relationship->getLinkFieldDefinition('Meetings', 'accounts_meetings_1');

        $this->assertNotEmpty($vardef['module'], 'get_module_fields failed: empty module returned');
        $this->assertNotEmpty($vardef['bean_name'], 'get_module_fields failed: empty bean_name returned');
    }
}

class AbstractRelationship62094 extends AbstractRelationship {

    public function getLinkFieldDefinition ($sourceModule , $relationshipName)
    {
        return parent::getLinkFieldDefinition($sourceModule , $relationshipName);
    }

}

class ActivitiesRelationship62094 extends ActivitiesRelationship {

    public function getLinkFieldDefinition ($sourceModule , $relationshipName)
    {
        return parent::getLinkFieldDefinition($sourceModule , $relationshipName);
    }

}
