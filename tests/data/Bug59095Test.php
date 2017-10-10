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


/**
 * Bug #59095 : Quick Search field not returning defined limit results.
 *
 * @ticket 59095
 * @author myarotsky@sugarcrm.com
 */
class Bug59095Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $query;
    public function setUp()
    {
        global $sugar_config;
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        $sugar_config['disable_count_query'] = true;
        for ($i = 0; $i < 3; $i++)
        {
            SugarTestAccountUtilities::createAccount();
        }
        $this->query = "SELECT accounts.*  FROM accounts WHERE 1=1";
    }

    public function tearDown()
    {
        global $sugar_config;
        unset($sugar_config['disable_count_query']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Quick Search field not returning defined limit results.
     * @group 59095
     */
    public function testShouldReturnDefinedLimit()
    {
        $sb = BeanFactory::getBean('Accounts');
        $res = $sb->process_list_query($this->query, 0, 2);
        $this->assertEquals(2, count($res['list']));
    }
}
