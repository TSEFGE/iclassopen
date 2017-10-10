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


require_once('include/VarDefHandler/VarDefHandler.php');

/**
 * Bug #34785
 * Bug : relate field not available for alert template : Shows empty
 *
 * @author mgusev@sugarcrm.com
 * @ticket 34785
 */
class Bug34785Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test tries to assert related field in array which was filtered by template_filter
     *
     * @group 34785
     * @return void
     */
    public function testRelatedField()
    {
        $module = new SugarBean();
        $varDefHandler = new VarDefHandler($module, 'template_filter');

        $module->module_dir = 'Account';
        $module->field_defs = array(
            'bug_field_c' => array(
                'name' => 'bug_field_c',
                'source' => 'non-db',
                'type' => 'relate',
            )
        );

        $this->assertArrayHasKey('bug_field_c', $varDefHandler->get_vardef_array(true), 'Related field is not exist!');
    }
}

