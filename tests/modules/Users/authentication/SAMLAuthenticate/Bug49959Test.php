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


require_once 'modules/Users/authentication/SAMLAuthenticate/SAMLAuthenticateUser.php';
require_once 'modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml/settings.php';
require_once 'modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml/response.php';

/**
 * @ticket 49959
 */
class Bug49959Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SAMLAuthenticateUserTest
     */
    protected static $auth;

    /**
     * @var User
     */
    protected static $user;
    protected static $user_id,
        $user_name  = 'Bug49959TestUser',
        $user_email = 'bug49959.test.user@example.com';

    /**
     * This method is called before the first test of this test class is run.
     *
     * Creates shared test resources
     */
    public static function setUpBeforeClass()
    {
        self::$auth = new SAMLAuthenticateUserTest;
        $user = self::$user = new User();

        $user->user_name = self::$user_name;
        $user->email1    = self::$user_email;

        self::$user_id = $user->save();
    }


    /**
     * This method is called after the last test of this test class is run.
     *
     * Removes shared test resources
     */
    public static function tearDownAfterClass()
    {
        self::$user->mark_deleted(self::$user_id);
    }

    /**
     * Test fetching of a user to be authenticated by different fields
     */
    public function testFetchUser()
    {
        // test fetching user by ID
        $user = self::$auth->fetch_user(self::$user_id, 'id');
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by username
        $user = self::$auth->fetch_user(self::$user_name, 'user_name');
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by email (default case)
        $user = self::$auth->fetch_user(self::$user_email);
        $this->assertEquals(self::$user_id, $user->id);

        // test fetching user by unsupported field
        $user = self::$auth->fetch_user(self::$user_email, 'unsupported_field');
        $this->assertNull($user->id);

        // test fetching non-existing user
        $user = self::$auth->fetch_user('some_wrong_key');
        $this->assertNull($user->id);
    }

    /**
     * Test that get_nameid() method of SamlResponse is called by default
     */
    public function testDefaultNameId()
    {
        // create a mock of SAML response
        $mock = $this->getResponse();
        $mock->expects($this->once())
            ->method('get_nameid');

        // create a default SAML settings object
        require(get_custom_file_if_exists('modules/Users/authentication/SAMLAuthenticate/settings.php'));

        // expect that get_nameid() method of response is used by default
        self::$auth->get_user_id($mock, $settings);
    }

    /**
     * Test that custom XPath is used when specified in settings
     */
    public function testCustomNameId()
    {
        $node_id = 'Bug49959Test';

        // create response with custom XML
        $mock2 = $this->getResponse();
        $mock2->xml = $this->getResponseXml($node_id);

        // create SAML settings object with custom name id definition
        require(get_custom_file_if_exists('modules/Users/authentication/SAMLAuthenticate/settings.php'));
        $settings->saml_settings['check']['user_name'] = '//root';

        // expect that user ID is fetched from the document according to settings
        $result = self::$auth->get_user_id($mock2, $settings);
        $this->assertEquals($node_id, $result);
    }

    /**
     * Returns a mock of SamlResponse object
     *
     * @return SamlResponse
     */
    protected function getResponse()
    {
        return $this->getMock('SamlResponse', array(), array(), '', false);
    }

    /**
     * Returns custom response XML document
     *
     * @param $node_id
     * @return DOMDocument
     */
    protected function getResponseXml($node_id)
    {
        $document = new DOMDocument();
        $document->loadXML('<root>' . $node_id . '</root>');
        $root = $document->createElement('root');
        $document->appendChild($root);
        return $document;
    }
}

/**
 * A SAMLAuthenticate class wrapper that makes some of its methods accessible
 */
class SAMLAuthenticateUserTest extends SAMLAuthenticateUser
{
    public function fetch_user($id, $field = null)
    {
        return parent::fetch_user($id, $field);
    }

    public function get_user_id($samlresponse, $settings)
    {
        return parent::get_user_id($samlresponse, $settings);
    }
}
