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
require_once('tests/modules/Users/AuthenticateTest.php');

/**
 * Bug50936Test.php
 * This tests that we can correctly load a custom settings.php file for SAMLAuthentication when called from
 * modules/Users/authentication/SAMLAuthenticate/index.php
 *
 * This tests mimics the contents of modules/Users/authentication/SAMLAuthenticate/index.php by placing it
 * in a custom directory minus the header() function call.  We can't include that because it'd just cause other issues
 */
class Bug50936Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    var $customContents;

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
        $GLOBALS['sugar_config']['SAML_X509Cert'] = 'Bug50936_X509Cert';

        //Create the custom directory if it does not exist
        if(!is_dir('custom/modules/Users/authentication/SAMLAuthenticate')) {
           mkdir_recursive('custom/modules/Users/authentication/SAMLAuthenticate');
        }

$contents = <<<EOQ
<?php
        require('modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml.php');
        require(get_custom_file_if_exists('modules/Users/authentication/SAMLAuthenticate/settings.php'));

        \$authrequest = new SamlAuthRequest(\$settings);
        \$url = \$authrequest->create();
        echo \$url;
EOQ;

        file_put_contents('custom/modules/Users/authentication/SAMLAuthenticate/index.php', $contents);

$contents = <<<EOQ
<?php
                // this function should be modified to return the SAML settings for the current use
                \$settings = new SamlSettings();
                // when using Service Provider Initiated SSO (starting at index.php), this URL asks the IdP to authenticate the user.
                \$settings->idp_sso_target_url = 'www.sugarcrm.com';

                // the certificate for the users account in the IdP
                \$settings->x509certificate = \$GLOBALS['sugar_config']['SAML_X509Cert'];

                // The URL where to the SAML Response/SAML Assertion will be posted
                \$settings->assertion_consumer_service_url = \$GLOBALS['sugar_config']['site_url']. "/index.php?module=Users&action=Authenticate";

                // Name of this application
                \$settings->issuer = "php-saml";

                // Tells the IdP to return the email address of the current user
                \$settings->name_identifier_format = "urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress";

        ?>
EOQ;

        if(file_exists('custom/modules/Users/authentication/SAMLAuthenticate/settings.php')) {
           $this->customContents = file_get_contents('custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
        }

        file_put_contents('custom/modules/Users/authentication/SAMLAuthenticate/settings.php', $contents);
	}

	public function tearDown()
	{
        //If we had a custom settings.php file already, just restore it
        if(!empty($this->customContents))
        {
            file_put_contents('custom/modules/Users/authentication/SAMLAuthenticate/settings.php', $this->customContents);
        } else {
            unlink('custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
        }

        //Remove the test index.php file
        if(file_exists('custom/modules/Users/authentication/SAMLAuthenticate/index.php'))
        {
            unlink('custom/modules/Users/authentication/SAMLAuthenticate/index.php');
        }

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

    public function testLoadCustomSettingsFromIndex()
    {
        require('custom/modules/Users/authentication/SAMLAuthenticate/index.php');
        $this->expectOutputRegex('/www\.sugarcrm\.com/', 'Failed to override custom/modules/Users/authentication/SAMLAuthenticate/settings.php');
    }


}
?>
