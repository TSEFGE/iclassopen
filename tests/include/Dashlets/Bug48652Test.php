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
 * @ticket 48652
 */
class Bug48652Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Name of test option set
     *
     * @var string
     */
    protected $options = 'bug_48652_options';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        global $app_list_strings;
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Home'));

        // populate test options with blank value
        $app_list_strings[$this->options] = array(
            '' => '',
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Ensure that module labels are built correctly
     */
    public function testBlankOptionsAreNotFiltered()
    {
        global $app_strings;

        // name of test field
        $field_name = 'bug_48652_field';

        // create a bean with minimal needed set of field definitions
        $seedBean = new SugarBean();
        $seedBean->field_defs = array(
            $field_name => array(
                'name'         => null,
                'vname'        => null,
                'type'         => 'enum',
                'remove_blank' => false,
                'options'      => $this->options,
            ),
        );

        // create a dashlet containing bean
        require_once 'include/Dashlets/DashletGeneric.php';
        $dashlet = new DashletGeneric(null);
        $dashlet->seedBean = $seedBean;
        $dashlet->columns = array();
        $dashlet->searchFields = array(
            $field_name => array(),
        );

        // generate dashlet setup form
        $dashlet->processDisplayOptions();

        // ensure that the generated element contains blank option
        $search_fields = $dashlet->currentSearchFields;
        $this->assertArrayHasKey($field_name, $search_fields);
        $this->assertArrayHasKey('input', $search_fields[$field_name]);
        $this->assertContains('>' . $app_strings['LBL_NONE'] . '<', $search_fields[$field_name]['input']);
    }
}
