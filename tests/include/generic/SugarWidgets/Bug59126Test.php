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


require_once('include/generic/LayoutManager.php');
require_once('include/generic/SugarWidgets/SugarWidgetFieldrelate.php');

class Bug59126Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $contact;

    public function testLastName()
    {
        $layoutDef = array(
            'table' => $this->contact->table_name,
            'input_name0' => array(),
            'name' => 'contacts',
            'rname' => 'last_name',
        );
        $html = $this->getSugarWidgetFieldRelate()->displayInput($layoutDef);
        $regExpPattern =$this->getAssertRegExp($this->contact->id, "{$this->contact->last_name}");
        $this->assertRegExp($regExpPattern, $html);
    }

    public function testFirstLastName()
    {
        $layoutDef = array(
            'table' => $this->contact->table_name,
            'input_name0' => array(),
            'name' => 'contacts',
            'rname' => 'last_name',
            'db_concat_fields' => array('first_name', 'last_name'),
        );
        $html = $this->getSugarWidgetFieldRelate()->displayInput($layoutDef);
        $regExpPattern = $this->getAssertRegExp(
            $this->contact->id,
            "{$this->contact->first_name}\s+{$this->contact->last_name}"
        );
        $this->assertRegExp($regExpPattern, $html);
    }

    public function testCustomField()
    {
        $layoutDef = array(
            'table' => $this->contact->table_name,
            'module' => $this->contact->module_name,
            'custom_module' => 'Contacts',
            'input_name0' => array(),
            'name' => 'customField',
            'rname' => 'name',
        );
        $html = $this->getSugarWidgetFieldRelate()->displayInput($layoutDef);
        $regExpPattern = $this->getAssertRegExp(
            $this->contact->id,
            "{$this->contact->first_name}\s+{$this->contact->last_name}"
        );
        $this->assertRegExp($regExpPattern, $html);
    }

    private function  getAssertRegExp($value, $text)
    {
        $pattern = '/\<option.+value="' . $value . '".*\>' . $text . '\<\/option\>/i';
        return $pattern;
    }

    private function getSugarWidgetFieldRelate()
    {
        $LayoutManager = new LayoutManager();
        $temp = (object)array('db' => $GLOBALS['db'], 'report_def_str' => '');
        $LayoutManager->setAttributePtr('reporter', $temp);
        $Widget = new SugarWidgetFieldRelate($LayoutManager);
        return $Widget;
    }

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->contact = SugarTestContactUtilities::createContact();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

}
