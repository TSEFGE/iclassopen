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


require_once('include/nusoap/nusoap.php');
require_once('include/TimeDate.php');

abstract class SOAPTestCase extends Sugar_PHPUnit_Framework_TestCase
{
	public static $_user = null;
	public $_soapClient = null;
	public $_session = null;
	public $_sessionId = '';
    public $_soapURL = '';

    public static function setUpBeforeClass()
    {
        self::$_user = SugarTestUserUtilities::createAnonymousUser();
        self::$_user->status = 'Active';
        self::$_user->is_admin = 1;
        self::$_user->save();
        $GLOBALS['db']->commit();
        $GLOBALS['current_user'] = self::$_user;
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($GLOBALS['current_user']);
        $GLOBALS['db']->commit();
    }

    /**
     * Create test user
     *
     */
	public function setUp()
    {
        $beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

        $this->_soapClient = new nusoapclient($this->_soapURL,false,false,false,false,false,600,600);
        parent::setUp();
        $GLOBALS['db']->commit();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown()
    {
        $this->_sessionId = '';

		unset($GLOBALS['beanList']);
		unset($GLOBALS['beanFiles']);
        $GLOBALS['db']->commit();
    }

    protected function _login()
    {
        $GLOBALS['db']->commit();
    	$result = $this->_soapClient->call('login',
            array('user_auth' =>
                array('user_name' => self::$_user->user_name,
                    'password' => self::$_user->user_hash,
                    'version' => '.01'),
                'application_name' => 'SoapTest', "name_value_list" => array())
            );
        $this->_sessionId = $result['id'];
		return $result;
    }

    /**
     * Create a test user
     *
     */
	public function _setupTestUser() {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['db']->commit();
        $GLOBALS['current_user'] = $this->_user;
    }

    /**
     * Remove user created for test
     *
     */
	public function _tearDownTestUser() {
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
       unset($GLOBALS['current_user']);
    }

}
