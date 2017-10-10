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


require_once('modules/ModuleBuilder/parsers/parser.label.php');

/**
 * Bug #49772
 *
 * [IBM RTC 3001] XSS - Administration, Rename Modules, Singular Label
 * @ticket 49772
 * @author arymarchik@sugarcrm.com
 */
class Bug49772Test extends Sugar_PHPUnit_Framework_TestCase
{


    private $_old_label = '';
    private $_test_label = 'LBL_ACCOUNT_NAME';
    private $_test_module = 'Contacts';
    private $_lang = 'en_us';


    /**
     * Generating new label with HTML tags
     * @group 43069
     */
    public function testLabelSaving()
    {
        $mod_strings = return_module_language($this->_lang, $this->_test_module);
        $this->_old_label = $mod_strings[$this->_test_label];
        $pref = '<img alt="<script>" src="www.test.com/img.png" ="alert(7001)" width="1" height="1"/>';
        $prepared_pref = to_html(strip_tags(from_html($pref)));
        $new_label = $prepared_pref . ' ' . $this->_old_label;

        // save the new label to the language file
        ParserLabel::addLabels($this->_lang, array($this->_test_label => $new_label), $this->_test_module);

        // read the language file to get the new value
        include("custom/modules/{$this->_test_module}/language/{$this->_lang}.lang.php");

        $this->assertEquals($new_label, $mod_strings[$this->_test_label]);
        $this->assertNotEquals($pref . ' ' . $this->_old_label, $mod_strings[$this->_test_label]);

    }

    public function tearDown()
    {
        ParserLabel::addLabels($this->_lang, array($this->_test_label=>$this->_old_label), $this->_test_module);
    }
}
