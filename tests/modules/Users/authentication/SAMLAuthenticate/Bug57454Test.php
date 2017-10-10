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

/**
 * @ticket 57454
*/
class Bug57454Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if(!function_exists('gzinflate')) {
            $this->markTestSkipped("No gzip - skipping");
        }
            if(!function_exists('simplexml_load_string')) {
            $this->markTestSkipped("No SimpleXML - skipping");
        }
        parent::setUp();
    }

    public function testSAMLEncoding()
    {
        require_once('modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml.php');
        require('modules/Users/authentication/SAMLAuthenticate/settings.php');
        $authrequest = new SamlAuthRequest($settings);
        $url = $authrequest->create();
        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertNotEmpty($query, 'No query part');
        parse_str($query, $components);
        $this->assertArrayHasKey('SAMLRequest', $components);
        $data = gzinflate(base64_decode(rawurldecode($components['SAMLRequest'])));
        $this->assertNotEmpty($data, "Data did not decode");
        $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NONET);
        $this->assertNotEmpty($xml, 'XML did not parse');
        $myurl = $xml['AssertionConsumerServiceURL'];
        $this->assertNotEmpty($myurl, 'URL not found');
        $this->assertEquals(parse_url($GLOBALS['sugar_config']['site_url']. "/index.php", PHP_URL_PATH), parse_url($myurl, PHP_URL_PATH), "Bad URL");
    }
}