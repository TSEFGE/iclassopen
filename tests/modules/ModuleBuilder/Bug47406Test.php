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



require_once("modules/ModuleBuilder/MB/MBVardefs.php");
if (file_exists("custom/include/Smarty/plugins/function.sugar_currency_format.php"))
{
	require_once("custom/include/Smarty/plugins/function.sugar_currency_format.php");
}
else
{
	require_once("include/Smarty/plugins/function.sugar_currency_format.php");
}
require_once("include/Smarty/Smarty.class.php");

class Bug47406Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $mbvardef;
    private $smarty;

    public function setUp()
    {
        $this->smarty = new Smarty();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->mbvardef);
        unset($this->smarty);
        unset($GLOBALS['current_user']);
    }

    public function providerMBVardefAddFieldVardef()
    {
        return array(
            array(array('name' => 'field_name', 'default' => ''), null),
            array(array('name' => 'field_name', 'default' => 0), 0),
            array(array('name' => 'field_name', 'default' => '0'), '0'),
            array(array('name' => 'field_name', 'default' => '0.00'), '0.00'),
            array(array('name' => 'field_name', 'default' => ' '), ' '),
        );
    }

    /**
     * @dataProvider providerMBVardefAddFieldVardef
     */
    public function testMBVardefAddFieldVardef($vardef, $expected)
    {
        unset($this->mbvardef);

        $this->mbvardef = $this->getMockBuilder('MBVardefs')
            ->disableOriginalConstructor(array('load'))
            ->setMethods(array('load'))
            ->setConstructorArgs(array('name', 'path', 'key'))
            ->getMock();

        $this->mbvardef->addFieldVardef($vardef);

        if ( null === $expected )
        {
            $this->assertArrayNotHasKey('default', $this->mbvardef->vardef['fields'][$vardef['name']]);
        }
        else
        {
            $this->assertEquals( $expected, $this->mbvardef->vardef['fields'][$vardef['name']]['default']);
        }
    }

    public function providerSugarCurrencyFormat()
    {
        return array(
            array(array('var' => ''), '', false),
            array(array('var' => ' '), '0.00', true),
            array(array('var' => 0), '0.00', true),
            array(array('var' => 0.00), '0.00', true),
            array(array('var' => '0.00'), '0.00', true),
        );
    }

    /**
     * @dataProvider providerSugarCurrencyFormat
     */
    public function testSugarCurrencyFormat($params, $expected, $as_regexp = false)
    {
        $return = smarty_function_sugar_currency_format($params, $this->smarty);
        if ( $as_regexp )
        {
            $this->assertRegExp('/'.$expected.'$/', $return);
        }
        else
        {
            $this->assertEquals( $expected, $return);
        }
    }
}
?>
