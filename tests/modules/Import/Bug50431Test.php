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



require_once('modules/Import/views/view.step3.php');

/**
 * Bug50431Test.php
 *
 * This file tests the getMappingClassName function in modules/Import/views/view.step3.php
 *
 */
class Bug50431Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $customMappingFile = 'custom/modules/Import/maps/ImportMapCustomTestImportToken.php';
    private $customMappingFile2 = 'custom/modules/Import/maps/ImportMapTestImportToken.php';
    private $customMappingFile3 = 'custom/modules/Import/maps/ImportMapOther.php';
    private $outOfBoxTestFile = 'modules/Import/maps/ImportMapTestImportToken.php';
    private $source = 'TestImportToken';

    public function setUp()
    {
        if (!is_dir('custom/modules/Import/maps'))
        {
            mkdir_recursive('custom/modules/Import/maps');
        }

        file_put_contents($this->customMappingFile, '<?php class ImportMapCustomTestImportToken { } ');
        file_put_contents($this->customMappingFile2, '<?php class ImportMapTestImportToken { } ');
        file_put_contents($this->customMappingFile3, '<?php class ImportMapOther { } ');
        file_put_contents($this->outOfBoxTestFile, '<?php class ImportMapTestImportTokenOutOfBox { } ');
    }

    public function tearDown()
    {
        if(file_exists($this->customMappingFile))
        {
            unlink($this->customMappingFile);
        }

        if(file_exists($this->customMappingFile2))
        {
            unlink($this->customMappingFile2);
        }

        if(file_exists($this->customMappingFile3))
        {
            unlink($this->customMappingFile3);
        }

        if(file_exists($this->outOfBoxTestFile))
        {
            unlink($this->outOfBoxTestFile);
        }
    }

    public function testGetMappingClassName()
    {
        $view = new Bug50431ImportViewStep3Mock();
        $result = $view->getMappingClassName($this->source);

        $this->assertEquals('ImportMapCustomTestImportToken', $result, 'Failed to load ' . $this->customMappingFile);

        unlink($this->customMappingFile);

        $result = $view->getMappingClassName($this->source);

        $this->assertEquals('ImportMapTestImportToken', $result, 'Failed to load ' . $this->customMappingFile2);

        unlink($this->customMappingFile2);

        $result = $view->getMappingClassName($this->source);

        $this->assertEquals('ImportMapTestImportToken', $result, 'Failed to load ' . $this->outOfBoxTestFile);

        unlink($this->outOfBoxTestFile);

        $result = $view->getMappingClassName($this->source);

        $this->assertEquals('ImportMapOther', $result, 'Failed to load ' . $this->customMappingFile3);
    }

}


class Bug50431ImportViewStep3Mock extends ImportViewStep3
{
    public function getMappingClassName($source)
    {
        return parent::getMappingClassName($source);
    }
}