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


require_once 'include/SugarTheme/SugarTheme.php';

class Bug48571Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $globalDefaultTheme;
    var $unavailableThemes;
    var $customThemeDef;

    public function setUp()
    {
        if(isset($GLOBALS['sugar_config']['default_theme']))
        {
            $this->globalDefaultTheme = $GLOBALS['sugar_config']['default_theme'];
            unset($GLOBALS['sugar_config']['default_theme']);
        }

        if(isset($GLOBALS['sugar_config']['disabled_themes']))
        {
            $this->unavailableThemes = $GLOBALS['sugar_config']['disabled_themes'];
            unset($GLOBALS['sugar_config']['disabled_themes']);
        }

        if(file_exists('custom/themes/default/themedef.php'))
        {
            $this->customThemeDef = file_get_contents('custom/themes/default/themedef.php');
            unlink('custom/themes/default/themedef.php');
        }

        //Blowout all existing cache/themes that may not have been cleaned up
        if(file_exists('cache/themes'))
        {
            rmdir_recursive('cache/themes');
        }

    }

    public function tearDown()
    {
        if(!empty($this->globalDefaultTheme))
        {
            $GLOBALS['sugar_config']['default_theme'] = $this->globalDefaultTheme;
            unset($this->globalDefaultTheme);
        }

        if(!empty($this->unavailableThemes))
        {
            $GLOBALS['sugar_config']['disabled_themes'] = $this->unavailableThemes;
            unset($this->unavailableThemes);
        }

        if(!empty($this->customThemeDef))
        {
            file_put_contents('custom/themes/default/themedef.php', $this->customThemeDef);
        }
    }

    public function testBuildRegistry()
    {
        $this->markTestSkipped('Skip for community edition builds for now as this was to test a ce->pro upgrade');
        
        SugarThemeRegistry::buildRegistry();
        $themeObject = SugarThemeRegistry::current();

    }

}

?>