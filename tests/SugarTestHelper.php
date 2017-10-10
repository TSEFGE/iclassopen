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


if(!defined('sugarEntry')) define('sugarEntry', true);

set_include_path(
    dirname(__FILE__) . PATH_SEPARATOR .
    dirname(__FILE__) . '/..' . PATH_SEPARATOR .
    get_include_path()
);

// constant to indicate that we are running tests
if (!defined('SUGAR_PHPUNIT_RUNNER'))
    define('SUGAR_PHPUNIT_RUNNER', true);

// initialize the various globals we use
global $sugar_config, $db, $fileName, $current_user, $locale, $current_language;

if ( !isset($_SERVER['HTTP_USER_AGENT']) )
    // we are probably running tests from the command line
    $_SERVER['HTTP_USER_AGENT'] = 'cli';

// move current working directory
chdir(dirname(__FILE__) . '/..');

require_once('include/entryPoint.php');

require_once('include/utils/layout_utils.php');

$GLOBALS['db'] = DBManagerFactory::getInstance();

$current_language = $sugar_config['default_language'];
// disable the SugarLogger
$sugar_config['logger']['level'] = 'fatal';

$GLOBALS['sugar_config']['default_permissions'] = array (
		'dir_mode' => 02770,
		'file_mode' => 0777,
		'chown' => '',
		'chgrp' => '',
	);

$GLOBALS['js_version_key'] = 'testrunner';

if ( !isset($_SERVER['SERVER_SOFTWARE']) )
    $_SERVER["SERVER_SOFTWARE"] = 'PHPUnit';

// helps silence the license checking when running unit tests.
$_SESSION['VALIDATION_EXPIRES_IN'] = 'valid';

$GLOBALS['startTime'] = microtime(true);

// clean out the cache directory
require_once('modules/Administration/QuickRepairAndRebuild.php');
$repair = new RepairAndClear();
$repair->module_list = array();
$repair->show_output = false;
$repair->clearJsLangFiles();
$repair->clearJsFiles();

// mark that we got by the admin wizard already
$focus = new Administration();
$focus->retrieveSettings();
$focus->saveSetting('system','adminwizard',1);

// include the other test tools
require_once 'SugarTestObjectUtilities.php';
require_once 'SugarTestProjectUtilities.php';
require_once 'SugarTestProjectTaskUtilities.php';
require_once 'SugarTestUserUtilities.php';
require_once 'SugarTestEmailAddressUtilities.php';
require_once 'SugarTestLangPackCreator.php';
require_once 'SugarTestThemeUtilities.php';
require_once 'SugarTestContactUtilities.php';
require_once 'SugarTestEmailUtilities.php';
require_once 'SugarTestCampaignUtilities.php';
require_once 'SugarTestLeadUtilities.php';
require_once 'SugarTestStudioUtilities.php';
require_once 'SugarTestMeetingUtilities.php';
require_once 'SugarTestCallUtilities.php';
require_once 'SugarTestAccountUtilities.php';
require_once 'SugarTestTrackerUtility.php';
require_once 'SugarTestImportUtilities.php';
require_once 'SugarTestMergeUtilities.php';
require_once 'SugarTestTaskUtilities.php';
require_once 'SugarTestOpportunityUtilities.php';
require_once 'SugarTestCurrencyUtilities.php';
require_once 'SugarTestRelationshipUtilities.php';
require_once 'SugarTestSugarEmailAddressUtilities.php';

$GLOBALS['db']->commit();

// define our testcase subclass
class Sugar_PHPUnit_Framework_TestCase extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = FALSE;

    protected $useOutputBuffering = true;

    protected function assertPreConditions()
    {
        if(isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("START TEST: {$this->getName(false)}");
        }
        SugarCache::instance()->flush();
    }

    protected function assertPostConditions() {
        if(!empty($_REQUEST)) {
            foreach(array_keys($_REQUEST) as $k) {
		        unset($_REQUEST[$k]);
		    }
        }

        if(!empty($_POST)) {
            foreach(array_keys($_POST) as $k) {
		        unset($_POST[$k]);
		    }
        }

        if(!empty($_GET)) {
            foreach(array_keys($_GET) as $k) {
		        unset($_GET[$k]);
		    }
        }
        if(isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("DONE TEST: {$this->getName(false)}");
        }
        // reset error handler in case somebody set it
        restore_error_handler();
    }

    public static function tearDownAfterClass()
    {
        unset($GLOBALS['disable_date_format']);
        unset($GLOBALS['saving_relationships']);
        unset($GLOBALS['updating_relationships']);
        $GLOBALS['timedate']->clearCache();
    }
}

