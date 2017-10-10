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

require_once 'include/database/MssqlManager.php';

class MssqlManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var MssqlManager
     */
    private $_db = null;

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
        $this->_db = new MssqlManager();
    }

    public function testQuote()
    {
        $string = "'dog eat ";
        $this->assertEquals($this->_db->quote($string),"''dog eat ");
    }

    public function testArrayQuote()
    {
        $string = array("'dog eat ");
        $this->_db->arrayQuote($string);
        $this->assertEquals($string,array("''dog eat "));
    }

    public function providerConvert()
    {
        $returnArray = array(
                array(
                    array('foo','today'),
                    'GETDATE()'
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
                    'LEFT(CONVERT(varchar(10),foo,120),10)'
                    ),
                array(
                    array('foo','date_format',array('1','2','3')),
                    'LEFT(CONVERT(varchar(10),foo,120),10)'
                    ),
                array(
                    array('foo','date_format',array("'%Y-%m'")),
                    'LEFT(CONVERT(varchar(7),foo,120),7)'
                    ),
                array(
                    array('foo','IFNULL'),
                    'ISNULL(foo,\'\')'
                    ),
                array(
                    array('foo','IFNULL',array('1','2','3')),
                    'ISNULL(foo,1,2,3)'
                    ),
                array(
                    array('foo','CONCAT',array('1','2','3')),
                    'foo+1+2+3'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT'),
                    '1+2+3'
                    ),
                array(
                    array(array('1','2','3'),'CONCAT',array('foo', 'bar')),
                    '1+2+3+foo+bar'
                    ),
                array(
                    array('foo','text2char'),
                    'CAST(foo AS varchar(8000))'
                ),
                array(
                    array('foo','length'),
                    "LEN(foo)"
                ),
                array(
                    array('foo','month'),
                    "MONTH(foo)"
                ),
                array(
                    array('foo','quarter'),
                    "DATENAME(quarter, foo)"
                ),
                array(
                    array('foo','add_date',array(1,'day')),
                    "DATEADD(day,1,foo)"
                ),
                array(
                    array('foo','add_date',array(2,'week')),
                    "DATEADD(week,2,foo)"
                ),
                array(
                    array('foo','add_date',array(3,'month')),
                    "DATEADD(month,3,foo)"
                ),
                array(
                    array('foo','add_date',array(4,'quarter')),
                    "DATEADD(quarter,4,foo)"
                ),
                array(
                    array('foo','add_date',array(5,'year')),
                    "DATEADD(year,5,foo)"
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
         $this->assertEquals("LTRIM(RTRIM(ISNULL(foo.col1,'')+' '+ISNULL(foo.col2,'')+' '+ISNULL(foo.col3,'')))", $ret);
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
                     '2009-01-01'
                     ),
                 array(
                     array('2009-01-01 12:00:00','time'),
                     '12:00:00'
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

    /**
     * @group bug50024 - connect fails when not passed a db_name config option
     */
    public function testConnectWithNoDbName()
    {
        if ( ($GLOBALS['db']->dbType != 'mssql') || !function_exists('mssql_connect'))
            $this->markTestSkipped('Only applies to SQL Server legacy driver.');

        // set up a connection w/o a db_name
        $configOptions = array(
            'db_host_name' => $GLOBALS['db']->connectOptions['db_host_name'],
            'db_host_instance' => $GLOBALS['db']->connectOptions['db_host_instance'],
            'db_user_name' => $GLOBALS['db']->connectOptions['db_user_name'],
            'db_password' => $GLOBALS['db']->connectOptions['db_password'],
        );

        $this->assertTrue($this->_db->connect($configOptions));
    }

    public function providerFullTextQuery()
    {
        return array(
            array(array('word1'), array(), array(),
                "CONTAINS(unittest, '(\"word1\")')"),
            array(array("'word1'"), array(), array(),
                "CONTAINS(unittest, '(\"''word1''\")')"),
            array(array("\"word1\""), array(), array(),
                "CONTAINS(unittest, '(\"word1\")')"),
            array(array('word1', 'word2'), array(), array(),
                "CONTAINS(unittest, '(\"word1\" | \"word2\")')"),
            array(array('word1', 'word2'), array('mustword'), array(),
                "CONTAINS(unittest, '\"mustword\" AND (\"word1\" | \"word2\")')"),
            array(array('word1', 'word2'), array('mustword', 'mustword2'), array(),
                "CONTAINS(unittest, '\"mustword\" AND \"mustword2\" AND (\"word1\" | \"word2\")')"),
            array(array(), array('mustword', 'mustword2'), array(),
                "CONTAINS(unittest, '\"mustword\" AND \"mustword2\"')"),
            array(array('word1'), array(), array('notword'),
                "CONTAINS(unittest, '(\"word1\") AND  NOT \"notword\"')"),
            array(array('word1'), array(), array('notword', 'notword2'),
                "CONTAINS(unittest, '(\"word1\") AND  NOT \"notword\" AND  NOT \"notword2\"')"),
            array(array('word1', 'word2'), array('mustword', 'mustword2'), array('notword', 'notword2'),
                "CONTAINS(unittest, '\"mustword\" AND \"mustword2\" AND (\"word1\" | \"word2\") AND  NOT \"notword\" AND  NOT \"notword2\"')"),
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

    /**
     * Test checks order by string in different queries
     *
     * @group 54990
     * @dataProvider getQueriesForReturnOrderBy
     */
    public function testReturnOrderBy($query, $start, $count, $expected)
    {
        $actual = $this->_db->limitQuery($query, $start, $count, false, '', false);
        $this->assertContains($expected, $actual, 'Order By is incorrect');
    }

    /**
     * Data provider for testReturnOrderBy
     * Returns queries with different functions, offsets & aliases
     *
     * @return array
     */
    static public function getQueriesForReturnOrderBy()
    {
        return array(
            array(
                "SELECT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a1 ASC",
                0,
                1,
                "(ORDER BY t1.f1 ASC)"
            ),
            array(
                "SELECT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a2 ASC",
                0,
                1,
                "(ORDER BY t1.f2 ASC)"
            ),
            array(
                "SELECT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f1 ASC",
                0,
                1,
                "(ORDER BY t1.f1 ASC)"
            ),
            array(
                "SELECT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f2 ASC",
                0,
                1,
                "(ORDER BY t1.f2 ASC)"
            ),

            array(
                "SELECT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a1 ASC",
                1,
                1,
                "(ORDER BY t1.f1 ASC)"
            ),
            array(
                "SELECT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a2 ASC",
                1,
                1,
                "(ORDER BY t1.f2 ASC)"
            ),
            array(
                "SELECT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f1 ASC",
                1,
                1,
                "(ORDER BY t1.f1 ASC)"
            ),
            array(
                "SELECT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f2 ASC",
                1,
                1,
                "(ORDER BY t1.f2 ASC)"
            ),

            array(
                "SELECT DISTINCT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a1 ASC",
                0,
                1,
                "ORDER BY a1 ASC"
            ),
            array(
                "SELECT DISTINCT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a2 ASC",
                0,
                1,
                "ORDER BY a2 ASC"
            ),
            array(
                "SELECT DISTINCT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f1 ASC",
                0,
                1,
                "ORDER BY t1.f1 ASC"
            ),
            array(
                "SELECT DISTINCT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f2 ASC",
                0,
                1,
                "ORDER BY t1.f2 ASC"
            ),

            array(
                "SELECT DISTINCT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a1 ASC",
                1,
                1,
                "(ORDER BY t1.f1 ASC)"
            ),
            array(
                "SELECT DISTINCT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a2 ASC",
                1,
                1,
                "(ORDER BY t1.f2 ASC)"
            ),
            array(
                "SELECT DISTINCT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f1 ASC",
                1,
                1,
                "(ORDER BY t1.f1 ASC)"
            ),
            array(
                "SELECT DISTINCT t1.f1 a1, t1.f2 a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f2 ASC",
                1,
                1,
                "(ORDER BY t1.f2 ASC)"
            ),

            array(
                "SELECT ISNULL( t1.f1, '' ) a1, ISNULL( t1.f2, '' ) a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a1 ASC",
                0,
                1,
                "(ORDER BY isnull( t1.f1, '' ) ASC)"
            ),
            array(
                "SELECT ISNULL( t1.f1, '' ) a1, ISNULL( t1.f2, '' ) a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a2 ASC",
                0,
                1,
                "(ORDER BY isnull( t1.f2, '' ) ASC)"
            ),
            array(
                "SELECT ISNULL( t1.f1, '' ) a1, ISNULL( t1.f2, '' ) a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f1 ASC",
                0,
                1,
                "(ORDER BY t1.f1 ASC)"
            ),
            array(
                "SELECT ISNULL( t1.f1, '' ) a1, ISNULL( t1.f2, '' ) a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f2 ASC",
                0,
                1,
                "(ORDER BY t1.f2 ASC)"
            ),

            array(
                "SELECT ISNULL( t1.f1, '' ) a1, ISNULL(  t1.f2, '' ) a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a1 ASC",
                1,
                1,
                "(ORDER BY isnull( t1.f1, '' ) ASC)"
            ),
            array(
                "SELECT ISNULL( t1.f1, '' ) a1, ISNULL( t1.f2, '' ) a2 FROM table1 t1 WHERE 1 = 1 ORDER BY a2 ASC",
                1,
                1,
                "(ORDER BY isnull( t1.f2, '' ) ASC)"
            ),
            array(
                "SELECT ISNULL( t1.f1, '' ) a1, ISNULL( t1.f2, '' ) a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f1 ASC",
                1,
                1,
                "(ORDER BY t1.f1 ASC)"
            ),
            array(
                "SELECT ISNULL( t1.f1, '' ) a1, ISNULL( t1.f2, '' ) a2 FROM table1 t1 WHERE 1 = 1 ORDER BY t1.f2 ASC",
                1,
                1,
                "(ORDER BY t1.f2 ASC)"
            ),

            array(
                "SELECT
                    ISNULL(accounts.id,'') primaryid,
                    ISNULL(accounts.name,'') accounts_name,
                    ISNULL(l2.id,'') l2_id,
                    l2.email_address l2_email_address
                FROM
                    accounts
                INNER JOIN
                    accounts_contacts l1_1
                ON
                    accounts.id=l1_1.account_id
                    AND l1_1.deleted=0
                INNER JOIN
                    contacts l1
                ON
                    l1.id=l1_1.contact_id
                    AND l1.deleted=0
                    AND l1.team_set_id IN (
                        SELECT
                            tst.team_set_id
                        from
                            team_sets_teams tst
                        INNER JOIN
                            team_memberships team_memberships
                        ON
                            tst.team_id = team_memberships.team_id
                            AND team_memberships.user_id = '5a409dc7-1cdb-278b-2222-511e6952dac8'
                            AND team_memberships.deleted=0
                    )
                INNER JOIN
                    email_addr_bean_rel l2_1
                ON
                    l1.id=l2_1.bean_id
                    AND l2_1.deleted=0
                    AND l2_1.primary_address = '1'
                INNER JOIN
                    email_addresses l2
                ON
                    l2.id=l2_1.email_address_id
                    AND l2.deleted=0
                WHERE
                    ((1=1)
                    AND accounts.team_set_id IN (
                        SELECT
                            tst.team_set_id
                        FROM
                            team_sets_teams tst
                        INNER JOIN
                            team_memberships team_memberships
                        ON
                            tst.team_id = team_memberships.team_id
                            AND team_memberships.user_id = '5a409dc7-1cdb-278b-2222-511e6952dac8'
                            AND team_memberships.deleted=0
                    ))
                    AND accounts.deleted=0
                ORDER BY
                    l2_email_address ASC
                ",
                1,
                1,
                "(ORDER BY l2.email_address ASC)"
            ),

            array(
                "SELECT
                    ISNULL(accounts.id,'') primaryid,
                    ISNULL(accounts.name,'') accounts_name,
                    ISNULL(l2.id,'') l2_id,
                    l2.email_address l2_email_address
                FROM
                    accounts
                INNER JOIN
                    accounts_contacts l1_1
                ON
                    accounts.id=l1_1.account_id
                    AND l1_1.deleted=0
                INNER JOIN
                    contacts l1
                ON
                    l1.id=l1_1.contact_id
                    AND l1.deleted=0
                    AND l1.team_set_id IN (
                        SELECT
                            tst.team_set_id
                        from
                            team_sets_teams tst
                        INNER JOIN
                            team_memberships team_memberships
                        ON
                            tst.team_id = team_memberships.team_id
                            AND team_memberships.user_id = 'c71f4b54-2058-5d8b-1d17-511e6b730b27'
                            AND team_memberships.deleted=0
                    )
                INNER JOIN
                    email_addr_bean_rel l2_1
                ON
                    l1.id=l2_1.bean_id
                    AND l2_1.deleted=0
                    AND l2_1.primary_address = '1'
                INNER JOIN
                    email_addresses l2
                ON
                    l2.id=l2_1.email_address_id
                    AND l2.deleted=0
                WHERE
                    ((1=1)
                    AND accounts.team_set_id IN (
                        SELECT
                            tst.team_set_id
                        FROM
                            team_sets_teams tst
                        INNER JOIN
                            team_memberships team_memberships
                        ON
                            tst.team_id = team_memberships.team_id
                            AND team_memberships.user_id = 'c71f4b54-2058-5d8b-1d17-511e6b730b27'
                            AND team_memberships.deleted=0
                    ))
                    AND  accounts.deleted=0
                ORDER BY
                    accounts_name ASC
                ",
                1,
                1,
                "(ORDER BY isnull(accounts.name,'') ASC)"
            ),

            array(
                "SELECT DISTINCT meetings.id,
                    LTRIM(RTRIM(ISNULL(jt0.first_name,'')+' '+ISNULL(jt0.last_name,''))) assigned_user_name,
                    'Users' assigned_user_name_mod,
                    meetings.date_entered
                FROM meetings
                LEFT JOIN
                    users jt0
                ON
                    meetings.assigned_user_id=jt0.id
                    AND jt0.deleted=0
                    AND jt0.deleted=0
                LEFT JOIN
                    sugarfavorites sfav
                ON
                    sfav.module ='Meetings'
                    AND sfav.record_id=meetings.id
                    AND sfav.created_by='1'
                    AND sfav.deleted=0
                where (
                        (meetings.status IN ('Planned'))
                        AND (meetings.assigned_user_id IN ('1','seed_chris_id','seed_jim_id'))
                    ) AND meetings.deleted=0
                ORDER BY
                    meetings.date_entered DESC
                ",
                1,
                1,
                "group by meetings.id, LTRIM(RTRIM(ISNULL(jt0.first_name,'')+' '+ISNULL(jt0.last_name,''))), meetings.date_entered"
            ),

            array(
                "SELECT DISTINCT m1.id,
                    m1.name,
                    m1.date_start,
                    m1.date_end,
                    m1.assigned_user_id
                FROM
                    meetings_users rt
                inner join
                    meetings m1
                on
                    rt.meeting_id = m1.id
                inner join
                    users m2
                on
                    rt.user_id = m2.id
                    AND m2.id = '1'
                WHERE
                    (m1.deleted = 0)
                    AND m2.id = '1'
                ",
                1,
                1,
                "(ORDER BY m1.id, m1.name, m1.date_start, m1.date_end, m1.assigned_user_id)"
            ),

            array(
                "SELECT DISTINCT rt.id,
                    m1.name,
                    m1.date_start,
                    m1.date_end,
                    m1.assigned_user_id
                FROM
                    meetings_users rt
                inner join
                    meetings m1
                on
                    rt.meeting_id = m1.id
                inner join
                    users m2
                on
                    rt.user_id = m2.id
                    AND m2.id = '1'
                WHERE
                    (m1.deleted = 0)
                    AND m2.id = '1'
                ",
                1,
                1,
                "(ORDER BY rt.id, m1.name, m1.date_start, m1.date_end, m1.assigned_user_id)"
            ),
        );
    }
}
