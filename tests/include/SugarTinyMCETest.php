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


/* Original Bug: 27655
 * 
 * This test was expanded to hit both major paths in this file.
 * 
 */

require_once 'include/SugarTinyMCE.php';

class SugarTinyMCETest extends Sugar_PHPUnit_Framework_TestCase{
	
	static $customConfigFile = 'custom/include/tinyButtonConfig.php';
	static $customDefaultConfigFile = 'custom/include/tinyMCEDefaultConfig.php';
    static $directory = 'custom/include';
    static $MCE;
	
	/*
	 * Setup: Backup old custom files and create new ones for the test
	 */
	public static function setUpBeforeClass(){

        if (!file_exists(self::$directory)) {
            sugar_mkdir(self::$directory, 0777, true);
        }

		if(file_exists(self::$customConfigFile)){
			rename(self::$customConfigFile, self::$customConfigFile . ".bak");
		}
		if(file_exists(self::$customDefaultConfigFile)){
			rename(self::$customDefaultConfigFile, self::$customDefaultConfigFile . ".bak");
		}
				
		file_put_contents(self::$customConfigFile,
			"<?php
			\$buttonConfigs = array('default' => array('buttonConfig' =>'testcase',
									'buttonConfig2' => 'cut,copy,paste,pastetext,pasteword,selectall,separator,search,replace,separator,bullist,numlist,separator,outdent,
	                     					indent,separator,ltr,rtl,separator,undo,redo,separator, link,unlink,anchor,image,separator,sub,sup,separator,charmap,
	                     					visualaid', 
	                    			'buttonConfig3' => 'tablecontrols,separator,advhr,hr,removeformat,separator,insertdate,inserttime,separator,preview'),
									'badkey1' => 'bad data1');
			?>");
		
		file_put_contents(self::$customDefaultConfigFile, 
			"<?php
			\$defaultConfig = array('extended_valid_elements' => 'upload[testlength|ratio|initialtest|mintestsize|threads|maxchunksize|maxchunkcount],download[testlength|initialtest|mintestsize|threads|maximagesize]',
																 'badkey2' => 'bad data2');
			?>"
		);
		$tinySugar = new SugarTinyMCE();
		self::$MCE = $tinySugar->getInstance();
				
	}
	
	
	
	/*
	 * Teardown: remove new custom files and restore the previous ones
	 */
	public static function tearDownAfterClass(){
		unlink(self::$customConfigFile);
		unlink(self::$customDefaultConfigFile);
		
		if(file_exists(self::$customConfigFile . ".bak")){
			rename(self::$customConfigFile . ".bak", self::$customConfigFile);
		}
		if(file_exists(self::$customDefaultConfigFile . ".bak")){
			rename(self::$customDefaultConfigFile . ".bak", self::$customDefaultConfigFile);
		}
	}
	
	public function testCheckValidCustomButtonOverrdide(){
		$this->assertContains("testcase", self::$MCE, "TinyMCE custom button not found.");
	}
	
	public function testCheckInvalidCustomButtonOverrdide(){
		$pos = strpos("badkey1", self::$MCE);
		if($pos === false){
			$pos = 0;
		}
		$this->assertEquals(0, $pos, "Invalid custom button found. Stripping code failed.");
	}

	public function testCheckValidDefaultOverrdide(){
		$this->assertContains("download", self::$MCE, "TinyMCE custom config not found.");
	}
	
	public function testCheckInvalidDefaultOverrdide(){
		$pos = strpos("badkey2", self::$MCE);
		if($pos === false){
			$pos = 0;
		}
		$this->assertEquals(0, $pos, "Invalid custom config found. Stripping code failed.");
	}
}
