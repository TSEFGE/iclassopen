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


require_once 'modules/UpgradeWizard/uw_utils.php';

class Bug41058Test extends Sugar_PHPUnit_Framework_OutputTestCase {

    var $user;
    var $backupConfig;
    var $backupSystemLocaleNameFormat;
    var $loc;

    public function setUp() {
        global $sugar_config;

        $this->backupConfig = $sugar_config;

        $this->user = SugarTestUserUtilities::createAnonymousUser(true);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
	    $GLOBALS['current_user']->save();

        $this->loc = new Localization();
        if ($this->loc->invalidLocaleNameFormatUpgrade()) {
            rename($this->loc->invalidNameFormatUpgradeFilename, $this->loc->invalidNameFormatUpgradeFilename.'.backup');
        }
    }

    public function tearDown() {
        global $sugar_config, $sugar_version, $mod_strings;

        if (file_exists($this->loc->invalidNameFormatUpgradeFilename)) {
            unlink($this->loc->invalidNameFormatUpgradeFilename);
        }
        if (file_exists($this->loc->invalidNameFormatUpgradeFilename.'.backup')) {
            rename($this->loc->invalidNameFormatUpgradeFilename.'.backup', $this->loc->invalidNameFormatUpgradeFilename);
        }
        unset($this->loc);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);

        $sugar_config = $this->backupConfig;
        if(!rebuildConfigFile($sugar_config, $sugar_version)) {
            logThis('*** ERROR: could not write config.php!');
            $errors[] = $mod_strings['ERR_UW_CONFIG_WRITE'];
        }

