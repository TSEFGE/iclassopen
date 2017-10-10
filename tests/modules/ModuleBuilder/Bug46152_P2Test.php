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



require_once 'modules/DynamicFields/FieldCases.php';
require_once 'modules/DynamicFields/DynamicField.php';

class Bug46152_P2Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $fields = array();
    private $dynamicField = null;

    /**
     * Test is id fields have unique label
     *
     * Create 2 equal fields. Test is id fields have unique label. For correct import we must have unique label of id fields.
     * 
     * @group 46152
     */
    public function testDoubleLabel()
    {

        $idName1 = $GLOBALS['dictionary']['Note']['fields'][$this->fields[0]->name]['id_name'];
        $idName2 = $GLOBALS['dictionary']['Note']['fields'][$this->fields[1]->name]['id_name'];
        $vName1 = $GLOBALS['dictionary']['Note']['fields'][$idName1]['vname'];
        $vName2 = $GLOBALS['dictionary']['Note']['fields'][$idName2]['vname'];

        $this->assertArrayHasKey($vName1, $GLOBALS['mod_strings']);
        $this->assertArrayHasKey($vName2, $GLOBALS['mod_strings']);

        $this->assertNotEquals($GLOBALS['mod_strings'][$vName1], $GLOBALS['mod_strings'][$vName2]);
    }

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Notes'));
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user');

        $this->dynamicField = new DynamicField('Notes');
        $this->dynamicField->setup(BeanFactory::getBean('Notes'));

        $this->addField('testfield1_b46152');
        $this->addField('testfield2_b46152');

        SugarTestHelper::setUp('mod_strings', array('Notes'));

    }

    private function addField($name)
    {
        $labelName = 'LBL_' . strtoupper($name);
        $field = get_widget('relate');
        $field->audited = 0;
        $field->view = 'edit';
        $field->name = $name;
        $field->vname = $labelName;
        $field->label = $labelName;

        $field->ext2 = 'Opportunities';
        $field->label_value = $name;
        $field->save($this->dynamicField);
        $this->fields[] = $field;

    }

    public function tearDown()
    {
        $this->deleteFields();

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    private function deleteFields()
    {
        foreach ($this->fields AS $field) {
            $field->delete($this->dynamicField);
        }
    }

}