// define output testcase subclass
class Sugar_PHPUnit_Framework_OutputTestCase extends PHPUnit_Extensions_OutputTestCase
{
    protected $backupGlobals = FALSE;

    protected $_notRegex;
    protected $_outputCheck;

    protected function assertPreConditions()
    {
        if(isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("START TEST: {$this->getName(false)}");
        }
        SugarCache::instance()->flush();
    }

    protected function assertPostConditions() {
        if(!empty($_REQUEST)) {
            foreach(array_keys($_REQUEST) as $k) {
		        unset($_REQUEST[$k]);
		    }
        }

        if(!empty($_POST)) {
            foreach(array_keys($_POST) as $k) {
		        unset($_POST[$k]);
		    }
        }

        if(!empty($_GET)) {
            foreach(array_keys($_GET) as $k) {
		        unset($_GET[$k]);
		    }
        }
        if(isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("DONE TEST: {$this->getName(false)}");
        }
    }

    protected function NotRegexCallback($output)
    {
        if(empty($this->_notRegex)) {
            return true;
        }
        $this->assertNotRegExp($this->_notRegex, $output);
        return true;
    }

    public function setOutputCheck($callback)
    {
        if (!is_callable($callback)) {
            throw new PHPUnit_Framework_Exception;
        }

        $this->_outputCheck = $callback;
    }

    protected function runTest()
    {
		$testResult = parent::runTest();
        if($this->_outputCheck) {
            $this->assertTrue(call_user_func($this->_outputCheck, $this->output));
        }
        return $testResult;
    }

    public function expectOutputNotRegex($expectedRegex)
    {
        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->_notRegex = $expectedRegex;
        }

        $this->setOutputCheck(array($this, "NotRegexCallback"));
    }

}

// define a mock logger interface; used for capturing logging messages emited
// the test suite
class SugarMockLogger
{
	private $_messages = array();

	public function __call($method, $message)
	{
		$this->messages[] = strtoupper($method) . ': ' . $message[0];
	}

	public function getLastMessage()
	{
		return end($this->messages);
	}

	public function getMessageCount()
	{
		return count($this->messages);
	}
}

require_once('ModuleInstall/ModuleInstaller.php');

/**
 * Own exception for SugarTestHelper class
 *
 * @author mgusev@sugarcrm.com
 */
class SugarTestHelperException extends PHPUnit_Framework_Exception
{

}

/**
 * Helper for initialization of global variables of SugarCRM
 *
 * @author mgusev@sugarcrm.com
 */
class SugarTestHelper
{
    /**
     * @var array array of registered vars. It allows helper to unregister them on tearDown
     */
    protected static $registeredVars = array();

    /**
     * @var array array of global vars. They are storing on init one time and restoring in global scope each tearDown
     */
    protected static $initVars = array(
        'GLOBALS' => array()
    );

    /**
     * @var array of system preference of SugarCRM as theme etc. They are storing on init one time and restoring each tearDown
     */
    protected static $systemVars = array();

    /**
     * @var array of modules which we should refresh on tearDown.
     */
    protected static $cleanModules = array();

    /**
     * @var bool is SugarTestHelper inited or not. Just to skip initialization on the second and others call of init method
     */
    protected static $isInited = false;

    /**
     * All methods are static because of it we disable constructor
     */
    private function __construct()
    {
    }

    /**
     * All methods are static because of it we disable clone
     */
    private function __clone()
    {
    }

