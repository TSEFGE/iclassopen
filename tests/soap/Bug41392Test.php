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


require_once('tests/service/SOAPTestCase.php');

/**
 * Bug #41392
 * Wildcard % searching does not return email addresses when searching with outlook plugin
 *
 * @author mgusev@sugarcrm.com
 * @ticket 41392
 */
class Bug41392Test extends SOAPTestCase
{
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
    }

    /**
     * Test creates new account and tries to find the account by wildcard of its email
     *
     * @group 41392
     */
    public function testSearchByModule()
    {
        $user = new User();
        $user->retrieve(1);

        $account = new Account();
        $account->name = 'Bug4192Test';
        $account->email1 = 'Bug4192Test@example.com';
        $account->save();
        $GLOBALS['db']->commit();

        $params = array(
            'user_name' => $user->user_name,
            'password' => $user->user_hash,
            'search_string' => '%@example.com',
            'modules' => array(
                'Accounts'
            ),
            'offset' => 0,
            'max_results' => 30
        );

        $actual = $this->_soapClient->call('search_by_module', $params);
        $account->mark_deleted($account->id);

        $this->assertGreaterThan(0, $actual['result_count'], 'Call must return one bean minimum');
        $this->assertEquals('Accounts', $actual['entry_list'][0]['module_name'], 'Bean must be account');
        $this->assertEquals($account->id, $actual['entry_list'][0]['id'], 'Bean id must be same as id of created account');
    }
}
