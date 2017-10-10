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
class Bug56694Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateInt
     */
    protected $templateInt = null;

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
    }

    /**
     * Test asserts that min & max properties more important than ext1 & ext2
     *
     * @group 53554
     */
    public function testMinMaxWinExt()
    {
        $this->templateInt->ext1 = 3;
        $this->templateInt->ext2 = 4;
        $vardef = $this->templateInt->get_field_def();

        $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
        $this->assertEquals(3, $vardef['validation']['min'], 'Ext won');
        $this->assertEquals(4, $vardef['validation']['max'], 'Ext won');

        $this->templateInt->min = 1;
        $this->templateInt->max = 2;
        $vardef = $this->templateInt->get_field_def();

        $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
        $this->assertEquals(1, $vardef['validation']['min'], 'Min won');
        $this->assertEquals(2, $vardef['validation']['max'], 'Max won');
    }

    /**
     * Method returns data for tests
     * min value
     * max value
     * should validator be present
     * should min value be present
     * should max value be present
     *
     * @return array
     */
    public function getMaxMin()
    {
        return array(
            array(null, null, false, false, false),
            array(0, null, true, true, false),
            array(0, 0, true, true, true),
            array('0', '0', true, true, true),
            array(null, 0, true, false, true),
            array(1, 5, true, true, true, true),
            array('1', '5', true, true, true, true),
            array('a', 'b', false, false, false),
            array('a', 5, true, false, true),
            array(5, 'a', true, true, false)
        );
    }

    /**
     * Test checks min & max range for validator for int field
     *
     * @param mixed $min value
     * @param mixed $max max
     * @param bool $isValidation is validation required
     * @param bool $isMin is min value present
     * @param bool $isMax is max value present
     *
     * @dataProvider getMaxMin
     * @group 56694
     */
    public function testGetFieldDefByMinMax($min, $max, $isValidation, $isMin, $isMax)
    {
        $this->templateInt->min = $min;
        $this->templateInt->max = $max;
        $vardef = $this->templateInt->get_field_def();

        if ($isValidation == false)
        {
            $this->assertArrayNotHasKey('validation', $vardef, 'Validation is required');
        }
        else
        {
            $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
            if ($isMin == true)
            {
                $this->assertEquals($min, $vardef['validation']['min'], 'Min value is incorrect');
            }
            else
            {
                $this->assertEquals(false, $vardef['validation']['min'], 'Min value is present');
            }
            if ($isMax == true)
            {
                $this->assertEquals($max, $vardef['validation']['max'], 'Max value is incorrect');
            }
            else
            {
                $this->assertEquals(false, $vardef['validation']['max'], 'Max value is present');
            }
        }
    }

    /**
     * Test checks min & max range for validator for int field
     *
     * @param mixed $min value
     * @param mixed $max max
     * @param bool $isValidation is validation required
     * @param bool $isMin is min value present
     * @param bool $isMax is max value present
     *
     * @dataProvider getMaxMin
     * @group 56694
     */
    public function testGetFieldDefByExt($min, $max, $isValidation, $isMin, $isMax)
    {
        $this->templateInt->ext1 = $min;
        $this->templateInt->ext2 = $max;
        $vardef = $this->templateInt->get_field_def();

        if ($isValidation == false)
        {
            $this->assertArrayNotHasKey('validation', $vardef, 'Validation is required');
        }
        else
        {
            $this->assertArrayHasKey('validation', $vardef, 'Validation is not required');
            if ($isMin == true)
            {
                $this->assertEquals($min, $vardef['validation']['min'], 'Min value is incorrect');
            }
            else
            {
                $this->assertEquals(false, $vardef['validation']['min'], 'Min value is present');
            }
            if ($isMax == true)
            {
                $this->assertEquals($max, $vardef['validation']['max'], 'Max value is incorrect');
            }
            else
            {
                $this->assertEquals(false, $vardef['validation']['max'], 'Max value is present');
            }
        }
    }
}
