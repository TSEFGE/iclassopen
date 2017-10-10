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

require_once('modules/Users/authentication/AuthenticationController.php');
require_once('modules/Users/authentication/SAMLAuthenticate/SAMLAuthenticate.php');
require_once('tests/modules/Users/AuthenticateTest.php');

class Bug49069Test extends  Sugar_PHPUnit_Framework_TestCase
{

	public function setUp()
    {
        $GLOBALS['app'] = new SugarApplication();
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$this->sugar_config_old = $GLOBALS['sugar_config'];
    	$_REQUEST['user_name'] = 'foo';
    	$_REQUEST['user_password'] = 'bar';
    	$_SESSION['authenticated_user_id'] = true;
    	$_SESSION['hasExpiredPassword'] = false;
    	$_SESSION['isMobile'] = null;
        $GLOBALS['sugar_config']['authenticationClass'] = 'SAMLAuthenticate';
        //$this->useOutputBuffering = false;
	}

	public function tearDown()
	{
	    unset($GLOBALS['current_user']);
	    $GLOBALS['sugar_config'] = $this->sugar_config_old;
	    unset($_REQUEST['login_module']);
        unset($_REQUEST['login_action']);
        unset($_REQUEST['login_record']);
        unset($_REQUEST['user_name']);
        unset($_REQUEST['user_password']);
        unset($_SESSION['authenticated_user_id']);
        unset($_SESSION['hasExpiredPassword']);
        unset($_SESSION['isMobile']);
	}

    public function testDefaultUserNamePasswordNotSet()
    {
        unset($GLOBALS['sugar_config']['default_module']);
        unset($GLOBALS['sugar_config']['default_action']);
        $_REQUEST['action'] = 'Authenticate';
        $_REQUEST['login_module'] = 'foo';
        $_REQUEST['login_action'] = 'bar';
        $_REQUEST['login_record'] = '123';
        unset($_REQUEST['user_name']);
        unset($_REQUEST['user_password']);
        $authController = new AuthenticationController((!empty($GLOBALS['sugar_config']['authenticationClass'])? $GLOBALS['sugar_config']['authenticationClass'] : 'SugarAuthenticate'));

        $url = '';
        require('modules/Users/Authenticate.php');

        $this->assertEquals(
            'Location: index.php?module=foo&action=bar&record=123',
            $url
            );
    }

    public function testDefaultUserNamePasswordSet()
    {
        unset($GLOBALS['sugar_config']['default_module']);
        unset($GLOBALS['sugar_config']['default_action']);
        $_REQUEST['action'] = 'Authenticate';
        $_REQUEST['login_module'] = 'foo';
        $_REQUEST['login_action'] = 'bar';
        $_REQUEST['login_record'] = '123';
        $authController = new AuthenticationController((!empty($GLOBALS['sugar_config']['authenticationClass'])? $GLOBALS['sugar_config']['authenticationClass'] : 'SugarAuthenticate'));

        $url = '';
        require('modules/Users/Authenticate.php');

        $this->assertEquals(
            'Location: index.php?module=foo&action=bar&record=123',
            $url
            );
        
        $this->assertTrue(!empty($_REQUEST['user_name']), 'Assert that we automatically set a user_name in $_REQUEST');
        $this->assertEquals('foo', $_REQUEST['user_name']);
        $this->assertTrue(!empty($_REQUEST['user_password']), 'Assert that we automatically set a user_password in $_REQUEST');
        $this->assertEquals('bar', $_REQUEST['user_password']);
    }
}
?>