        unset($this->backupSystemLocaleNameFormat);
        unset($sugar_config);
        unset($sugar_version);
        unset($mod_strings);
        unset($app_strings);
        unset($app_list_strings);
        unset($locale);
        unset($_REQUEST);
    }


    /**
     * Tests that Localization::isAllowedNameFormat returns true for valid name formats
     * @param $name_format valid name format from dataProvider
     * @dataProvider goodLocaleNameFormatProvider
     */
    public function testCheckReturnsTrueForValidNameFormats($name_format) {
        $this->assertTrue($this->loc->isAllowedNameFormat($name_format));
    }

    /**
     * Tests that Localization::isAllowedNameFormat returns false for invalid name formats
     * @param $name_format invalid name format from dataProvider
     * @dataProvider badLocaleNameFormatProvider
     */
    public function testCheckReturnsFalseForInvalidNameFormats($name_format) {
        $this->assertFalse($this->loc->isAllowedNameFormat($name_format));
    }

    /**
     * Tests that good Locale Name Format strings from user preferences get added to the config
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends testCheckReturnsTrueForValidNameFormats
     */
    public function testUserPreferenceForLocaleNameFormatUpgrade($name_format) {
        global $sugar_config;

        $this->user->setPreference('default_locale_name_format', $name_format);
        $this->user->savePreferencesToDB();
        $this->user->save();

        $ulnf = $this->user->getPreference('default_locale_name_format');
        $this->assertSame($name_format, $ulnf);

        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        upgradeUserPreferences();
        $this->assertArrayHasKey($name_format, $sugar_config['name_formats']);
    }

    /**
     * Tests that bad Locale Name Format strings from user preferences do not get added to the config
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends testCheckReturnsFalseForInvalidNameFormats
     */
    public function testBadUserPreferenceForLocaleNameFormatUpgrade($name_format) {
        global $sugar_config;

        $this->user->setPreference('default_locale_name_format', $name_format);
        $this->user->savePreferencesToDB();
        $this->user->save();

        $ulnf = $this->user->getPreference('default_locale_name_format');
        $this->assertSame($name_format, $ulnf);

        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        upgradeUserPreferences();
        $this->assertArrayNotHasKey($name_format, $sugar_config['name_formats']);
        $coreDefaults = $this->loc->getLocaleConfigDefaults();
        $this->assertSame($coreDefaults['default_locale_name_format'], $this->user->getPreference('default_locale_name_format'));
    }


    /**
     * Tests that good Locale Name Format strings from global preference get added to the config
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends testCheckReturnsTrueForValidNameFormats
     */
    public function testGlobalPreferenceForLocaleNameFormatUpgrade($name_format) {
        global $sugar_config, $sugar_version;

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        $sugar_config['default_locale_name_format'] = $name_format;
        if(!rebuildConfigFile($sugar_config, $sugar_version)) {
            $errors[] = $mod_strings['ERR_UW_CONFIG_WRITE'];
            $this->fail("Could not rebuild config file, please check your installation.");
        }
        upgradeUserPreferences();
        require ('config.php');
        $this->assertSame($name_format, $sugar_config['default_locale_name_format']);
        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
    }

    /**
     * Tests that bad Locale Name Format strings from global preference do not get added to the config
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends testCheckReturnsFalseForInvalidNameFormats
     */
    public function testInvalidGlobalPreferenceForLocaleNameFormatUpgrade($name_format) {
        global $sugar_config, $sugar_version;

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        $sugar_config['default_locale_name_format'] = $name_format;
        if(!rebuildConfigFile($sugar_config, $sugar_version)) {
            $errors[] = $mod_strings['ERR_UW_CONFIG_WRITE'];
            $this->fail("Could not rebuild config file, please check your installation.");
        }
        upgradeUserPreferences();
        $this->assertNotSame($name_format, $sugar_config['default_locale_name_format']);
        require ('config.php');
        $coreDefaults = $this->loc->getLocaleConfigDefaults();
        $this->assertSame($coreDefaults['default_locale_name_format'], $sugar_config['default_locale_name_format']);
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);
    }

    /**
     * Tests that UI presents a message on the locale settings page when there was an invalid name format during an upgrade
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends testCheckReturnsFalseForInvalidNameFormats
     */
    public function testMessageIsShownWhenInvalidLocaleNameFormatIsFoundInUpgrade($name_format) {
        global $sugar_config, $locale, $app_strings, $app_list_strings;

        require('modules/Administration/language/en_us.lang.php');

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);

        $this->expectOutputRegex('/'.$mod_strings['ERR_INVALID_LOCALE_NAME_FORMAT_UPGRADE'].'/');
        require('modules/Administration/Locale.php');
    }

    /**
     * Tests that UI does not present a message on the locale settings page when there wasn't an invalid name format during an upgrade
     * @param $name_format valid name format from data provider
     * @dataProvider goodLocaleNameFormatProvider
     * @depends testCheckReturnsTrueForValidNameFormats
     */
    public function testMessageIsNotShownWhenNoInvalidLocaleNameFormatIsFoundInUpgrade($name_format) {
        global $sugar_config, $locale, $app_strings, $app_list_strings;

        require('modules/Administration/language/en_us.lang.php');

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);

        $this->expectOutputNotRegex('/'.$mod_strings['ERR_INVALID_LOCALE_NAME_FORMAT_UPGRADE'].'/');
        require('modules/Administration/Locale.php');
        
    }

    /**
     * Test that file gets removed after a save from Locale page
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     * @depends testCheckReturnsFalseForInvalidNameFormats
     */
    public function testFileGetsRemovedAfterLocaleSave($name_format) {
        global $sugar_config, $locale, $app_strings, $app_list_strings;
        require('modules/Administration/language/en_us.lang.php');

        $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        $sugar_config['default_locale_name_format'] = $name_format;
        upgradeUserPreferences();
        $this->assertFileExists($this->loc->invalidNameFormatUpgradeFilename);
        try {
            $_REQUEST['process'] = 'true';
            require('modules/Administration/Locale.php');
        } catch (Exception $e) {
            $this->assertFileNotExists($this->loc->invalidNameFormatUpgradeFilename);
        }
    }

    /**
     * Test that bad formats are not added to the list for the dropdown
     * @param $name_format invalid name format from data provider
     * @dataProvider badLocaleNameFormatProvider
     */
    public function testBadFormatsInConfigAreNotIncludedInDropdown($name_format) {
        $localeDefaults = $this->loc->getLocaleConfigDefaults();
        $formats = $localeDefaults['name_formats'];

        $formats[$name_format] = $name_format;

        $list = $this->loc->getUsableLocaleNameOptions($formats);
        $this->assertArrayHasKey($name_format, $formats);
        $this->assertArrayNotHasKey($name_format, $list);
    }

    /**
     * Data provider of allowed name formats
     * @return array of allowed name format strings
     */
    public function goodLocaleNameFormatProvider() {
        $goodFormatsArray = array(
            array('`l` `f` `s`'),
            array('l_f_s'),
            array('*-s-f-l-*'),
            array('{[`~!@#$%^&*()_-+=;:\'"/?\\|.>s, f, l    <]}'),
        );

        return $goodFormatsArray;
    }

    /**
     * Data provider of disallowed name formats
     * @return array of disallowed name format strings
     */
    public function badLocaleNameFormatProvider() {
        $badFormatsArray = array(
            array('`l` `f` `s`: `t`'),
            array('alpha-bits'),
            array('*-s-f-l-*-bad_name_format'),
            array('bad{[`~!@#$%^&*()_-+=;:\'"/?\\|.>s, f, l    <]}'),
        );

        return $badFormatsArray;
    }

}
?>
