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


require_once('modules/ModuleBuilder/controller.php');

/**
 * Bug #51172
 * Employees |  Employees custom fields not working
 *
 * @author imatsiushyna@sugarcrm.com
 * @ticket 51172
 */

class Bug51172Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     *  @var string name custom fields
     */
    protected $field_name = 'test_bug51172';

    /**
     *  @var string modules name
     */
    protected $module = 'Employees';
    protected $add_module = 'Users';

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        $_REQUEST = array();
        sugar_cache_clear('mod_strings.en_us');

        if(file_exists('custom/modules/'.$this->module.'/language/en_us.lang.php'))
        {
            unlink('custom/modules/'.$this->module.'/language/en_us.lang.php');
        }
        if(file_exists('custom/modules/'.$this->add_module.'/language/en_us.lang.php'))
        {
            unlink('custom/modules/'.$this->add_module.'/language/en_us.lang.php');
        }

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @return array
     */
    public function getRequestData()
    {
        return array (
            'name' => $this->field_name,
            'view_module' => $this->module,
            'label' => 'LBL_' . strtoupper($this->field_name),
            'labelValue' => $this->field_name,
        );
    }

    /**
     * @group 51172
     * Check that the label custom fields of Employees module was saved also for Users module
     *
     * @return void
     */
    public function testSaveLabelForCustomFields()
    {
        $_REQUEST = $this->getRequestData();

        $mb = new ModuleBuilderController();
        $mb ->action_SaveLabel();

        $mod_strings = return_module_language($GLOBALS['current_language'], $this->add_module);

        //assert that array $mod_strings Users module contains current label
        $this->assertArrayHasKey( $_REQUEST['label'], $mod_strings);
    }
}
