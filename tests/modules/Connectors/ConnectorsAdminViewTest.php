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
 * ConnectorsAdminViewTest
 *
 * @author Collin Lee
 */
class ConnectorsAdminViewTest extends Sugar_PHPUnit_Framework_OutputTestCase
{

public static function setUpBeforeClass()
{
    global $mod_strings, $app_strings, $theme;
    $theme = SugarTestThemeUtilities::createAnonymousTheme();
    $mod_strings = return_module_language($GLOBALS['current_language'], 'Connectors');
    $app_strings = return_application_language($GLOBALS['current_language']);
}

public static function tearDownAfterClass()
{
    global $mod_strings, $app_strings, $theme;
    SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
    unset($theme);
    unset($mod_strings);
    unset($app_strings);
}

public function testMapConnectorFields()
{
    require_once('modules/Connectors/views/view.modifymapping.php');
    $view = new ViewModifyMapping(null, null);
    $view->ss = new Sugar_Smarty();
    $view->display();
    $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

}

public function testEnableConnectors()
{
    require_once('modules/Connectors/views/view.modifydisplay.php');
    $view = new ViewModifyDisplay(null, null);
    $view->ss = new Sugar_Smarty();
    $view->display();
    $this->expectOutputRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

}

public function testConnectorProperties()
{
    require_once('modules/Connectors/views/view.modifyproperties.php');
    $view = new ViewModifyProperties(null, null);
    $view->ss = new Sugar_Smarty();
    $view->display();
    $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

}

public function testConnectorSearchProperties()
{
    require_once('modules/Connectors/views/view.modifysearch.php');
    $view = new ViewModifySearch(null, null);
    $view->ss = new Sugar_Smarty();
    $view->display();
    $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');
}

}


