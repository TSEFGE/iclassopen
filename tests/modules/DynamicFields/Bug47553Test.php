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
 * Bug47553Test.php
 * @author Collin Lee
 *
 * This is a test that simulates the DynamicField saveToVardef call to ensure that the vardef definition for the Employees module
 * is correctly built when the Users vardef is rebuilt.  In particular we are interested to make sure that the status field has
 * a studio attribute set to false and that the status field is not required for the Employees vardef definition.
 */

require_once('modules/DynamicFields/DynamicField.php');

class Bug47553Test extends Sugar_PHPUnit_Framework_TestCase
{

    var $cachedEmployeeVardefs;

    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        if(file_exists('cache/modules/Employees/Employeevardefs.php'))
        {
            $this->cachedEmployeeVardefs = file_get_contents('cache/modules/Employees/Employeevardefs.php');
            unlink('cache/modules/Employees/Employeevardefs.php');
        }
    }

    public function tearDown()
    {
        if(!empty($this->cachedEmployeeVardefs))
        {
            file_put_contents('cache/modules/Employees/Employeevardefs.php', $this->cachedEmployeeVardefs);
        }
    }

    public function testSaveUsersVardefs()
    {
        global $dictionary;
        $dynamicField = new DynamicField('Users');
        VardefManager::loadVardef('Users', 'User');
        $dynamicField->saveToVardef('Users', $dictionary['User']['fields']);
        //Test that we have refreshed the Employees vardef
        $this->assertTrue(file_exists('cache/modules/Employees/Employeevardefs.php'), 'cache/modules/Employees/Emloyeevardefs.php file not created');

        //Test that status is not set to be required
        $this->assertFalse($dictionary['Employee']['fields']['status']['required'], 'status field set to required');

        //Test that the studio attribute is set to false for status field
        $this->assertFalse($dictionary['Employee']['fields']['status']['studio'], 'status field studio not set to false');
    }

}