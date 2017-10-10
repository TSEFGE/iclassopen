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
 * Bug49219Test.php
 * @author Collin Lee
 *
 * This test will attempt to assert two things:
 * 1) That upgrade for Meetings quickcreatedefs.php correctly remove footerTpl and headerTpl metadata attributes from
 * custom quickcreatedefs.php files (since we removed them from code base)
 * 2) That the SubpanelQuickCreate changes done for this bug can correctly pick up metadata footerTpl and headerTpl
 * attributes
 */
require_once 'include/dir_inc.php';
require_once 'include/EditView/SubpanelQuickCreate.php';

class Bug49219Test extends Sugar_PHPUnit_Framework_TestCase  {
	
var $merge;

function setUp() {
    SugarTestHelper::setUp('beanFiles');
    SugarTestHelper::setUp('beanList');
    SugarTestHelper::setUp('current_user');
    SugarTestHelper::setUp('app_strings');
    SugarTestHelper::setUp('app_list_strings');
    SugarTestMergeUtilities::setupFiles(array('Meetings'), array('quickcreatedefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
}


function tearDown() {
    SugarTestMergeUtilities::teardownFiles();
    SugarTestHelper::tearDown();
}


/**
 * testUpgradeMeetingsQuickCreate641
 * @outputBuffering enabled
 * This test asserts that the footerTpl and headerTpl form attributes are removed from quickcreatedefs.php when
 * upgrading to 641
 */
function testUpgradeMeetingsQuickCreate641() {
   require('custom/modules/Meetings/metadata/quickcreatedefs.php');
   $this->assertArrayHasKey('headerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'Unit test setup failed');
   $this->assertArrayHasKey('footerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'Unit test setup failed');
   require_once 'modules/UpgradeWizard/SugarMerge/QuickCreateMerge.php';
   $this->merge = new QuickCreateMerge();
   $this->merge->merge('Meetings', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/640/modules/Meetings/metadata/quickcreatedefs.php','modules/Meetings/metadata/quickcreatedefs.php','custom/modules/Meetings/metadata/quickcreatedefs.php');
   require('custom/modules/Meetings/metadata/quickcreatedefs.php');
   $this->assertArrayNotHasKey('headerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'SugarMerge code does not remove headerTpl from quickcreatedefs.php');
   $this->assertArrayNotHasKey('footerTpl', $viewdefs['Meetings']['QuickCreate']['templateMeta']['form'], 'SugarMerge code does not remove footerTpl from quickcreatedefs.php');
}


/**
 * testSubpanelQuickCreate
 * @outputBuffering enabled
 * This test asserts that we can pick up the footerTpl and headerTpl attributes in the quickcreatedefs.php files
 */
function testSubpanelQuickCreate()
{
    $quickCreate = new SubpanelQuickCreate('Meetings', 'QuickCreate', true);
    $this->assertEquals('modules/Meetings/tpls/header.tpl', $quickCreate->ev->defs['templateMeta']['form']['headerTpl'], 'SubpanelQuickCreate fails to pick up headerTpl attribute');
    $this->assertEquals('modules/Meetings/tpls/footer.tpl', $quickCreate->ev->defs['templateMeta']['form']['footerTpl'], 'SubpanelQuickCreate fails to pick up footerTpl attribute');
    require_once 'modules/UpgradeWizard/SugarMerge/QuickCreateMerge.php';
    $this->merge = new QuickCreateMerge();
    $this->merge->merge('Meetings', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/640/modules/Meetings/metadata/quickcreatedefs.php','modules/Meetings/metadata/quickcreatedefs.php','custom/modules/Meetings/metadata/quickcreatedefs.php');
    $quickCreate = new SubpanelQuickCreate('Meetings', 'QuickCreate', true);
    $this->assertEquals('include/EditView/header.tpl', $quickCreate->ev->defs['templateMeta']['form']['headerTpl'], 'SubpanelQuickCreate fails to pick up default headerTpl attribute');
    $this->assertEquals('include/EditView/footer.tpl', $quickCreate->ev->defs['templateMeta']['form']['footerTpl'], 'SubpanelQuickCreate fails to pick up default footerTpl attribute');

}

}
