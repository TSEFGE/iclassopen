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

require_once 'include/database/MysqlManager.php';

class MysqlManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
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
        if ( $GLOBALS['db']->dbType != 'mysql' ) {
            $this->markTestSkipped('Only applies to MySQL');
        }

        $this->_db = new MysqlManager();
    }

    public function testQuote()
    {
        $string = "'dog eat ";
        if(!$this->_db->valid()) $this->markTestSkipped("MySQL not enabled");
        $this->assertEquals($this->_db->quote($string),"\\'dog eat ");
    }

    public function testArrayQuote()
    {
        if(!$this->_db->valid()) $this->markTestSkipped("MySQL not enabled");
        $string = array("'dog eat ");
        $this->_db->arrayQuote($string);
        $this->assertEquals($string,array("\\'dog eat "));
    }

    public function providerConvert()
    {
        $returnArray = array(
            array(
                array('foo','nothing'),
                'foo'
                ),
                array(
                    array('foo','today'),
                    'CURDATE()'
                    ),
                array(
                    array('foo','left'),
                    'LEFT(foo)'
                ),
                array(
                    array('foo','left',array('1','2','3')),
                    'LEFT(foo,1,2,3)'
                    ),
                array(
                    array('foo','date_format'),
                    'DATE_FORMAT(foo,\'%Y-%m-%d\')'
                        ),
                array(
                    array('foo','date_format',array('1','2','3')),
                    'DATE_FORMAT(foo,\'1\')'
                    ),
                array(
                    array('foo','date_format',array("'1'","'2'","'3'")),
                    'DATE_FORMAT(foo,\'1\')'
                    ),
                    array(
                    array('foo','datetime',array("'%Y-%m'")),
                    'foo'
                        ),
                array(
                    array('foo','IFNULL'),
                    'IFNULL(foo,\'\')'
                    ),
                array(
                    array('foo','IFNULL',array('1','2','3')),
                    'IFNULL(foo,1,2,3)'
                    ),
                array(
                    array('foo','CONCAT',array('1','2','3')),
                    'CONCAT(foo,1,2,3)'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT'),
                    'CONCAT(1,2,3)'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT',array('foo', 'bar')),
                    'CONCAT(1,2,3,foo,bar)'
                    ),
                array(
                    array('foo','text2char'),
                    'foo'
                ),
                array(
                    array('foo','length'),
                    "LENGTH(foo)"
                ),
                array(
                    array('foo','month'),
                    "MONTH(foo)"
                ),
                array(
                    array('foo','quarter'),
                    "QUARTER(foo)"
                ),
                array(
                    array('foo','add_date',array(1,'day')),
                    "DATE_ADD(foo, INTERVAL 1 day)"
                ),
                array(
                    array('foo','add_date',array(2,'week')),
                    "DATE_ADD(foo, INTERVAL 2 week)"
                ),
                array(
                    array('foo','add_date',array(3,'month')),
                    "DATE_ADD(foo, INTERVAL 3 month)"
                ),
                array(
                    array('foo','add_date',array(4,'quarter')),
                    "DATE_ADD(foo, INTERVAL 4 quarter)"
                ),
                array(
                    array('foo','add_date',array(5,'year')),
                    "DATE_ADD(foo, INTERVAL 5 year)"
                ),
        );
        return $returnArray;
    }

    /**
     * @ticket 33283
     * @dataProvider providerConvert
     */
    public function testConvert(array $parameters, $result)
    {
        $this->assertEquals($result, call_user_func_array(array($this->_db, "convert"), $parameters));
     }

     /**
      * @ticket 33283
      */
     public function testConcat()
     {
         $ret = $this->_db->concat('foo',array('col1','col2','col3'));
         $this->assertEquals("LTRIM(RTRIM(CONCAT(IFNULL(foo.col1,''),' ',IFNULL(foo.col2,''),' ',IFNULL(foo.col3,''))))", $ret);
     }

     public function providerFromConvert()
     {
         $returnArray = array(
             array(
                 array('foo','nothing'),
                 'foo'
                 ),
                 array(
                     array('2009-01-01 12:00:00','date'),
                     '2009-01-01 12:00:00'
                     ),
                 array(
                     array('2009-01-01 12:00:00','time'),
                     '2009-01-01 12:00:00'
                     )
                 );

         return $returnArray;
     }

     /**
      * @ticket 33283
      * @dataProvider providerFromConvert
      */
     public function testFromConvert(
         array $parameters,
         $result
         )
     {
         $this->assertEquals(
             $this->_db->fromConvert($parameters[0],$parameters[1]),
             $result);
    }

    public function providerFullTextQuery()
    {
        return array(
            array(array('word1'), array(), array(),
                "MATCH(unittest) AGAINST('word1' IN BOOLEAN MODE)"),
            array(array("'word1'"), array(), array(),
                "MATCH(unittest) AGAINST('\'word1\'' IN BOOLEAN MODE)"),
            array(array('word1', 'word2'), array(), array(),
                "MATCH(unittest) AGAINST('word1 word2' IN BOOLEAN MODE)"),
            array(array('word1', 'word2'), array('mustword'), array(),
                "MATCH(unittest) AGAINST('word1 word2 +mustword' IN BOOLEAN MODE)"),
            array(array('word1', 'word2'), array('mustword', 'mustword2'), array(),
                "MATCH(unittest) AGAINST('word1 word2 +mustword +mustword2' IN BOOLEAN MODE)"),
            array(array(), array('mustword', 'mustword2'), array(),
                "MATCH(unittest) AGAINST('+mustword +mustword2' IN BOOLEAN MODE)"),
            array(array('word1'), array(), array('notword'),
                "MATCH(unittest) AGAINST('word1 -notword' IN BOOLEAN MODE)"),
            array(array('word1'), array(), array('notword', 'notword2'),
                "MATCH(unittest) AGAINST('word1 -notword -notword2' IN BOOLEAN MODE)"),
            array(array('word1', 'word2'), array('mustword', 'mustword2'), array('notword', 'notword2'),
                "MATCH(unittest) AGAINST('word1 word2 +mustword +mustword2 -notword -notword2' IN BOOLEAN MODE)"),
        );
    }

    /**
     * @ticket 37435
     * @dataProvider providerFullTextQuery
     * @param array $terms
     * @param string $result
     */
    public function testFullTextQuery($terms, $must_terms, $exclude_terms, $result)
    {
        $this->assertEquals($result,
        		$this->_db->getFulltextQuery('unittest', $terms, $must_terms, $exclude_terms));
    }
}
