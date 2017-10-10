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


require_once('modules/ModuleBuilder/views/view.labels.php');

/**
 * Bug #39749
 * Quick Create in Studio
 * @ticket 39749
 */
class Bug39749Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function editModules()
    {
        return array(
            array('module' => 'KBDocuments', 'get_quick_create' => '0'),
            array('module' => 'ProjectTask', 'get_quick_create' => '0'),
            array('module' => 'Campaigns', 'get_quick_create' => '0'),
            array('module' => 'ProductTemplates', 'get_quick_create' => '0'),
            array('module' => 'Accounts', 'get_quick_create' => '1'),
            array('module' => 'Quotes', 'get_quick_create' => '0')
        );
    }

    /**
     * @group 39749
     * @dataProvider editModules
     */
    public function testGetVariableMap($module, $get_quick_create)
    {
        $vl = new ViewLabels();

        $varMap = $vl->getVariableMap($module);

        $this->assertTrue((isset($varMap['quickcreate']) == $get_quick_create));
    }
}
?>