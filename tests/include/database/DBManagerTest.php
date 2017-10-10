<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';

class DBManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $_db;
    protected $created = array();

    protected $backupGlobals = FALSE;

    static public function setupBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        if(empty($this->_db)){
            $this->_db = DBManagerFactory::getInstance();
        }
        $this->created = array();
    }

    public function tearDown()
    {
        foreach($this->created as $table => $dummy) {
            $this->_db->dropTableName($table);
        }
    }

    protected function createTableParams($tablename, $fieldDefs, $indices)
    {
        $this->created[$tablename] = true;
        return $this->_db->createTableParams($tablename, $fieldDefs, $indices);
    }

    protected function dropTableName($tablename)
    {
        unset($this->created[$tablename]);
        return $this->_db->dropTableName($tablename);
    }

    private function _createRecords(
        $num
        )
    {
        $beanIds = array();
        for ( $i = 0; $i < $num; $i++ ) {
            $bean = new Contact();
            $bean->id = "$i-test" . mt_rand();
            $bean->last_name = "foobar";
            $this->_db->insert($bean);
            $beanIds[] = $bean->id;
        }

        return $beanIds;
    }

    private function _removeRecords(
        array $ids
        )
    {
        foreach ($ids as $id)
            $this->_db->query("DELETE From contacts where id = '{$id}'");
    }

    public function testGetDatabase()
    {
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli',$this->_db->getDatabase());
        else
            $this->assertTrue(is_resource($this->_db->getDatabase()));
    }

    public function testCheckError()
    {
        $this->assertFalse($this->_db->checkError());
        $this->assertFalse($this->_db->lastError());
    }

    public function testCheckErrorNoConnection()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError());
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testGetQueryTime()
    {
        $this->_db->version();
        $this->assertTrue($this->_db->getQueryTime() > 0);
    }

    public function testCheckConnection()
    {
        $this->_db->checkConnection();
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli',$this->_db->getDatabase());
        else
            $this->assertTrue(is_resource($this->_db->getDatabase()));
    }

    public function testInsert()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id   = 'test' . mt_rand();
        $this->_db->insert($bean);

        $result = $this->_db->query("select id, last_name from contacts where id = '{$bean->id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'],$bean->last_name);
        $this->assertEquals($row['id'],$bean->id);

        $this->_db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testUpdate()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id   = 'test' . mt_rand();
        $this->_db->insert($bean);
        $id = $bean->id;

        $bean = new Contact();
        $bean->last_name = 'newfoobar' . mt_rand();
        $this->_db->update($bean,array('id'=>$id));

        $result = $this->_db->query("select id, last_name from contacts where id = '{$id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'],$bean->last_name);
        $this->assertEquals($row['id'],$id);

        $this->_db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testDelete()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id   = 'test' . mt_rand();
        $this->_db->insert($bean);
        $id = $bean->id;

        $bean = new Contact();
        $this->_db->delete($bean,array('id'=>$id));

        $result = $this->_db->query("select deleted from contacts where id = '{$id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['deleted'],'1');

        $this->_db->query("delete from contacts where id = '{$id}'");
    }

    public function testRetrieve()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id   = 'test' . mt_rand();
        $this->_db->insert($bean);
        $id = $bean->id;

        $bean = new Contact();
        $result = $this->_db->retrieve($bean,array('id'=>$id));
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['id'],$id);

        $this->_db->query("delete from contacts where id = '{$id}'");
    }

    public function testRetrieveView()
    {
        $this->markTestIncomplete('Write this test');
    }

    public function testCreateTable()
    {
        $this->markTestIncomplete('Write this test');
    }

    public function testCreateTableParams()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_'. $tablename,
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);
    }

    public function testRepairTable()
    {
        $this->markTestIncomplete('Write this test');
    }

    public function testRepairTableNoChanges()
    {
        $tableName = 'testRTNC_' . mt_rand();
        $params =  array(
                /* VARDEF - id -  ROW[name] => 'id'  [vname] => 'LBL_ID'  [required] => 'true'  [type] => 'char'  [reportable] => ''  [comment] => 'Unique identifier'  [dbType] => 'id'  [len] => '36'  */
            'id' =>
                array (
                'name' => 'id',
                'vname' => 'LBL_ID',
                'required'=>true,
                'type' => 'id',
                'reportable'=>false,
                'comment' => 'Unique identifier'
                ),
            'date_entered' =>
                array (
                'name' => 'date_entered',
                'vname' => 'LBL_DATE_ENTERED',
                'type' => 'datetime',
                'required'=>true,
                'comment' => 'Date record created'
                ),
            'date_modified' =>
                array (
                  'name' => 'date_modified',
                  'vname' => 'LBL_DATE_MODIFIED',
                  'type' => 'datetime',
                  'required'=>true,
                  'comment' => 'Date record last modified'
                ),
            'modified_user_id' =>
                array (
                  'name' => 'modified_user_id',
                  'rname' => 'user_name',
                  'id_name' => 'modified_user_id',
                  'vname' => 'LBL_MODIFIED',
                  'type' => 'assigned_user_name',
                  'table' => 'modified_user_id_users',
                  'isnull' => 'false',
                  'dbType' => 'id',
                  'required'=> false,
                  'len' => 36,
                  'reportable'=>true,
                  'comment' => 'User who last modified record'
                ),
            'created_by' =>
                array (
                  'name' => 'created_by',
                  'rname' => 'user_name',
                  'id_name' => 'created_by',
                  'vname' => 'LBL_CREATED',
                  'type' => 'assigned_user_name',
                  'table' => 'created_by_users',
                  'isnull' => 'false',
                  'dbType' => 'id',
                  'len' => 36,
                  'comment' => 'User ID who created record'
                ),
            'name' =>
                array (
                  'name' => 'name',
                  'type' => 'varchar',
                  'vname' => 'LBL_NAME',
                  'len' => 150,
                  'comment' => 'Name of the allowable action (view, list, delete, edit)'
                ),
            'category' =>
                array (
                  'name' => 'category',
                  'vname' => 'LBL_CATEGORY',
                  'type' => 'varchar',
                  'len' =>100,
                  'reportable'=>true,
                  'comment' => 'Category of the allowable action (usually the name of a module)'
                ),
            'acltype' =>
                array (
                  'name' => 'acltype',
                  'vname' => 'LBL_TYPE',
                  'type' => 'varchar',
                  'len' =>100,
                  'reportable'=>true,
                  'comment' => 'Specifier for Category, usually "module"'
                ),
            'aclaccess' =>
                array (
                  'name' => 'aclaccess',
                  'vname' => 'LBL_ACCESS',
                  'type' => 'int',
                  'len'=>3,
                  'reportable'=>true,
                  'comment' => 'Number specifying access priority; highest access "wins"'
                ),
            'deleted' =>
                array (
                  'name' => 'deleted',
                  'vname' => 'LBL_DELETED',
                  'type' => 'bool',
                  'reportable'=>false,
                  'comment' => 'Record deletion indicator'
                ),
            'roles' =>
                array (
                    'name' => 'roles',
                    'type' => 'link',
                    'relationship' => 'acl_roles_actions',
                    'source'=>'non-db',
                    'vname'=>'LBL_USERS',
                ),
  			'reverse' =>
                array (
                    'name' => 'reverse',
                    'vname' => 'LBL_REVERSE',
                    'type' => 'bool',
                    'default' => 0
                ),
  		 	'deleted2' =>
                array (
                    'name' => 'deleted2',
                    'vname' => 'LBL_DELETED2',
                    'type' => 'bool',
                    'reportable'=>false,
                    'default' => '0'
                ),
            'primary_address_country' =>
                array (
                   'name' => 'primary_address_country',
                   'vname' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
                   'type' => 'varchar',
                   'group'=>'primary_address',
                   'comment' => 'Country for primary address',
                   'merge_filter' => 'enabled',
                ),
            'refer_url' => array (
                'name' => 'refer_url',
                'vname' => 'LBL_REFER_URL',
                'type' => 'varchar',
                'len' => '255',
                'default' => 'http://',
                'comment' => 'The URL referenced in the tracker URL; no longer used as of 4.2 (see campaign_trkrs)'
                ),
            'budget' => array (
                'name' => 'budget',
                'vname' => 'LBL_CAMPAIGN_BUDGET',
                'type' => 'currency',
                'dbType' => 'double',
                'comment' => 'Budgeted amount for the campaign'
                ),
            'time_from' => array (
                'name' => 'time_from',
                'vname' => 'LBL_TIME_FROM',
                'type' => 'time',
                'required' => false,
                'reportable' => false,
                ),
            'description' =>
                array (
                'name' => 'description',
                'vname' => 'LBL_DESCRIPTION',
                'type' => 'text',
                'comment' => 'Full text of the note',
                'rows' => 6,
                'cols' => 80,
                ),
            'cur_plain' => array (
                'name' => 'cur_plain',
                'vname' => 'LBL_curPlain',
                'type' => 'currency',
            ),
            'cur_len_prec' => array (
                'name' => 'cur_len_prec',
                'vname' => 'LBL_curLenPrec',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26,6',
            ),
            'cur_len' => array (
                'name' => 'cur_len',
                'vname' => 'LBL_curLen',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26',
            ),
            'cur_len_prec2' => array (
                'name' => 'cur_len_prec2',
                'vname' => 'LBL_curLenPrec',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26',
                'precision' => '6',
            ),
            'token_ts' =>
            array (
                'name' => 'token_ts',
                'type' => 'long',
                'required' => true,
                'comment' => 'Token timestamp',
                'function' => array('name' => 'displayDateFromTs', 'returns' => 'html', 'onListView' => true)
            ),
            'conskey' => array(
                'name'		=> 'conskey',
                'type'		=> 'varchar',
                'len'		=> 32,
                'required'	=> true,
                'isnull'	=> false,
            ),
        );

        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());

        $repair = $this->_db->repairTableParams($tableName, $params, array(), false);

        $this->assertEmpty($repair, "Unexpected repairs: " . $repair);

        $this->dropTableName($tableName);
    }

    public function testRepairTableParamsAddData()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
        );

        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());

		$params['bar'] =  array (
                    'name' => 'bar',
                    'type' => 'int',
                    );
        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayNotHasKey('bar', $cols);

        $repair = $this->_db->repairTableParams($tableName, $params, array(), false);
        $this->assertRegExp('#MISSING IN DATABASE.*bar#i', $repair);
        $repair = $this->_db->repairTableParams($tableName, $params, array(), true);
        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayHasKey('bar', $cols);
        $this->assertEquals('bar', $cols['bar']['name']);
        $this->assertEquals($this->_db->getColumnType('int'), $cols['bar']['type']);

        $this->dropTableName($tableName);
    }

    public function testRepairTableParamsAddIndex()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'bar' => array (
                    'name' => 'bar',
                    'type' => 'int',
                    ),
        );
        $index = array(
			'name'			=> 'test_index',
			'type'			=> 'index',
			'fields'		=> array('foo', 'bar', 'bazz'),
		);
        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());
		$params['bazz'] =  array (
                    'name' => 'bazz',
                    'type' => 'int',
        );

        $repair = $this->_db->repairTableParams($tableName, $params, array($index), false);
        $this->assertRegExp('#MISSING IN DATABASE.*bazz#i', $repair);
        $this->assertRegExp('#MISSING INDEX IN DATABASE.*test_index#i', $repair);
        $repair = $this->_db->repairTableParams($tableName, $params, array($index), true);

        $idx = $this->_db->get_indices($tableName);
        $this->assertArrayHasKey('test_index', $idx);
        $this->assertContains('foo', $idx['test_index']['fields']);
        $this->assertContains('bazz', $idx['test_index']['fields']);

        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayHasKey('bazz', $cols);
        $this->assertEquals('bazz', $cols['bazz']['name']);
        $this->assertEquals($this->_db->getColumnType('int'), $cols['bazz']['type']);

        $this->dropTableName($tableName);
    }

    public function testRepairTableParamsAddIndexAndData()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'bar' => array (
                    'name' => 'bar',
                    'type' => 'int',
                    ),
        );
        $index = array(
			'name'			=> 'test_index',
			'type'			=> 'index',
			'fields'		=> array('foo', 'bar'),
		);
        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());

        $repair = $this->_db->repairTableParams($tableName, $params, array($index), false);
        $this->assertRegExp('#MISSING INDEX IN DATABASE.*test_index#i', $repair);
        $repair = $this->_db->repairTableParams($tableName, $params, array($index), true);
        $idx = $this->_db->get_indices($tableName);
        $this->assertArrayHasKey('test_index', $idx);
        $this->assertContains('foo', $idx['test_index']['fields']);
        $this->assertContains('bar', $idx['test_index']['fields']);

        $this->dropTableName($tableName);
    }

    public function testCompareFieldInTables()
    {
        $tablename1 = 'test1_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test2_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesNotInTable1()
    {
        $tablename1 = 'test3_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test4_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);
        $this->assertEquals($res['msg'],'not_exists_table1');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesNotInTable2()
    {
        $tablename1 = 'test5_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test6_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesFieldsDoNotMatch()
    {
        $tablename1 = 'test7_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test8_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'int',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testAddIndexes()
    {
        $tableName = 'test17_' . mt_rand();
        $fields = array(
            'foo' => array (
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            )
        );
        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            )
        );
        $this->createTableParams($tableName, $fields, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->dropTableName($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');

        $tableName = 'test18_' . mt_rand();
        $fields = array(
            'foo' => array (
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            )
        );
        $indexes = array();
        $this->createTableParams($tableName, $fields, $indexes);
        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            )
        );

        // first test not executing the statement
        $this->_db->addIndexes($tableName, $indexes, false);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEmpty($indexesDB, 'Indexes were created');

        // now, execute the statement
        $this->_db->addIndexes($tableName, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');
    }

    public function testDropIndexes()
    {
        $tableName = 'test19_' . mt_rand();
        $fields = array(
            'foo' => array (
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            )
        );
        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            )
        );
        $this->createTableParams($tableName, $fields, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');

        // first test not executing the statement
        $this->_db->dropIndexes($tableName, $indexes, false);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are missed');

        // now, execute the statement
        $this->_db->dropIndexes($tableName, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEmpty($indexesDB, 'Indexes were not dropped');
    }

    public function testModifyIndexes()
    {
        $tableName = 'test21_' . mt_rand();
        $fields = array(
            'foo' => array (
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ),
            'foobar' => array (
                'name' => 'foobar',
                'type' => 'varchar',
                'len' => '255',
            ),
        );
        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            )
        );
        $this->createTableParams($tableName, $fields, $indexes);

        $indexesNew = $indexes;
        $indexesNew['idx_foo']['fields'] = array('foobar');
        $this->_db->modifyIndexes($tableName, $indexesNew, false);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');

        $this->_db->modifyIndexes($tableName, $indexesNew);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexesNew, $indexesDB, 'Indexes are incorrect');
    }

    public function testAddIndexByMultiQuery()
    {
        $tableName = 'test22_' . mt_rand();
        $this->created[$tableName] = true;
        $fields = array(
            'foo' => array (
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            )
        );
        $indexes = array();

        $queries = array();
        $queries[] = $this->_db->createTableSQLParams($tableName, $fields, $indexes);

        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            )
        );
        $tQueries = $this->_db->addIndexes($tableName, $indexes, false);
        $queries = array_merge($queries, explode(";\n", rtrim($tQueries, ";\n")));
        $this->_db->query($queries, true);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');
    }

    public function testDropIndexByMultiQuery()
    {
        $tableName = 'test23_' . mt_rand();
        $this->created[$tableName] = true;
        $fields = array(
            'foo' => array (
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            )
        );
        $indexes = array();

        $queries = array();
        $queries[] = $this->_db->createTableSQLParams($tableName, $fields, $indexes);

        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            )
        );
        $tQueries = $this->_db->addIndexes($tableName, $indexes, false);
        $queries = array_merge($queries, explode(";\n", rtrim($tQueries, ";\n")));
        $tQueries = $this->_db->dropIndexes($tableName, $indexes, false);
        $queries = array_merge($queries, explode(";\n", rtrim($tQueries, ";\n")));
        $this->_db->query($queries, true);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEmpty($indexesDB, 'Indexes were not dropped');
    }

    public function testModifyIndexByMultiQuery()
    {
        $tableName = 'test24_' . mt_rand();
        $this->created[$tableName] = true;
        $fields = array(
            'foo' => array (
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ),
            'foobar' => array (
                'name' => 'foobar',
                'type' => 'varchar',
                'len' => '255',
            ),
        );
        $indexes = array();

        $queries = array();
        $queries[] = $this->_db->createTableSQLParams($tableName, $fields, $indexes);

        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            )
        );
        $tQueries = $this->_db->addIndexes($tableName, $indexes, false);
        $queries = array_merge($queries, explode(";\n", rtrim($tQueries, ";\n")));

        $indexesNew = $indexes;
        $indexesNew['idx_foo']['fields'] = array('foobar');
        $tQueries = $this->_db->modifyIndexes($tableName, $indexesNew, false);
        $queries = array_merge($queries, explode(";\n", rtrim($tQueries, ";\n")));
        $this->_db->query($queries);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexesNew, $indexesDB, 'Indexes are incorrect');
    }

    public function testAddMultiIndexes()
    {

        $tableName = 'test17_' . mt_rand();
        $fields = array(
            'foo' => array(
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ),
            'bar' => array(
                'name' => 'bar',
                'type' => 'varchar',
                'len' => '255',
            ),
        );
        $indexes = array();
        $this->createTableParams($tableName, $fields, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEmpty($indexesDB, 'Indexes are incorrect');

        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            ),
            'idx_bar' => array(
                'name'   => 'idx_bar',
                'type'   => 'index',
                'fields' => array('bar'),
            ),
        );
        $this->_db->addIndexes($tableName, $indexes, false);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEmpty($indexesDB);

        $this->_db->addIndexes($tableName, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');
    }

    public function testDropMultiIndexes()
    {
        $tableName = 'test17_' . mt_rand();
        $fields = array(
            'foo' => array(
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ),
            'bar' => array(
                'name' => 'bar',
                'type' => 'varchar',
                'len' => '255',
            ),
        );
        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            ),
            'idx_bar' => array(
                'name'   => 'idx_bar',
                'type'   => 'index',
                'fields' => array('bar'),
            ),
        );
        $this->createTableParams($tableName, $fields, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');

        $this->_db->dropIndexes($tableName, $indexes, false);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB);

        $this->_db->dropIndexes($tableName, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEmpty($indexesDB, 'Indexes are incorrect');
    }

    public function testModifyMultiIndexes()
    {
        $tableName = 'test17_' . mt_rand();
        $fields = array(
            'foo' => array(
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ),
            'bar' => array(
                'name' => 'bar',
                'type' => 'varchar',
                'len' => '255',
            ),
        );
        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            ),
            'idx_bar' => array(
                'name'   => 'idx_bar',
                'type'   => 'index',
                'fields' => array('bar'),
            ),
        );
        $this->createTableParams($tableName, $fields, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');

        $indexesNew = $indexes;
        $indexesNew['idx_foo']['fields'] = array('bar');
        $indexesNew['idx_bar']['fields'] = array('foo');
        $this->_db->modifyIndexes($tableName, $indexesNew, false);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');

        $this->_db->modifyIndexes($tableName, $indexesNew);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexesNew, $indexesDB, 'Indexes are incorrect');
    }

    public function testAddMultiIndexesByMultiQuery()
    {
        $tableName = 'test17_' . mt_rand();
        $fields = array(
            'foo' => array(
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ),
            'bar' => array(
                'name' => 'bar',
                'type' => 'varchar',
                'len' => '255',
            ),
        );
        $indexes = array();
        $this->createTableParams($tableName, $fields, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEmpty($indexesDB, 'Indexes are incorrect');

        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            ),
            'idx_bar' => array(
                'name'   => 'idx_bar',
                'type'   => 'index',
                'fields' => array('bar'),
            ),
        );
        $queries = $this->_db->addIndexes($tableName, $indexes, false);
        $queries = explode(";\n", rtrim(trim($queries), ';'));
        $this->_db->query($queries);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');
    }

    public function testDropMultiIndexesByMultiQuery()
    {
        $tableName = 'test17_' . mt_rand();
        $fields = array(
            'foo' => array(
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ),
            'bar' => array(
                'name' => 'bar',
                'type' => 'varchar',
                'len' => '255',
            ),
        );
        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            ),
            'idx_bar' => array(
                'name'   => 'idx_bar',
                'type'   => 'index',
                'fields' => array('bar'),
            ),
        );
        $this->createTableParams($tableName, $fields, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');

        $queries = $this->_db->dropIndexes($tableName, $indexes, false);
        $queries = explode(";\n", rtrim(trim($queries), ';'));
        $this->_db->query($queries);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEmpty($indexesDB, 'Indexes are incorrect');
    }

    public function testModifyMultiIndexesByMultiQuery()
    {
        $tableName = 'test17_' . mt_rand();
        $fields = array(
            'foo' => array(
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ),
            'bar' => array(
                'name' => 'bar',
                'type' => 'varchar',
                'len' => '255',
            ),
        );
        $indexes = array(
            'idx_foo' => array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
            ),
            'idx_bar' => array(
                'name'   => 'idx_bar',
                'type'   => 'index',
                'fields' => array('bar'),
            ),
        );
        $this->createTableParams($tableName, $fields, $indexes);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexes, $indexesDB, 'Indexes are incorrect');

        $indexesNew = $indexes;
        $indexesNew['idx_foo']['fields'] = array('bar');
        $indexesNew['idx_bar']['fields'] = array('foo');
        $queries = $this->_db->modifyIndexes($tableName, $indexesNew, false);
        $queries = explode(";\n", rtrim(trim($queries), ';'));
        $this->_db->query($queries);
        $indexesDB = $this->_db->get_indices($tableName);
        $this->assertEquals($indexesNew, $indexesDB, 'Indexes are incorrect');
    }

    public function testAddColumn()
    {
        $tablename1 = 'test23_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test24_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        $this->_db->addColumn(
            $tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    )
                )
            );

        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function alterColumnDataProvider()
    {
        return array(
            array(
                 1,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'required' => true, 'default' => 'sugar'),
                'temp' => array ('name' => 'foobar', 'type' => 'int')                           // Check if type conversion works
            ),
            array(
                2,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'default' => 'kilroy'),
                'temp' => array ('name' => 'foobar', 'type' => 'double', 'default' => '99999')  // Check if default gets replaced
            ),
            array(
                3,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255'),
                'temp' => array ('name' => 'foobar', 'type' => 'double', 'default' => '99999')  // Check if default gets dropped
            ),
            array(
                4,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'required' => true, 'default' => 'sweet'),
                'temp' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '1500',)      // Check varchar shortening
            ),
            array(
                5,
                'target' => array ('name' => 'foobar', 'type' => 'longtext', 'required' => true),
                'temp' => array ('name' => 'foobar', 'type' => 'text', 'default' => 'dextrose') // Check clob(65k) to clob(2M or so) conversion
            ),
            array(
                6,
                'target' => array ('name' => 'foobar', 'type' => 'double', 'required' => true),
                'temp' => array ('name' => 'foobar', 'type' => 'int', 'default' => 0)           // Check int to double change
            ),
        );
    }



    /**
     * @dataProvider alterColumnDataProvider
     * @param  $i
     * @param  $target
     * @param  $temp
     * @return void
     */
    public function testAlterColumn($i, $target, $temp)
    {
        if($this->_db->dbType == "oci8" && ($i == 4 || $i == 6)) {
            $this->markTestSkipped("Cannot reliably shrink columns in Oracle");
        }

        $foo_col = array ('name' => 'foo', 'type' => 'varchar', 'len' => '255'); // Common column between tables

        $tablebase = 'testac_'. mt_rand() . '_';

        $t1 = $tablebase . $i .'A';
        $t2 = $tablebase . $i .'B';
        $this->createTableParams(  $t1,
                                        array('foo' => $foo_col, 'foobar' => $target),
                                        array());
        $this->createTableParams(  $t2,
                                        array('foo' => $foo_col, 'foobar' => $temp),
                                        array());

        $res = $this->_db->compareFieldInTables('foobar', $t1, $t2);

        $this->assertEquals('no_match', $res['msg'],
                            "testAlterColumn table columns match while they shouldn't for table $t1 and $t2: "
                            . print_r($res,true) );

        $this->_db->alterColumn($t2, array('foobar' => $target));

        $res = $this->_db->compareFieldInTables('foobar', $t1, $t2);

        $this->assertEquals('match', $res['msg'],
                            "testAlterColumn table columns don't match while they should for table $t1 and $t2: "
                            . print_r($res,true) );

        $this->dropTableName($t1);
        $this->dropTableName($t2);
    }

    public function testDropTable()
    {
        $this->markTestIncomplete('Write this test');
    }

    public function testDropTableName()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);

        $this->assertFalse(in_array($tablename,$this->_db->getTablesArray()));
    }

    public function testDeleteColumn()
    {
        $this->markTestIncomplete('Write this test');
    }

    public function testDisconnectAll()
    {
        DBManagerFactory::disconnectAll();
        $this->assertTrue($this->_db->checkError());
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testQuery()
    {
        $beanIds = $this->_createRecords(5);

        $result = $this->_db->query("SELECT id From contacts where last_name = 'foobar'");
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli_result',$result);
        else
            $this->assertTrue(is_resource($result));

        while ( $row = $this->_db->fetchByAssoc($result) )
            $this->assertTrue(in_array($row['id'],$beanIds),"Id not found '{$row['id']}'");

        $this->_removeRecords($beanIds);
    }

    public function disabledLimitQuery()
    {
        $beanIds = $this->_createRecords(5);
        $_REQUEST['module'] = 'contacts';
        $result = $this->_db->limitQuery("SELECT id From contacts where last_name = 'foobar'",1,3);
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli_result',$result);
        else
            $this->assertTrue(is_resource($result));

        while ( $row = $this->_db->fetchByAssoc($result) ) {
            if ( $row['id'][0] > 3 || $row['id'][0] < 0 )
                $this->assertFalse(in_array($row['id'],$beanIds),"Found {$row['id']} in error");
            else
                $this->assertTrue(in_array($row['id'],$beanIds),"Didn't find {$row['id']}");
        }
        unset($_REQUEST['module']);
        $this->_removeRecords($beanIds);
    }

    public function testGetOne()
    {
        $beanIds = $this->_createRecords(1);

        $id = $this->_db->getOne("SELECT id From contacts where last_name = 'foobar'");
        $this->assertEquals($id,$beanIds[0]);

        // bug 38994
        if ( $this->_db instanceOf MysqlManager ) {
            $id = $this->_db->getOne($this->_db->limitQuerySql("SELECT id From contacts where last_name = 'foobar'", 0, 1));
            $this->assertEquals($id,$beanIds[0]);
        }

        $this->_removeRecords($beanIds);
    }

    public function testGetFieldsArray()
    {
        $beanIds = $this->_createRecords(1);

        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");
        $fields = $this->_db->getFieldsArray($result,true);

        $this->assertEquals(array("id"),$fields);

        $this->_removeRecords($beanIds);
    }

    public function testGetAffectedRowCount()
    {
        if(!$this->_db->supports("affected_rows")) {
            $this->markTestSkipped('Skipping, backend doesn\'t support affected rows');
        }

        $beanIds = $this->_createRecords(1);
        $result = $this->_db->query("DELETE From contacts where id = '{$beanIds[0]}'");
        $this->assertEquals(1, $this->_db->getAffectedRowCount($result));
    }

    public function testFetchByAssoc()
    {
        $beanIds = $this->_createRecords(1);

        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");

        $row = $this->_db->fetchByAssoc($result);

        $this->assertTrue(is_array($row));
        $this->assertEquals($row['id'],$beanIds[0]);

        $this->_removeRecords($beanIds);
    }

    public function testConnect()
    {
        $this->markTestIncomplete('Write this test');
    }

    public function testDisconnect()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError());
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testGetTablesArray()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $this->assertTrue($this->_db->tableExists($tablename));

        $this->dropTableName($tablename);
    }

    public function testVersion()
    {
        $ver = $this->_db->version();

        $this->assertTrue(is_string($ver));
    }

    public function testTableExists()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);
    }

    public function providerCompareVardefs()
    {
        $returnArray = array(
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                false),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'len' => '255',
                ),
                false),
            array(
                array(
                    'name' => 'foo',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'FOO',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            );

        return $returnArray;
    }

    /**
     * @dataProvider providerCompareVarDefs
     */
    public function testCompareVarDefs($fieldDef1,$fieldDef2,$expectedResult)
    {
        if ( $expectedResult ) {
            $this->assertTrue($this->_db->compareVarDefs($fieldDef1,$fieldDef2));
        }
        else {
            $this->assertFalse($this->_db->compareVarDefs($fieldDef1,$fieldDef2));
        }
    }

    /**
     * @ticket 34892
     */
    public function test_Bug34892_MssqlNotClearingErrorResults()
    {
            // execute a bad query
            $this->_db->query("select dsdsdsdsdsdsdsdsdsd", false, "test_Bug34892_MssqlNotClearingErrorResults", true);
            // assert it found an error
            $this->assertNotEmpty($this->_db->lastError(), "lastError should return true as a result of the previous illegal query");
            // now, execute a good query
            $this->_db->query("select * from config");
            // and make no error messages are asserted
            $this->assertEmpty($this->_db->lastError(), "lastError should have cleared the previous error and return false of the last legal query");
    }

    public function vardefProvider()
    {
        $GLOBALS['log']->info('DBManagerTest.vardefProvider: _db = ' . print_r($this->_db));
        $this->setUp(); // Just in case the DB driver is not created yet.
        $emptydate = $this->_db->emptyValue("date");
        $emptytime = $this->_db->emptyValue("time");
        $emptydatetime = $this->_db->emptyValue("datetime");

        return array(
            array("testid", array (
                  'id' =>
                  array (
                    'name' => 'id',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  ),
                  array("id" => "test123"),
                  array("id" => "'test123'")
            ),
            array("testtext", array (
                  'text1' =>
                  array (
                    'name' => 'text1',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  'text2' =>
                  array (
                    'name' => 'text2',
                    'type' => 'varchar',
                  ),
                  ),
                  array(),
                  array("text1" => "''"),
                  array()
            ),
            array("testtext2", array (
                  'text1' =>
                  array (
                    'name' => 'text1',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  'text2' =>
                  array (
                    'name' => 'text2',
                    'type' => 'varchar',
                  ),
                  ),
                  array('text1' => 'foo', 'text2' => 'bar'),
                  array("text1" => "'foo'", 'text2' => "'bar'"),
            ),
            array("testreq", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                        'required'=>true,
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                        'required'=>true,
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                        'required'=>true,
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                        'required'=>true,
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                        'required'=>true,
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                        'required'=>true,
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                        'required'=>true,
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                        'required'=>true,
                      ),
                  ),
                  array("id" => "test123", 'intval' => 42, 'floatval' => 42.24,
                  		'money' => 56.78, 'test_dtm' => '2002-01-02 12:34:56', 'test_dtm2' => '2011-10-08 01:02:03',
                        'test_dt' => '1998-10-04', 'test_tm' => '03:04:05'
                  ),
                  array("id" => "'test123'", 'intval' => 42, 'floatval' => 42.24,
                  		'money' => 56.78, 'test_dtm' => $this->_db->convert('\'2002-01-02 12:34:56\'', "datetime"), 'test_dtm2' => $this->_db->convert('\'2011-10-08 01:02:03\'', 'datetime'),
                        'test_dt' => $this->_db->convert('\'1998-10-04\'', 'date'), 'test_tm' => $this->_db->convert('\'03:04:05\'', 'time')
                  ),
            ),
            array("testreqnull", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                        'required'=>true,
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                        'required'=>true,
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                        'required'=>true,
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                        'required'=>true,
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                        'required'=>true,
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                        'required'=>true,
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                        'required'=>true,
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                        'required'=>true,
                      ),
                  ),
                  array(),
                  array("id" => "''", 'intval' => 0, 'floatval' => 0,
                  		'money' => 0, 'test_dtm' => "$emptydatetime", 'test_dtm2' => "$emptydatetime",
                        'test_dt' => "$emptydate", 'test_tm' => "$emptytime"
                  ),
                  array(),
            ),
            array("testnull", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                      ),
                  ),
                  array("id" => 123),
                  array("id" => "'123'"),
                  array(),
            ),
            array("testempty", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                      ),
                   'text_txt' =>
                      array (
                        'name' => 'test_txt',
                        'type' => 'varchar',
                      ),
                  ),
                  array("id" => "", 'intval' => '', 'floatval' => '',
                  		'money' => '', 'test_dtm' => '', 'test_dtm2' => '',
                        'test_dt' => '', 'test_tm' => '', 'text_txt' => null
                  ),
                  array("id" => "''", 'intval' => 0, 'floatval' => 0,
                  		'money' => 0, 'test_dtm' => "NULL", 'test_dtm2' => "NULL",
                        'test_dt' => "NULL", 'test_tm' => 'NULL'
                  ),
                  array('intval' => 'NULL', 'floatval' => 'NULL',
                  		'money' => 'NULL', 'test_dtm' => 'NULL', 'test_dtm2' => 'NULL',
                        'test_dt' => 'NULL', 'test_tm' => 'NULL'
                  ),
            ),
        );
    }

   /**
    * Test InserSQL functions
    * @dataProvider vardefProvider
    * @param string $name
    * @param array $defs
    * @param array $data
    * @param array $result
    */
    public function testInsertSQL($name, $defs, $data, $result)
    {
        $vardefs = array(
			'table' => $name,
            'fields' => $defs,
        );
        $obj = new TestSugarBean($name, $vardefs);
        // regular fields
        foreach($data as $k => $v) {
            $obj->$k = $v;
        }
        $sql = $this->_db->insertSQL($obj);
        $names = join('\s*,\s*',array_map('preg_quote', array_keys($result)));
        $values = join('\s*,\s*',array_map('preg_quote', array_values($result)));
        $this->assertRegExp("/INSERT INTO $name\s+\(\s*$names\s*\)\s+VALUES\s+\(\s*$values\s*\)/is", $sql, "Bad sql: $sql");
    }

   /**
    * Test UpdateSQL functions
    * @dataProvider vardefProvider
    * @param string $name
    * @param array $defs
    * @param array $data
    * @param array $_
    * @param array $result
    */
    public function testUpdateSQL($name, $defs, $data, $_, $result = null)
    {
        $name = "update$name";
        $vardefs = array(
			'table' => $name,
            'fields' => $defs,
        );
        // ensure it has an ID
        $vardefs['fields']['id'] = array (
                    'name' => 'id',
                    'type' => 'id',
                    'required'=>true,
                  );
        $vardefs['fields']['deleted'] = array (
                    'name' => 'deleted',
                    'type' => 'bool',
                  );

        $obj = new TestSugarBean($name, $vardefs);
        // regular fields
        foreach($defs as $k => $v) {
            if(isset($data[$k])) {
                $obj->$k = $data[$k];
            } else {
                $obj->$k = null;
            }
        }
        // set fixed ID
        $obj->id = 'test_ID';
        $sql = $this->_db->updateSQL($obj);
        if(is_null($result)) {
            $result = $_;
        }
        $names_i = array();
        foreach($result as $k => $v) {
            if($k == "id" || $k == 'deleted') continue;
            $names_i[] = preg_quote("$k=$v");
        }
        if(empty($names_i)) {
            $this->assertEquals("", $sql, "Bad sql: $sql");
            return;
        }
        $names = join('\s*,\s*',$names_i);
        $this->assertRegExp("/UPDATE $name\s+SET\s+$names\s+WHERE\s+$name.id\s*=\s*'test_ID' AND deleted=0/is", $sql, "Bad sql: $sql");
    }

     /**
    * Test UpdateSQL functions
    * @dataProvider vardefProvider
    * @param string $name
    * @param array $defs
    * @param array $data
    * @param array $_
    * @param array $result
    */
    public function testUpdateSQLNoDeleted($name, $defs, $data, $_, $result = null)
    {
        $name = "updatenodel$name";
        $vardefs = array(
			'table' => $name,
            'fields' => $defs,
        );
        // ensure it has an ID
        $vardefs['fields']['id'] = array (
                    'name' => 'id',
                    'type' => 'id',
                    'required'=>true,
                  );
        unset($vardefs['fields']['deleted']);

        $obj = new TestSugarBean($name, $vardefs);
        // regular fields
        foreach($defs as $k => $v) {
            if(isset($data[$k])) {
                $obj->$k = $data[$k];
            } else {
                $obj->$k = null;
            }
        }
        // set fixed ID
        $obj->id = 'test_ID';
        $sql = $this->_db->updateSQL($obj);
        if(is_null($result)) {
            $result = $_;
        }
        $names_i = array();
        foreach($result as $k => $v) {
            if($k == "id" || $k == 'deleted') continue;
            $names_i[] = preg_quote("$k=$v");
        }
        if(empty($names_i)) {
            $this->assertEquals("", $sql, "Bad sql: $sql");
            return;
        }
        $names = join('\s*,\s*',$names_i);
        $this->assertRegExp("/UPDATE $name\s+SET\s+$names\s+WHERE\s+$name.id\s*=\s*'test_ID'/is", $sql, "Bad sql: $sql");
        $this->assertNotContains(" AND deleted=0", $sql, "Bad sql: $sql");
    }

    /**
     * Test the canInstall
     * @return void
     */
    public function testCanInstall() {
        $DBManagerClass = get_class($this->_db);
        if(!method_exists($this->_db, 'version') || !method_exists($this->_db, 'canInstall'))
            $this->markTestSkipped(
              "Class {$DBManagerClass} doesn't implement canInstall or version methods");

        $method = new ReflectionMethod($DBManagerClass, 'canInstall');
        if($method->class == 'DBManager')
            $this->markTestSkipped(
              "Class {$DBManagerClass} or one of it's ancestors doesn't override DBManager's canInstall");

        // First assuming that we are only running unit tests against a supported database :)
        $this->assertTrue($this->_db->canInstall(), "Apparently we are not running this unit test against a supported database!!!");

        $DBstub = $this->getMock($DBManagerClass, array('version'));
        $DBstub->expects($this->any())
               ->method('version')
               ->will($this->returnValue('0.0.0')); // Expect that any supported version is higher than 0.0.0

        $this->assertTrue(is_array($DBstub->canInstall()), "Apparently we do support version 0.0.0 in " . $DBManagerClass);
    }

    public function providerValidateQuery()
    {
        return array(
            array(true, 'SELECT * FROM accounts'),
            array(false, 'SELECT * FROM blablabla123'),
        );
    }

    /**
     * Test query validation
     * @dataProvider providerValidateQuery
     * @param $good
     * @param $sql
     * @return void
     */
    public function testValidateQuery($good, $sql)
    {
        $check = $this->_db->validateQuery($sql);
        $this->assertEquals($good, $check);
    }

    public function testTextSizeHandling()
    {
        $tablename = 'testTextSize';// . mt_rand();
        $fielddefs = array(
                        'id' =>
                            array (
                            'name' => 'id',
                            'required'=>true,
                            'type' => 'id',
                            ),
                        'test' => array (
                            'name' => 'test',
                            'type' => 'longtext',
                            //'len' => '255',
                            ),
                        'dummy' => array (
                            'name' => 'dummy',
                            'type' => 'longtext',
                            //'len' => '255',
                            ),
                        );

        $this->createTableParams($tablename, $fielddefs, array());
        $basestr = '0123456789abcdefghijklmnopqrstuvwxyz';
        $str = $basestr;
        while(strlen($str) < 159900)
        {
            $str .= $basestr;
        }

        for($i = 0; $i < 50; $i++)
        {
            $str .= $basestr;
            $size = strlen($str);
            //echo "$size\n";
            $this->_db->insertParams($tablename, $fielddefs, array('id' => $size, 'test' => $str, 'dummy' => $str));

            $select = "SELECT test FROM $tablename WHERE id = '{$size}'";
            $strresult = $this->_db->getOne($select);

            $this->assertEquals(0, mb_strpos($str, $strresult));
        }
    }

    public function testGetIndicesContainsPrimary()
    {
        $indices = $this->_db->get_indices('accounts');

        // find if any are primary
        $found = false;

        foreach($indices as $index)
        {
            if($index['type'] == "primary") {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Primary Key Not Found On Module');
    }

    /*
     * testDBGuidGeneration
     * Tests that the first 1000 DB generated GUIDs are unique
     */
    public function testDBGuidGeneration()
    {

		$guids = array();
		$sql = "SELECT {$this->_db->getGuidSQL()} {$this->_db->getFromDummyTable()}";
		for($i = 0; $i < 1000; $i++)
		{
			$newguid = $this->_db->getOne($sql);
			$this->assertFalse(in_array($newguid, $guids), "'$newguid' already existed in the array of GUIDs!");
			$guids []= $newguid;
		}
	}

    public function testAddPrimaryKey()
    {
        $tablename = 'testConstraints';
        $fielddefs = array(
                        'id' =>
                            array (
                            'name' => 'id',
                            'required'=>true,
                            'type' => 'id',
                            ),
                        'test' => array (
                            'name' => 'test',
                            'type' => 'longtext',
                            ),
                        );

        $this->createTableParams($tablename, $fielddefs, array());
        unset($this->created[$tablename]); // that table is required by testRemovePrimaryKey test

        $sql = $this->_db->add_drop_constraint(
            $tablename,
            array(
                'name'   => 'testConstraints_pk',
                'type'   => 'primary',
                'fields' => array('id'),
                ),
            false
            );

        $result = $this->_db->query($sql);

        $indices = $this->_db->get_indices($tablename);

        // find if any are primary
        $found = false;

        foreach($indices as $index)
        {
            if($index['type'] == "primary") {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Primary Key Not Found On Table');
    }

    /**
     * @depends testAddPrimaryKey
     */
    public function testRemovePrimaryKey()
    {
        $tablename = 'testConstraints';
        $this->created[$tablename] = true;

         $sql = $this->_db->add_drop_constraint(
            $tablename,
            array(
                'name'   => 'testConstraints_pk',
                'type'   => 'primary',
                'fields' => array('id'),
                ),
            true
            );

        $result = $this->_db->query($sql);

        $indices = $this->_db->get_indices($tablename);

        // find if any are primary
        $found = false;

        foreach($indices as $index)
        {
            if($index['type'] == "primary") {
                $found = true;
                break;
            }
        }

        $this->assertFalse($found, 'Primary Key Found On Table');
    }

}
