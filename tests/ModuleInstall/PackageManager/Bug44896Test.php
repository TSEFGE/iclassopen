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




require_once 'ModuleInstall/PackageManager/PackageManager.php';


class Bug44896Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (!is_dir(dirname(Bug44896PackageManger::$location))) {
            sugar_mkdir(dirname(Bug44896PackageManger::$location));
        }
        if (!is_dir(Bug44896PackageManger::$location))
        {
            sugar_mkdir(Bug44896PackageManger::$location);
        }

        $manage = new Bug44896PackageManger();
        $manage->createTempModule();
    }

    public function tearDown()
    {
        if (is_dir(Bug44896PackageManger::$location)) {
            rmdir_recursive(dirname(Bug44896PackageManger::$location));
        }
    }

    public function testCheckedArrayKey()
    {
        $package = new PackageManager();
        $returnJson = $package->getPackagesInStaging('module');
        foreach ($returnJson as $module) {
            $this->assertArrayHasKey('unFile', $module, 'Key "unFile" is missing in return array');
        }
    }

}

class Bug44896PackageManger
{
	static $manifest_location = "upload/upgrades/module/Bug44896-manifest.php";
    static $zip_location = "upload/upgrades/module/Bug44896.zip";
    static $location = "upload/upgrades/module/";

	public function __construct()
    {
	   $this->manifest_content = <<<EOQ
<?php
\$manifest = array (
         'acceptable_sugar_versions' =>
          array (
            '6.4.0'
          ),
          'acceptable_sugar_flavors' =>
          array(
            'ENT'
          ),
          'readme'=>'',
          'key'=>'tf1',
          'author' => '',
          'description' => '',
          'icon' => '',
          'is_uninstallable' => false,
          'name' => 'Bug44896',
          'published_date' => '2010-10-20 22:10:01',
          'type' => 'module',
          'version' => '1287612601',
          'remove_tables' => 'prompt',
          );
\$installdefs = array (
  'id' => 'asdfqq',
  'copy' =>
  array (
     0 => array (
      'from' => '<basepath>/Extension/modules/Cases/Ext/Vardefs/dummy_extension2.php',
      'to' => 'custom/Extension/modules/Cases/Ext/Vardefs/dummy_extension2.php',
    ),
  ),
);

EOQ;
	}

	public function createTempModule()
    {
	   if (!is_file(self::$manifest_location))
       {
            file_put_contents(self::$manifest_location, $this->manifest_content);
            zip_files_list(self::$zip_location, array(self::$manifest_location));
       }
	}
}