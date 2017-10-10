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


require_once('modules/DynamicFields/templates/Fields/TemplateInt.php');

/**
 * Bug #56694
 * Integer fields preset to max of 100 after upgrade
 *
 * @author mgusev@sugarcrm.com
 * @ticked 56694
 */
class Bug56694v2Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateInt
     */
    protected $templateInt = null;

    /**
     * @var javascript56694
     */
    protected $javascript = null;

    /**
     * @var SugarBean
     */
    protected $bean = null;

    public function setUp()
    {
        $this->templateInt = new TemplateInt();
        $this->templateInt->importable = "true";
        $this->templateInt->label = "LBL_TEST";
        $this->templateInt->name = "bug_c";
        $this->templateInt->no_default = 1;
        $this->templateInt->reportable = "1";
        $this->templateInt->supports_unified_search = true;
        $this->templateInt->vname = $this->templateInt->label;

        $this->bean = new SugarBean();

        $this->javascript = new javascript56694();
        $this->javascript->setSugarBean($this->bean);
    }

    /**
     * Test asserts that after addField call validator is not added
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithoutValidator()
    {
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertEmpty($this->javascript->getData(), 'Validator is added');
    }

    /**
     * Test asserts that after addField call validator is added with empty values
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithoutRealValidator()
    {
        $this->templateInt->min = 5;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->bean->field_name_map[$this->templateInt->name]['validation']['min'] = null;
        $this->bean->field_name_map[$this->templateInt->name]['validation']['max'] = null;
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame(array(false, false), $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added only for min value, max value should be false
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMinOnly()
    {
        $this->templateInt->min = 5;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame(array($this->templateInt->min,false), $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added only to max value, min value should be false
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMaxOnly()
    {
        $this->templateInt->max = 5;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');

        $actual = $this->javascript->getData();
        $this->assertSame(array(false, $this->templateInt->max), $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator is added with both values
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithMaxMin()
    {
        $this->templateInt->min = 5;
        $this->templateInt->max = 6;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');
        $actual = $this->javascript->getData();
        $this->assertEquals(array($this->templateInt->min, $this->templateInt->max), $actual, 'Values are incorrect');
    }

    /**
     * Test asserts that after addField call validator added to both values and has min value, because of min value more than max
     *
     * @group 56694
     */
    public function testAddFieldForFieldWithInvertedMaxMin()
    {
        $this->templateInt->min = 6;
        $this->templateInt->max = 5;
        $this->bean->field_name_map[$this->templateInt->name] = $this->templateInt->get_field_def();
        $this->javascript->addField($this->templateInt->name, $this->templateInt->required);

        $this->assertNotEmpty($this->javascript->getData(), 'Validator is not added');
        $actual = $this->javascript->getData();
        $this->assertSame(array($this->templateInt->min, $this->templateInt->min), $actual, 'Min value is incorrect');
    }
}

/**
 * Mock of javascript class
 */
class javascript56694 extends javascript
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function addFieldRange($field, $type, $displayName, $required, $prefix = '', $min, $max)
    {
        $this->data = array($min, $max);
    }
}
