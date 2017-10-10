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

class Bug51161Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;



	public function setUp()
    {
	    $this->_db = DBManagerFactory::getInstance();
        $this->useOutputBuffering = false;
	}

	public function tearDown()
	{

	}


	public function providerBug51161()
    {
        $returnArray = array(
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'varchar',
						'len' => '34',
						),
					),
					'/foo\s+$baseType\(34\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'nvarchar',
						'len' => '35',
						),
					),
					'/foo\s+$baseType\(35\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'char',
						'len' => '23',
						),
					),
					'/foo\s+$baseType\(23\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'text',
						'len' => '1024',
						),
					),
					'/foo\s+$baseType\(1024\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'clob',
						),
					),
					'/foo\s+$colType/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'clob',
						'len' => '1024',
						),
					),
					'/foo\s+$baseType\(1024\)/i',
					1
				),
				array(
					array(
					'foo' => array (
						'name' => 'foo',
						'type' => 'blob',
						'len' => '1024',
						),
					),
					'/foo\s+$baseType\(1024\)/i',
					1
				),
           );

        return $returnArray;
    }

    /**
     * @dataProvider providerBug51161
     */

    public function testBug51161($fieldDef,$successRegex, $times)
    {
        // Allowing type part variables in passed in regular expression so that database specific mappings
        // can be accounted for in the test
        $ftype = $this->_db->getFieldType($fieldDef['foo']);
        $colType = $this->_db->getColumnType($ftype);
        $successRegex = preg_replace('/\$colType/', $colType, $successRegex);
        if($type = $this->_db->getTypeParts($colType)){
            if(isset($type['baseType']))
                $successRegex = preg_replace('/\$baseType/', $type['baseType'], $successRegex);
            if(isset($type['len']))
                $successRegex = preg_replace('/\$len/', $type['len'], $successRegex);
            if(isset($type['scale']))
                $successRegex = preg_replace('/\$scale/', $type['scale'], $successRegex);
            if(isset($type['arg']))
                $successRegex = preg_replace('/\$arg/', $type['arg'], $successRegex);
        }
        $result = $this->_db->createTableSQLParams('test', $fieldDef, array());
        $this->assertEquals($times, preg_match($successRegex, $result), "Resulting statement: $result failed to match /$successRegex/");
    }
}
