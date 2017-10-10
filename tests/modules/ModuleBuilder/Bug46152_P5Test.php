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


require_once ('modules/DynamicFields/FieldCases.php');

class Bug46152_P5Test extends Sugar_PHPUnit_Framework_TestCase
{

    static private $moduleBuilder;

    static private $packageName = 'pnb46152';
    static private $moduleName = 'modb46152';

    static private $fieldName = 'fldb46152';

    private $field;
    private $relatedModule = 'Opportunities';
    private $fieldLabelName = 'Opportunities';

    /**
     * Testing creation a field in Module Builder. Also test MBModule::getField
     * 
     * @group 46152
     */
    public function testMBAddField()
    {
        $this->createField();

        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);

        $modStrings = $module->getModStrings($GLOBALS['current_language']);

        $field = $module->getField(self::$fieldName);

        $this->assertNotNull($field);

        $fieldId = $module->getField($field->id_name);

        $this->assertNotNull($fieldId);

        $this->assertNotEmpty($field->vname);
        $this->assertNotEmpty($fieldId->vname);

        $this->assertArrayHasKey($field->vname, $modStrings);
        $this->assertArrayHasKey($fieldId->vname, $modStrings);
        $this->assertEquals($this->relatedModule, $field->ext2);

        return $field->id_name;
    }

    /**
     * Testing deleting a field in Module Builder. Also test MBModule::getField
     * 
     * @group 46152
     * @depends testMBAddField
     */
    public function testMBDeleteField($idFieldName)
    {
        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $field = $module->getField(self::$fieldName);
        $fieldId = $module->getField($field->id_name);

        $field->delete($module);

        $modStrings = $module->getModStrings($GLOBALS['current_language']);

        $this->assertNull($module->getField(self::$fieldName));
        $this->assertNull($module->getField($idFieldName));
        $this->assertArrayNotHasKey($fieldId->vname, $modStrings);

    }

    private function createField()
    {
        $this->fieldLabelName = 'LBL_' . strtoupper(self::$fieldName);
        $this->field = get_widget('relate');
        $this->field->audited = 0;
        $this->field->view = 'edit';
        $this->field->name = self::$fieldName;
        $this->field->vname = $this->fieldLabelName;
        $this->field->label = $this->fieldLabelName;
        $this->field->ext2 = $this->relatedModule;
        $this->field->label_value = self::$fieldName;


        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $this->field->save($module);
        $module->mbvardefs->save();
        $module->setLabel($GLOBALS['current_language'], $this->fieldLabelName, self::$fieldName);
        $module->save();

    }

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
        SugarTestHelper::setUp('current_user');
        self::createPackage();
        self::createModule();

        parent::setUpBeforeClass();
    }

    private static function createPackage()
    {
        self::$moduleBuilder = new ModuleBuilder();
        $package = self::$moduleBuilder->getPackage(self::$packageName);
        $_REQUEST['key'] = self::$packageName;
        $_REQUEST['description'] = '';
        $_REQUEST['author'] = '';
        $_REQUEST['readme'] = '';

        $package->populateFromPost();
        $package->loadModules();
        self::$moduleBuilder->save();
    }

    public static function createModule()
    {
        $module = self::$moduleBuilder->getPackage(self::$packageName)->getModule(self::$moduleName);
        $_REQUEST ['team_security'] = 1;
        $_REQUEST ['has_tab'] = 1;
        $_REQUEST ['type'] = 'company';
        $_REQUEST ['label'] = self::$moduleName;
        $module->populateFromPost();
        self::$moduleBuilder->save();
    }

    public static function tearDownAfterClass()
    {
        self::$moduleBuilder->getPackage(self::$packageName)->delete ();

        $_REQUEST = array();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

}