    /**
     * Initialization of main variables of SugarCRM in global scope
     *
     * @static
     */
    public static function init()
    {
        if (self::$isInited == true)
        {
            return true;
        }

        // initialization & backup of sugar_config
        self::$initVars['GLOBALS']['sugar_config'] = null;
        if ($GLOBALS['sugar_config'])
        {
            self::$initVars['GLOBALS']['sugar_config'] = $GLOBALS['sugar_config'];
        }
        if (self::$initVars['GLOBALS']['sugar_config'] == false)
        {
            global $sugar_config;
            if (is_file('config.php'))
            {
                require_once('config.php');
            }
            if (is_file('config_override.php'))
            {
                require_once('config_override.php');
            }
            self::$initVars['GLOBALS']['sugar_config'] = $GLOBALS['sugar_config'];
        }

        // backup of current_language
        self::$initVars['GLOBALS']['current_language'] = 'en_us';
        if (isset($sugar_config['current_language']))
        {
            self::$initVars['GLOBALS']['current_language'] = $sugar_config['current_language'];
        }
        if (isset($GLOBALS['current_language']))
        {
            self::$initVars['GLOBALS']['current_language'] = $GLOBALS['current_language'];
        }
        $GLOBALS['current_language'] = self::$initVars['GLOBALS']['current_language'];

        // backup of reload_vardefs
        self::$initVars['GLOBALS']['reload_vardefs'] = null;
        if (isset($GLOBALS['reload_vardefs']))
        {
            self::$initVars['GLOBALS']['reload_vardefs'] = $GLOBALS['reload_vardefs'];
        }

        // backup of locale
        self::$initVars['GLOBALS']['locale'] = null;
        if (isset($GLOBALS['locale']))
        {
            self::$initVars['GLOBALS']['locale'] = $GLOBALS['locale'];
        }
        if (self::$initVars['GLOBALS']['locale'] == false)
        {
            self::$initVars['GLOBALS']['locale'] = new Localization();
        }

        // backup of service_object
        self::$initVars['GLOBALS']['service_object'] = null;
        if (isset($GLOBALS['service_object']))
        {
            self::$initVars['GLOBALS']['service_object'] = $GLOBALS['service_object'];
        }

        // backup of SugarThemeRegistry
        self::$systemVars['SugarThemeRegistry'] = SugarThemeRegistry::current();

        self::$isInited = true;
    }

    /**
     * Checking is there helper for variable or not
     *
     * @static
     * @param string $varName name of global variable of SugarCRM
     * @return bool is there helper for a variable or not
     * @throws SugarTestHelperException fired when there is no implementation of helper for a variable
     */
    protected static function checkHelper($varName)
    {
        if (method_exists(__CLASS__, 'setUp_' . $varName) == false)
        {
            throw new SugarTestHelperException('setUp for $' . $varName . ' is not implemented. ' . __CLASS__ . '::setUp_' . $varName);
        }
    }

    /**
     * Entry point for setup of global variable
     *
     * @static
     * @param string $varName name of global variable of SugarCRM
     * @param array $params some parameters for helper. For example for $mod_strings or $current_user
     * @return bool is variable setuped or not
     */
    public static function setUp($varName, $params = array())
    {
        self::init();
        self::checkHelper($varName);
        return call_user_func(__CLASS__ . '::setUp_' . $varName, $params);
    }

    /**
     * Clean up all registered variables and restore $initVars and $systemVars
     * @static
     * @return bool status of tearDown
     */
    public static function tearDown()
    {
        self::init();
        foreach(self::$registeredVars as $varName => $isCalled)
        {
            if ($isCalled)
            {
                unset(self::$registeredVars[$varName]);
                if (method_exists(__CLASS__, 'tearDown_' . $varName))
                {
                    call_user_func(__CLASS__ . '::tearDown_' . $varName, array());
                }
                elseif (isset($GLOBALS[$varName]))
                {
                    unset($GLOBALS[$varName]);
                }
            }
        }

        // Restoring of system variables
        foreach(self::$initVars as $scope => $vars)
        {
            foreach ($vars as $name => $value)
            {
                $GLOBALS[$scope][$name] = $value;
            }
        }

        // Restoring of theme
        SugarThemeRegistry::set(self::$systemVars['SugarThemeRegistry']->dirName);
        SugarCache::$isCacheReset = false;
        return true;
    }

    /**
     * Registration of $current_user in global scope
     *
     * @static
     * @param array $params parameters for SugarTestUserUtilities::createAnonymousUser method
     * @return bool is variable setuped or not
     */
    protected static function setUp_current_user(array $params)
    {
        self::$registeredVars['current_user'] = true;
        $GLOBALS['current_user'] = call_user_func_array('SugarTestUserUtilities::createAnonymousUser', $params);
        return true;
    }

