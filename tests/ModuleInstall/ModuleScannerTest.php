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

require_once 'ModuleInstall/ModuleScanner.php';

class ModuleScannerTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $fileLoc;

	public function setUp()
	{
        $this->fileLoc = "cache/moduleScannerTemp.php";
	}

	public function tearDown()
	{
		if (is_file($this->fileLoc))
			unlink($this->fileLoc);
        // testScanCopy
        if (is_file('files.md5.copy')) {
            rename('files.md5.copy', 'files.md5');
        }
        if (is_dir(sugar_cached("ModuleScannerTest"))) {
            rmdir_recursive(sugar_cached("ModuleScannerTest"));
        }
	}

	public function phpSamples()
	{
	    return array(
	        array("<?php echo blah;", true),
	        array("<? echo blah;", true),
	        array("blah <? echo blah;", true),
	        array("blah <?xml echo blah;", true),
	        array("<?xml version=\"1.0\"></xml>", false),
	        array("<?xml \n echo blah;", true),
	        array("<?xml version=\"1.0\"><? blah ?></xml>", true),
	        array("<?xml version=\"1.0\"><?php blah ?></xml>", true),
	        );
	}

	/**
	 * @dataProvider phpSamples
	 */
	public function testPHPFile($content, $is_php)
	{
        $ms = new MockModuleScanner();
	    $this->assertEquals($is_php, $ms->isPHPFile($content), "Bad PHP file result");
	}

	public function testFileTemplatePass()
    {

    	$fileModContents = <<<EOQ
<?PHP
require_once('include/SugarObjects/templates/file/File.php');

class testFile_sugar extends File {
	function fileT_testFiles_sugar(){
		parent::File();
		\$this->file = new File();
		\$file = "file";
	}
}
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertTrue(empty($errors));
    }

	public function testFileFunctionFail()
    {

    	$fileModContents = <<<EOQ
<?PHP
require_once('include/SugarObjects/templates/file/File.php');

class testFile_sugar extends File {
	function fileT_testFiles_sugar(){
		parent::File();
		\$this->file = new File();
		\$file = file('test.php');

	}
}
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertTrue(!empty($errors));
    }

	public function testCallUserFunctionFail()
    {

    	$fileModContents = <<<EOQ
<?PHP
	call_user_func("sugar_file_put_contents", "test2.php", "test");
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertTrue(!empty($errors));
    }


	public function testCallMethodObjectOperatorFail()
    {

    	$fileModContents = <<<EOQ
<?PHP
    //doesnt matter what the class name is, what matters is use of the banned method, setlevel
	\$GlobalLoggerClass->setLevel();
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertNotEmpty($errors, 'There should have been an error caught for use of "->setLevel()');
    }

	public function testCallMethodDoubleColonFail()
    {

    	$fileModContents = <<<EOQ
<?PHP
    //doesnt matter what the class name is, what matters is use of the banned method, setlevel
	\$GlobalLoggerClass::setLevel();
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new ModuleScanner();
		$errors = $ms->scanFile($this->fileLoc);
		$this->assertNotEmpty($errors, 'There should have been an error caught for use of "::setLevel()');
    }

    /**
     * Bug 56717
     *
     * When ModuleScanner is enabled, handle bars templates are invalidating published
     * package installation.
     *
     * @group bug56717
     */
    public function testBug56717ValidExtsAllowed() {
        // Allowed file names
        $allowed = array(
            'php' => 'test.php',
            'htm' => 'test.htm',
            'xml' => 'test.xml',
            'hbs' => 'test.hbs',
            'config' => 'custom/config.php',
        );

        // Disallowed file names
        $notAllowed = array(
            'docx' => 'test.docx',
            'docx(2)' => '../sugarcrm.xml/../sugarcrm/test.docx',
            'java' => 'test.java',
            'phtm' => 'test.phtm',
            'md5' => 'files.md5',
            'md5(2)' => '../sugarcrm/files.md5',

        );

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($allowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertTrue($valid, "The $ext extension should be valid on $file but the ModuleScanner is saying it is not");
        }

        // Test not valid
        foreach ($notAllowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertFalse($valid, "The $ext extension should not be valid on $file but the ModuleScanner is saying it is");
        }
    }

    public function testConfigChecks()
    {
            $isconfig = array(
            'config.php',
            'config_override.php',
            'custom/../config_override.php',
            'custom/.././config.php',
            );

        // Disallowed file names
        $notconfig = array(
            'custom/config.php',
            'custom/modules/config.php',
            'cache/config_override.php',
            'modules/Module/config.php'
        );

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($isconfig as $file) {
            $valid = $ms->isConfigFile($file);
            $this->assertTrue($valid, "$file should be recognized as config file");
        }

        // Test not valid
        foreach ($notconfig as $ext => $file) {
            $valid = $ms->isConfigFile($file);
            $this->assertFalse($valid, "$file should not be recognized as config file");
        }
    }

    /**
     * @group bug58072
     */
	public function testLockConfig()
    {

    	$fileModContents = <<<EOQ
<?PHP
	\$GLOBALS['sugar_config']['moduleInstaller']['test'] = true;
    	\$manifest = array();
    	\$installdefs = array();
?>
EOQ;
		file_put_contents($this->fileLoc, $fileModContents);
		$ms = new MockModuleScanner();
		$ms->config['test'] = false;
		$ms->lockConfig();
		MSLoadManifest($this->fileLoc);
		$errors = $ms->checkConfig($this->fileLoc);
		$this->assertTrue(!empty($errors), "Not detected config change");
		$this->assertFalse($ms->config['test'], "config was changed");
    }

    /**
     * @dataProvider normalizePathProvider
     * @param string $path
     * @param string $expected
     */
    public function testNormalize($path, $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($expected, $ms->normalizePath($path));
    }

    public function normalizePathProvider()
    {
        return array(
            array('./foo', 'foo'),
            array('foo//bar///baz/', 'foo/bar/baz'),
            array('./foo/.//./bar/foo', 'foo/bar/foo'),
            array('foo/../bar', false),
            array('../bar/./', false),
            array('./', ''),
            array('.', ''),
            array('', ''),
            array('/', ''),
        );
    }

    /**
     * @dataProvider scanCopyProvider
     * @param string $from
     * @param string $to
     * @param bool $ok is it supposed to be ok?
     */
    public function testScanCopy($file, $from, $to, $ok)
    {
        $this->markTestIncomplete('This test requires files.md5.');
        if (is_file('files.md5')) {
            copy('files.md5', 'files.md5.copy');
        }
        copy(__DIR__."/../upgrade/files.md5", "files.md5");
        // ensure target file exists
        $from = sugar_cached("ModuleScannerTest/$from");
        $file = sugar_cached("ModuleScannerTest/$file");
        mkdir_recursive(dirname($file));
        sugar_touch($file);

        $ms = new ModuleScanner();
        $ms->scanCopy($from, $to);
        if ($ok) {
            $this->assertEmpty($ms->getIssues(), "Issue found where it should not be");
        } else {
            $this->assertNotEmpty($ms->getIssues(), "Issue not detected");
        }
        // check with dir
        $ms->scanCopy(dirname($from), $to);
        if ($ok) {
            $this->assertEmpty($ms->getIssues(), "Issue found where it should not be");
        } else {
            $this->assertNotEmpty($ms->getIssues(), "Issue not detected");
        }
    }

    public function scanCopyProvider()
    {
        return array(
          array(
            'copy/modules/Audit/Audit.php',
            'copy/modules/Audit/Audit.php',
            "modules/Audit",
            false
          ),
          array(
            'copy/modules/Audit/Audit.php',
            'copy/modules/Audit/Audit.php',
            "modules/Audit/Audit.php",
            false
          ),
        array(
            'copy/modules/Audit/Audit.php',
            'copy',
            ".",
            false
          ),
        array(
            'copy/modules/Audit/SomeFile.php',
            'copy',
            ".",
            true
          ),
        );
    }

}

class MockModuleScanner extends  ModuleScanner
{
    public $config;
    public function isPHPFile($contents) {
        return parent::isPHPFile($contents);
    }
}

