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


require_once 'include/SearchForm/SearchForm2.php';

class FileLocatorTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $form;
    protected $tempfiles = array();

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $acc = new Account();
        $this->form = new SearchFormMock($acc, "Accounts");
    }

    public function tearDown()
    {
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        if(!empty($this->tempfiles)) {
            foreach($this->tempfiles as $file) {
                @unlink($file);
            }
        }
    }


    /**
     * Check file locator
     */
    public function testFileLocatorOptions()
    {
        $options = $this->form->getOptions();
        $this->assertNotEmpty($options['locator_class_params'][0]);
        $this->assertContains("custom/modules/Accounts/tpls/SearchForm", $options['locator_class_params'][0]);
        $this->assertContains("modules/Accounts/tpls/SearchForm", $options['locator_class_params'][0]);
    }

    /**
     * Check file locator
     */
    public function testFileLocatorSetOptions()
    {
        $paths = array('a', 'b', 'c');

        $options = array(
            'locator_class' => 'FileLocator',
            'locator_class_params' => array(
                $paths
            )
            );
        $this->form->setOptions($options);
        $options = $this->form->getOptions();
        $this->assertEquals($paths, $options['locator_class_params'][0]);
    }

    /**
     * Check file locator
     */
    public function testFileLocatorOptionsCtor()
    {
        $paths = array('a', 'b', 'c');

        $options = array(
            'locator_class' => 'FileLocator',
            'locator_class_params' => array(
                $paths
            )
            );
        $this->form = new SearchForm(new Account(), "Accounts", 'index', $options);
        $options = $this->form->getOptions();
        $this->assertEquals($paths, $options['locator_class_params'][0]);
    }

    public function testFileLocatorFindSystemFile()
    {
        $this->assertEquals("include/SearchForm/tpls/SearchFormGenericAdvanced.tpl",
            $this->form->locateFile('SearchFormGenericAdvanced.tpl'),
            "Wrong file location"
            );
    }

    public function testFileLocatorFindCustomFile()
    {
        sugar_mkdir('custom/include/SearchForm/tpls/', 0755, true);
        sugar_mkdir('custom/modules/Accounts/tpls/SearchForm', 0755, true);
        $this->tempfiles[]= 'custom/include/SearchForm/tpls/FileLocatorTest.tpl';
        file_put_contents('custom/include/SearchForm/tpls/FileLocatorTest.tpl', "unittest");
        $this->assertEquals("custom/include/SearchForm/tpls/FileLocatorTest.tpl",
            $this->form->locateFile('FileLocatorTest.tpl'),
            "Wrong file location"
            );

        $this->tempfiles[] = "custom/modules/Accounts/tpls/SearchForm/FileLocatorTest.tpl";
        file_put_contents('custom/modules/Accounts/tpls/SearchForm/FileLocatorTest.tpl', "unittest");
        $this->assertEquals("custom/modules/Accounts/tpls/SearchForm/FileLocatorTest.tpl",
            $this->form->locateFile('FileLocatorTest.tpl'),
            "Wrong file location"
            );
    }
}

class SearchFormMock extends SearchForm
{
    public function locateFile($file)
    {
        return parent::locateFile($file);
    }
}