    /**
     * Removal of $current_user from global scope
     *
     * @static
     * @return bool is variable removed or not
     */
    protected static function tearDown_current_user()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        return true;
    }

    /**
     * Registration of $beanList in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_beanList()
    {
        self::$registeredVars['beanList'] = true;
        global $beanList;
        require('include/modules.php');
        return true;
    }

    /**
     * Registration of $beanFiles in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_beanFiles()
    {
        self::$registeredVars['beanFiles'] = true;
        global $beanFiles;
        require('include/modules.php');
        return true;
    }

    /**
     * Registration of $moduleList in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_moduleList()
    {
        self::$registeredVars['moduleList'] = true;
        global $moduleList;
        require('include/modules.php');
        return true;
    }

    /**
     * Reinitialization of $moduleList in global scope because we can't unset that variable
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function tearDown_moduleList()
    {
        return self::setUp_moduleList();
    }

    /**
     * Registration of $modListHeader in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_modListHeader()
    {
        self::$registeredVars['modListHeader'] = true;
        if (isset($GLOBALS['current_user']) == false)
        {
            self::setUp_current_user(array(
                true,
                1
            ));
        }
        $GLOBALS['modListHeader'] = query_module_access_list($GLOBALS['current_user']);
        return true;
    }

    /**
     * Registration of $app_strings in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_app_strings()
    {
        self::$registeredVars['app_strings'] = true;
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        return true;
    }

    /**
     * Registration of $app_list_strings in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_app_list_strings()
    {
        self::$registeredVars['app_list_strings'] = true;
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        return true;
    }

    /**
     * Registration of $timedate in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_timedate()
    {
        self::$registeredVars['timedate'] = true;
        $GLOBALS['timedate'] = TimeDate::getInstance();
        return true;
    }

    /**
     * Removal of $timedate from global scope
     *
     * @static
     * @return bool is variable removed or not
     */
    protected static function tearDown_timedate()
    {
        $GLOBALS['timedate']->clearCache();
        return true;
    }

    /**
     * Registration of $mod_strings in global scope
     *
     * @static
     * @param array $params parameters for return_module_language function
     * @return bool is variable setuped or not
     */
    protected static function setUp_mod_strings(array $params)
    {
        self::$registeredVars['mod_strings'] = true;
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $params[0]);
        return true;
    }

    /**
     * Registration of $dictionary in global scope
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function setUp_dictionary()
    {
        self::setUp('beanFiles');
        self::setUp('beanList');
        self::$registeredVars['dictionary'] = true;

        global $dictionary;
        $dictionary = array();
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->silent = true;
        $moduleInstaller->rebuild_tabledictionary();
        require 'modules/TableDictionary.php';

        foreach($GLOBALS['beanList'] as $k => $v)
        {
            VardefManager::loadVardef($k, $v);
        }
        return true;
    }

    /**
     * Reinitialization of $dictionary in global scope because we can't unset that variable
     *
     * @static
     * @return bool is variable setuped or not
     */
    protected static function tearDown_dictionary()
    {
        return self::setUp_dictionary();
    }

    /**
     * Cleaning caches and refreshing vardefs
     *
     * @static
     * @param string $lhs_module left module from relation
     * @param string $rhs_module right module from relation
     * @return bool are caches refreshed or not
     */
    protected static function setUp_relation(array $params)
    {
        if (empty($params[0]) || empty($params[1]))
        {
            throw new SugarTestHelperException('setUp("relation") requires two parameters');
        }
        list($lhs_module, $rhs_module) = $params;
        self::$registeredVars['relation'] = true;
        self::$cleanModules[] = $lhs_module;

        LanguageManager::clearLanguageCache($lhs_module);
        if ($lhs_module != $rhs_module)
        {
            self::$cleanModules[] = $rhs_module;
            LanguageManager::clearLanguageCache($rhs_module);
        }

        self::setUp('dictionary');

        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        VardefManager::refreshVardefs($lhs_module, BeanFactory::getObjectName($lhs_module));
        if ($lhs_module != $rhs_module)
        {
            VardefManager::refreshVardefs($rhs_module, BeanFactory::getObjectName($rhs_module));
        }
        SugarRelationshipFactory::rebuildCache();

        return true;
    }

    /**
     * Doing the same things like setUp but for initialized list of modules
     *
     * @static
     * @return bool are caches refreshed or not
     */
    protected static function tearDown_relation()
    {
        SugarRelationshipFactory::deleteCache();

        $modules = array_unique(self::$cleanModules);
        foreach ($modules as $module)
        {
            LanguageManager::clearLanguageCache($module);
        }

        self::tearDown('dictionary');

        VardefManager::$linkFields = array();
        VardefManager::clearVardef();
        foreach($modules as $module)
        {
            VardefManager::refreshVardefs($module, BeanFactory::getBeanName($module));
        }
        SugarRelationshipFactory::rebuildCache();

        self::$cleanModules = array();
        return true;
    }
}
