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


require_once('include/SearchForm/SearchForm2.php');

class Bug48623Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->setPreference('timezone', 'EDT');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider dateTestProvider
     */
    public function testParseDateExpressionWithAndWithoutTimezoneAdjustment($expected1, $expected2, $operator, $type) {
        global $timedate;

        $seed = new Opportunity();
        $sf = new SearchForm2Wrap($seed, 'Opportunities', 'index');

        $where = $sf->publicParseDateExpression($operator, 'opportunities.date_closed', $type);
        $this->assertRegExp($expected1, $where);
        $this->assertRegExp($expected2, $where);
    }

    public function dateTestProvider() {
        $noTzRegExp1 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 00:00:00\'/';
        $noTzRegExp2 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 23:59:59\'/';
        $tzRegExp1 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 0[4,5]:00:00\'/';
        $tzRegExp2 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 0[3,4]:59:59\'/';
        return array(
            //  $expected1, expected2, $operator, $type
            array($noTzRegExp1, $noTzRegExp2, 'this_month', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'last_month', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'next_month', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'this_year', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'last_year', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'next_year', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'yesterday', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'today', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'tomorrow', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'last_7_days', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'next_7_days', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'last_30_days', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'next_30_days', 'date'),

            array($tzRegExp1, $tzRegExp2, 'this_month', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'last_month', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'next_month', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'this_year', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'last_year', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'next_year', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'yesterday', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'today', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'tomorrow', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'last_7_days', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'next_7_days', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'last_30_days', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'next_30_days', 'datetime'),
        );
    }

}


/**
 * Wrap the SearchForm class to make a protected function public
 */
class SearchForm2Wrap extends SearchForm {
    public function publicParseDateExpression($operator, $db_field, $field_type) {
        return $this->parseDateExpression($operator, $db_field, $field_type);
    }
}
