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


require_once('modules/DynamicFields/DynamicField.php');

class Bug61859_p2Test extends Sugar_PHPUnit_Framework_TestCase
{

    static private $module = 'Leads';
    private $object = 'Lead';
    private $relatedModule = 'Contacts';

    static private $field;
    static private $dynamicField;

    /**
     * @group 61859
     */
    public function testAddField()
    {
        $this->addField('testfieldbug61859');
        SugarTestHelper::setUp('dictionary');

        $idName = $GLOBALS['dictionary'][$this->object]['fields'][self::$field->name]['id_name'];

        $this->assertArrayHasKey(self::$field->name, $GLOBALS['dictionary'][$this->object]['fields']);
        $this->assertArrayHasKey($idName, $GLOBALS['dictionary'][$this->object]['fields']);

        return $idName;
    }

    /**
     * @depends testAddField
     * @group 61859
     */
    public function testUpdateField($idName)
    {

        self::$field->label_value = 'UpdatedLabel';
        self::$field->save(self::$dynamicField);

        SugarTestHelper::setUp('dictionary');

        $this->assertEquals($idName, self::$field->ext3);

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

        $field->ext2 = $this->relatedModule;
        $field->label_value = $name;
        $field->save(self::$dynamicField);

        self::$field = $field;
    }

    static public function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array(self::$module));
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user');

        self::$dynamicField = new DynamicField(self::$module);
        self::$dynamicField->setup(BeanFactory::getBean(self::$module));

    }

    static public function tearDownAfterClass()
    {
        self::$field->delete(self::$dynamicField);

        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

}
