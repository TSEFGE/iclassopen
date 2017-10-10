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
 * Bug49991Test.php
 * @author Collin Lee
 *
 * This test will check the enhancements made so that we may better load custom files.  While the bug was
 * originally filed for the Connectors module, this change was applied to the SugarView layer to allow all
 * views to take advantage of not having to repeatedly check the custom directory for the presence of a file.
 */
require_once('include/MVC/View/SugarView.php');

class Bug49991Test extends Sugar_PHPUnit_Framework_TestCase
{

var $mock;
var $sourceBackup;

public function setUp()
{
    $this->mock = new Bug49991SugarViewMock();
    mkdir_recursive('custom/modules/Connectors/tpls');
    if(file_exists('custom/modules/Connectors/tpls/source_properties.tpl'))
    {
        $this->sourceBackup = file_get_contents('custom/modules/Connectors/tpls/source_properties.tpl');
    }
    copy('modules/Connectors/tpls/source_properties.tpl', 'custom/modules/Connectors/tpls/source_properties.tpl');
}

public function tearDown()
{
    if(!empty($this->sourceBackup))
    {
        file_put_contents('custom/modules/Connectors/tpls/source_properties.tpl', $this->sourceBackup);
    } else {
        unlink('custom/modules/Connectors/tpls/source_properties.tpl');
    }
    unset($this->mock);
}

/**
 * testGetCustomFilePathIfExists
 *
 * Simple test just to assert that we have found the custom file
 */
public function testGetCustomFilePathIfExists()
{
    $this->assertEquals('custom/modules/Connectors/tpls/source_properties.tpl', $this->mock->getCustomFilePathIfExistsTest('modules/Connectors/tpls/source_properties.tpl'), 'Could not find the custom tpl file');
}

}

class Bug49991SugarViewMock extends SugarView {

    public function getCustomFilePathIfExistsTest($file)
    {
        return $this->getCustomFilePathIfExists($file);
    }
}