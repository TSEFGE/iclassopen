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



require_once("modules/ModuleBuilder/parsers/StandardField.php");
require_once("modules/DynamicFields/FieldCases.php");


/**
 *  Bug #51427: Setting a "name" field to calculated removes it from Global Search
 *
 *  StandardField class didn't have functionality for checking `unified_search`
 *  option. `unified_search` was always `false` for non-custom fields.
 */

class Bug51427Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $old_dictionary;
    private $old_bean_list;
    private $bean_name;
    private $test_field;
    private $test_standart_field;

    public function setUp()
    {
        global $dictionary, $bean_list;

        $this->old_dictionary=$dictionary;
        $this->old_bean_list=$bean_list;
        $this->test_standart_field = new TestStandardField();
        $this->test_standart_field->module='Accounts';
        loadBean($this->test_standart_field->module);
        $this->test_field=get_widget('varchar');
        $this->test_field->name='name';
        $this->bean_name = get_valid_bean_name($this->test_standart_field->module);
    }

    public function tearDown()
    {
        global $dictionary, $bean_list;

        $dictionary=$this->old_dictionary;
        $bean_list=$this->old_bean_list;
        VardefManager::clearVardef('Accounts', 'Account');
        VardefManager::refreshVardefs('Accounts', 'Account');
    }

    /**
     * Test different combinations of the options
     *
     * @dataProvider providerUnifiedSearchOptions
     */
    public function testIfUnifiedSearchEnabledByDefault(array $opt_common, array $opt_field, $assert)
    {
        $this->test_field->unified_search=false;
        $this->setOptions($opt_common, $opt_field);
        $this->test_standart_field->addFieldObject($this->test_field);
        $this->assertEquals($assert,(boolean)$this->test_field->unified_search,
                "\r\n"
                .'Assertion error: field->unified_search should be `'.($assert ? 'true' : 'false').'` with the following vardef options:'

                ."\r\nvardef[<module>][unified_search_default_enabled] "
                    .($opt_common['unified_search_default_enabled']===-1 ? 'is undefined' : ($opt_common['unified_search_default_enabled'] ? '= true' : '= false'))

                ."\r\nvardef[<module>][unified_search] "
                    .($opt_common['unified_search']===-1 ? 'is undefined' : ($opt_common['unified_search'] ? '= true' : '= false'))

                ."\r\nvardef[<module>][fields][<field_name>][unified_search] "
                    .($opt_field['unified_search']===-1 ? 'is undefined' : ($opt_field['unified_search'] ? '= true' : '= false'))
                ."\r\n"
        );
    }

    public function providerUnifiedSearchOptions()
    {
        return array(
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>1,
                    'unified_search'=>1,
                ),
                'options_field'=>array(
                    'unified_search'=>1,
                ),
                'assert'=>true,
            ),
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>0,
                    'unified_search'=>1,
                ),
                'options_field'=>array(
                    'unified_search'=>1,
                ),
                'assert'=>false,
            ),
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>0,
                    'unified_search'=>0,
                ),
                'options_field'=>array(
                    'unified_search'=>1,
                ),
                'assert'=>false,
            ),
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>0,
                    'unified_search'=>0,
                ),
                'options_field'=>array(
                    'unified_search'=>0,
                ),
                'assert'=>false,
            ),
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>0,
                    'unified_search'=>1,
                ),
                'options_field'=>array(
                    'unified_search'=>0,
                ),
                'assert'=>false,
            ),
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>0,
                    'unified_search'=>0,
                ),
                'options_field'=>array(
                    'unified_search'=>1,
                ),
                'assert'=>false,
            ),
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>1,
                    'unified_search'=>1,
                ),
                'options_field'=>array(
                     'unified_search'=>-1,
                ),
                'assert'=>true,
            ),
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>1,
                    'unified_search'=>-1,
                ),
                'options_field'=>array(
                    'unified_search'=>1,
                ),
                'assert'=>false,
            ),
            array(
                'options_common'=>array(
                    'unified_search_default_enabled'=>-1,
                    'unified_search'=>1,
                ),
                'options_field'=>array(
                    'unified_search'=>1,
                ),
                'assert'=>false,
            ),
        );
    }

    private function setOptions(array $opt_common, array $opt_field)
    {
        global $dictionary;
        $dictionary=$this->old_dictionary;
        if(!empty($opt_common))
        {
            foreach($opt_common as $k=>$v)
            {
                if($v===-1)
                {
                    unset($dictionary[$this->bean_name][$k]);
                }
                else
                {
                    $dictionary[$this->bean_name][$k]=$v;
                }
            }
        }
        if(!empty($opt_field))
        {
            foreach($opt_field as $k=>$v)
            {
                if($v===-1)
                {
                    unset($dictionary[$this->bean_name]['fields'][$this->test_field->name][$k]);
                }
                else
                {
                    $dictionary[$this->bean_name]['fields'][$this->test_field->name][$k]=$v;
                }
            }
        }
    }
}


/*
 * inherits StandardField
 * some methods aren't used in the test
 */
class TestStandardField extends StandardField
{
    function writeVardefExtension()
    {
    }

    function loadCustomDef()
    {
        $this->custom_def=array();
    }
}