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


require_once('modules/ModuleBuilder/parsers/StandardField.php');

/**
 * Bug #46869
 * @ticket 46869
 */
class Bug46869Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    private $customVardefPath;

    public function setUp()
    {
        $this->customVardefPath = 'custom' . DIRECTORY_SEPARATOR .
                                  'Extension' . DIRECTORY_SEPARATOR .
                                  'modules' . DIRECTORY_SEPARATOR .
                                  'Cases' . DIRECTORY_SEPARATOR .
                                  'Ext' . DIRECTORY_SEPARATOR .
                                  'Vardefs' . DIRECTORY_SEPARATOR .
                                  'sugarfield_resolution46869.php';
        $dirname = dirname($this->customVardefPath);

        if (file_exists($dirname) === false)
        {
            mkdir($dirname, 0777, true);
        }

        $code = <<<PHP
<?php
\$dictionary['Case']['fields']['resolution46869']['required']=true;
PHP;

        file_put_contents($this->customVardefPath, $code);

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
    }

    public function tearDown()
    {
        unlink($this->customVardefPath);

        SugarTestHelper::tearDown();
    }

    public function testLoadingCustomVardef()
    {
        $df = new StandardFieldBug46869Test('Cases') ;
        $df->base_path = dirname($this->customVardefPath);
        $customDef = $df->loadCustomDefBug46869Test('resolution46869');

        $this->assertArrayHasKey('required', $customDef, 'Custom definition of Case::resolution46869 does not have required property.');
    }

}

class StandardFieldBug46869Test extends StandardField
{
    public function loadCustomDefBug46869Test($field)
    {
        $this->loadCustomDef($field);

        return $this->custom_def;
    }
}