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

require_once 'include/database/DBManagerFactory.php';

class Bug61885Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * DataProvider function for test
     * @static
     * @return array
     */
    public static function provideVarDefs()
    {
        $returnArray = array(
            array(
                array(
                    'name' => 'FOO',
                    'type' => 'VARCHAR',
                    'len' => '255',
                ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
                true,
            ),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ),
                array(
                    'name' => 'FOO',
                    'type' => 'VARCHAR',
                    'len' => '255',
                ),
                true,
            ),
            array(
                array(
                    'name' => 'idx_ACCNT_id_del',
                    'type' => 'index',
                    'fields' => array('ID', 'deleted'),
                ),
                array(
                    'name' => 'idx_accnt_id_del',
                    'type' => 'index',
                    'fields' => array('id', 'deleted'),
                ),
                true,
            ),
            array(
                array(
                    'name' => 'idx_ACCNT_id_del',
                    'type' => 'index',
                    'fields' => array('ID', 'DELETED'),
                ),
                array(
                    'name' => 'idx_accnt_id_del',
                    'type' => 'index',
                    'fields' => array('id', 'deleted'),
                ),
                true,
            ),
            array(
                array(
                    'name' => 'idx_ACCNT_id_del',
                    'type' => 'index',
                    'fields' => array('IDxxx', 'DELETED'),
                ),
                array(
                    'name' => 'idx_accnt_id_del',
                    'type' => 'index',
                    'fields' => array('id', 'deleted'),
                ),
                false,
            ),
            array(
                array(
                    'name' => 'idx_ACCNT_id_del',
                    'type' => 'index',
                    'fields' => array('IDxxx', 'deletedxxx'),
                ),
                array(
                    'name' => 'idx_accnt_id_del',
                    'type' => 'index',
                    'fields' => array('id', 'deleted'),
                ),
                false,
            ),
            array(
                array(
                    'name' => 'idx_accnt_id_del',
                    'type' => 'index',
                    'fields' => array('id', 'deleted'),
                ),
                array(
                    'name' => 'idx_ACCNT_id_del',
                    'type' => 'index',
                    'fields' => array('ID', 'DELETED'),
                ),
                true,
            ),
        );

        return $returnArray;
    }

    /**
     * @dataProvider provideVarDefs
     * @group 61885
     */
    public function testCompareVarDefsNotCaseSensitive($fieldDef1, $fieldDef2, $expectedResult)
    {
        $DBManager = DBManagerFactory::getInstance();

        if ($expectedResult)
        {
            $this->assertTrue($DBManager->compareVarDefs($fieldDef1, $fieldDef2));
        }
        else
        {
            $this->assertFalse($DBManager->compareVarDefs($fieldDef1, $fieldDef2));
        }
    }
}
