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
 * Bug49939Test.php
 * @author Collin Lee
 *
 * This is a simple test to assert that we can correctly remove the XSS attack strings set in the help field
 * via Studio.
 *
 */

class Bug49939Test extends Sugar_PHPUnit_Framework_TestCase {

/**
 * xssFields
 * This is the provider function for testPopulateFromPostWithXSSHelpField
 *
 */
public function xssFields() {
   return array(
       array(htmlentities('<script>alert(50);</script>'), ''),
       array(htmlentities('This is some help text'), 'This is some help text'),
       array(htmlentities('???'), '???'),
       array(htmlentities('Foo Foo<script type="text/javascript">alert(50);</script>Bar Bar'), 'Foo FooBar Bar'),
       array(htmlentities('I am trying to <b>Bold</b> this!'), 'I am trying to &lt;b&gt;Bold&lt;/b&gt; this!'),
       array(htmlentities(''), ''),
   );
}


/**
 * testPopulateFromPostWithXSSHelpField
 * @dataProvider xssFields
 * @param string $badXSS The bad XSS script
 * @param string $expectedValue The expected output
 */
public function testPopulateFromPostWithXSSHelpField($badXSS, $expectedValue)
{
    $tf = new Bug49939TemplateFieldMock();
    $_REQUEST['help'] = $badXSS;
    $tf->vardef_map = array('help'=>'help');
    $tf->populateFromPost();
    $this->assertEquals($expectedValue, $tf->help, 'Unable to remove XSS from help field');
}


}


require_once('modules/DynamicFields/templates/Fields/TemplateField.php');
class Bug49939TemplateFieldMock extends TemplateField {

public function applyVardefRules()
{
    //no-opt function called at the end of populateFromPost method in TemplateField
}

}

?>