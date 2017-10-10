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


require_once("include/Sugarpdf/sugarpdf_config.php");
require_once 'include/tcpdf/tcpdf.php';
require_once 'include/SugarCache/SugarCacheFile.php';
require_once 'modules/Import/sources/ImportFile.php';
require_once 'Zend/Http/Response.php';

class SerializeEvilTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function testSugarCacheFile()
    {
        if(file_exists(sugar_cached("testevil.php"))) @unlink(sugar_cached("testevil.php"));
        $obj = 'test';
        try {
            $obj = unserialize('O:14:"SugarCacheFile":3:{s:13:"_cacheChanged";b:1;s:14:"_cacheFileName";s:12:"testevil.php";s:11:"_localStore";b:1;}');
        } catch(Exception $e) {
            $obj = null;
        }
        $this->assertNull($obj);
        unset($obj); // call dtor if object created
        $this->assertFileNotExists(sugar_cached("testevil.php"));
    }

    public function getDestructors()
    {
        return array(
            array("SugarCacheFile"),
            array("SugarTheme"),
            array("tcpdf"),
            array("ImportFile"),
            array("Zend_Http_Response_Stream"),
        );
    }

    /**
     * @dataProvider getDestructors
     *
     */
    public function testUnserializeExcept($name)
    {
        $len = strlen($name);
        $obj = 'test';
        try {
            $obj = unserialize("O:$len:\"$name\":1:{s:4:\"test\";b:1;}");
        } catch(Exception $e) {
             $obj = null;
        }
        $this->assertEmpty($obj);
    }
}
