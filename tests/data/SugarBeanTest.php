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


require_once('include/SugarObjects/templates/file/File.php');

class SugarBeanTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}

    public function testGetObjectName(){
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->getObjectName(), 'my_table', "SugarBean->getObjectName() is not returning the table name when object_name is empty.");
    }

    public function testGetAuditTableName(){
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_audit_table_name(), 'my_table_audit', "SugarBean->get_audit_table_name() is not returning the correct audit table name.");
    }
    
    /**
     * @ticket 47261
     */
    public function testGetCustomTableName()
    {
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_custom_table_name(), 'my_table_cstm', "SugarBean->get_custom_table_name() is not returning the correct custom table name.");
    }

    public function testRetrieveQuoting()
    {
        $bean = new BeanMockTestObjectName();
        $bean->db = new MockMysqlDb();
        $bean->retrieve("bad'idstring");
        $this->assertNotContains("bad'id", $bean->db->lastQuery);
        $this->assertContains("bad", $bean->db->lastQuery);
        $this->assertContains("idstring", $bean->db->lastQuery);
    }

    public function testRetrieveStringQuoting()
    {
        $bean = new BeanMockTestObjectName();
        $bean->db = new MockMysqlDb();
        $bean->retrieve_by_string_fields(array("test1" => "bad'string", "evil'key" => "data", 'tricky-(select * from config)' => 'test'));
        $this->assertNotContains("bad'string", $bean->db->lastQuery);
        $this->assertNotContains("evil'key", $bean->db->lastQuery);
        $this->assertNotContains("select * from config", $bean->db->lastQuery);
    }




    /**
     * Test to make sure that when a bean is cloned it removes all loaded relationships so they can be recreated on
     * the cloned copy if they are called.
     *
     * @group 51630
     * @return void
     */
    public function testCloneBeanDoesntKeepRelationship()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $account->load_relationship('contacts');

        // lets make sure the relationship is loaded
        $this->assertTrue(isset($account->contacts));

        $clone_account = clone $account;

        // lets make sure that the relationship is not on the cloned record
        $this->assertFalse(isset($clone_account->contacts));

        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Test whether a relate field is determined correctly
     *
     * @param array $field_defs
     * @param string $field_name
     * @param bool $is_relate
     * @dataProvider isRelateFieldProvider
     * @covers SugarBean::is_relate_field
     */
    public function testIsRelateField(array $field_defs, $field_name, $is_relate)
    {
        $bean = new BeanIsRelateFieldMock();
        $bean->field_defs = $field_defs;
        $actual = $bean->is_relate_field($field_name);

        if ($is_relate)
        {
            $this->assertTrue($actual);
        }
        else
        {
            $this->assertFalse($actual);
        }
    }

    public static function isRelateFieldProvider()
    {
        return array(
            // test for on a non-existing field
            array(
                array(), 'dummy', false,
            ),
            // test for non-specified field type
            array(
                array(
                    'my_field' => array(),
                ), 'my_field', false,
            ),
            // test on a non-relate field type
            array(
                array(
                    'my_field' => array(
                        'type' => 'varchar',
                    ),
                ), 'my_field', false,
            ),
            // test on a relate field type but link not specified
            array(
                array(
                    'my_field' => array(
                        'type' => 'relate',
                    ),
                ), 'my_field', false,
            ),
            // test when only link is specified
            array(
                array(
                    'my_field' => array(
                        'link' => 'my_link',
                    ),
                ), 'my_field', false,
            ),
            // test on a relate field type
            array(
                array(
                    'my_field' => array(
                        'type' => 'relate',
                        'link' => 'my_link',
                    ),
                ), 'my_field', true,
            ),
        );
    }

    /**
     * Test asserts behavior of haveFiles method
     *
     * @group 58955
     * @dataProvider getHaveFiles
     */
    public function testHaveFiles($class, $expected)
    {
        /**
         * @var SugarBean $bean
         */
        $bean = new $class();
        $this->assertEquals($expected, $bean->haveFiles(), 'Result is incorrect');
    }

    /**
     * Test asserts behavior of getFiles method
     *
     * @group 58955
     */
    public function testGetFiles()
    {
        $bean = new SugarBean58955Extends();
        $this->assertEmpty($bean->getFiles(), 'Incorrect result');

        $bean->id = 'test';
        $this->assertEquals(array('test'), $bean->getFiles(), 'Incorrect result');

        $bean = new SugarBean58955Implements();
        $this->assertEmpty($bean->getFiles(), 'Incorrect result');

        $bean->id = 'test';
        $this->assertEquals(array('test'), $bean->getFiles(), 'Incorrect result');

        $bean = new SugarBean58955Image();
        $bean->id = 'test';
        $this->assertEmpty($bean->getFiles(), 'Incorrect result');

        $bean->image = 'test';
        $this->assertEquals(array('test'), $bean->getFiles(), 'Incorrect result');
    }

    /**
     * Data provider for testHaveFiles
     * @return array
     */
    public function getHaveFiles()
    {
        return array(
            array('SugarBean58955Extends', true),
            array('SugarBean58955Implements', true),
            array('SugarBean58955Image', true),
            array('SugarBean', false),
        );
    }

    /**
     * Test asserts behavior of getFilesFields method
     *
     * @group 58955
     */
    public function testGetFilesFields()
    {
        $bean = new SugarBean58955Extends();
        $this->assertEquals(array('id'), $bean->getFilesFields(), 'Incorrect result');

        $bean = new SugarBean58955Implements();
        $this->assertEquals(array('id'), $bean->getFilesFields(), 'Incorrect result');

        $bean = new SugarBean58955Image();
        $this->assertEquals(array('image'), $bean->getFilesFields(), 'Incorrect result');
    }
}

/**
 * Class SugarBean58955Extends
 * Mock for testHaveFiles & testGetFiles tests
 */
class SugarBean58955Extends extends File
{
    /**
     * @var string
     */
    public $module_name = 'SugarBean58955Extends';

    public function __construct()
    {
        $this->field_defs = array();
    }
}

/**
 * Class SugarBean58955Implements
 * Mock for testHaveFiles & testGetFiles tests
 */
class SugarBean58955Implements extends SugarBean
{
    /**
     * @var string
     */
    public $module_name = 'SugarBean58955Implements';

    public function __construct()
    {
        $this->field_defs = array();
    }

    public function bean_implements($interface)
    {
        if ($interface == 'FILE') {
            return true;
        }
        return parent::bean_implements($interface);
    }
}

/**
 * Class SugarBean58955Image
 * Mock for testHaveFiles & testGetFiles tests
 */
class SugarBean58955Image extends SugarBean
{
    /**
     * @var string
     */
    public $module_name = 'SugarBean58955Image';

    public function __construct()
    {
        $this->field_defs = array(
            'image' => array(
                'type' => 'image'
            )
        );
    }
}

// Using Mssql here because mysql needs real connection for quoting
require_once 'include/database/MssqlManager.php';
class MockMysqlDb extends MssqlManager
{
    public $database = true;
    public $lastQuery;

    public function connect(array $configOptions = null, $dieOnError = false)
    {
        return true;
    }

    public function query($sql, $dieOnError = false, $msg = '', $suppress = false, $keepResult = false)
    {
        $this->lastQuery = $sql;
        return true;
    }

    public function fetchByAssoc($result, $encode = true)
    {
        return false;
    }
}

class BeanMockTestObjectName extends SugarBean
{
    var $table_name = "my_table";

    function BeanMockTestObjectName() {
		parent::SugarBean();
	}
}

class BeanIsRelateFieldMock extends SugarBean
{
    public function is_relate_field($field_name_name)
    {
        return parent::is_relate_field($field_name_name);
    }
}
