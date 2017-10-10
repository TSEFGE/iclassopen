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


/*
* This test will confirm that JSGroupings are concatenated using the Extensions Framework
*
*/

require_once('modules/Administration/QuickRepairAndRebuild.php');



class Bug54472Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $beforeArray;
    private $removeJSG_Dir = false;


    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));

        //lets retrieve the original jsgroupings file to populate the js_grouping array to compare against later on
        include('jssource/JSGroupings.php');

        //store the grouping value before any changes
        $this->beforeArray = $js_groupings;

        //create supporting files in seperate function
        $this->createSupportingFiles();

        //run repair so the extension files are created and updated
				$rac = new RepairAndClear();
				$rac->repairAndClearAll(array('rebuildExtensions'), array(), false, false);

    }


    /*
     * This function creates supporting directory structure and files to carry out the test
     */
    private function createSupportingFiles(){

        //create the js group directory in the proper extension location if needed
        if(!file_exists("custom/Extension/application/Ext/JSGroupings/")){
            mkdir_recursive("custom/Extension/application/Ext/JSGroupings/", true);
            $this->removeJSG_Dir = true;
        }

        //create the first grouping file and define the first group
        if( $fh = @fopen("custom/Extension/application/Ext/JSGroupings/Jgroup0.php", 'w+') )
        {
        $jsgrpStr = '<?php
$js_groupings [\'testEntrySite\'] = array("include/javascript/calendar.js" => "include/javascript/sugar_test_grp1.js", "include/javascript/cookie.js" => "include/javascript/sugar_test_grp1.js");
';
                        fputs( $fh, $jsgrpStr);
                        fclose( $fh );
        }


        //now create a second custom grouping file
        if( $fhAcc = @fopen("custom/Extension/application/Ext/JSGroupings/Jgroup1.php", 'w+') )
        {
        $jsgrpACCStr = '<?php
$js_groupings [\'testEntryMod\'] = array("include/javascript/calendar.js" => "include/javascript/sugar_testAcc_grp1.js", "include/javascript/quickCompose.js" => "include/javascript/sugar_testAcc_grp1.js");
';
                        fputs( $fhAcc, $jsgrpACCStr);
                        fclose( $fhAcc );
        }


    }

    public function tearDown()
    {

        //remove the 2 grouping files and their directories
        if(file_exists('custom/Extension/application/Ext/JSGroupings/Jgroup0.php')){
            unlink('custom/Extension/application/Ext/JSGroupings/Jgroup0.php');
        }
        if(file_exists('custom/Extension/application/Ext/JSGroupings/Jgroup1.php')){
            unlink('custom/Extension/application/Ext/JSGroupings/Jgroup1.php');
        }
        if($this->removeJSG_Dir && file_exists("custom/Extension/application/Ext/JSGroupings")) {
            @rmdir("custom/Extension/application/Ext/JSGroupings");
        }

        //unset before array
        unset($this->beforeArray);

        //run repair so the extension files are reset back to original state
        $trac = new RepairAndClear();
        $trac->repairAndClearAll(array('rebuildExtensions'), array(), false, false);
        SugarTestHelper::tearDown();
    }

    public function testGetJSGroupingCustomEntries() {

        //include jsgroupings file again, this time it should pick up the 2 new groups from the extensions.
        include('jssource/JSGroupings.php');

        //assert that the array count has increased, this confirms it is grabbing the files correctly
        $this->assertGreaterThan(count($this->beforeArray),count($js_groupings), 'JSGrouping array was not concatenated correctly, the number of elements should have increased');

        //Check for the individual entries to confirm they are being concatenated and not overwritten
        $this->assertArrayHasKey('testEntrySite', $js_groupings,'JSGrouping array was not concatenated correctly, site entry is missing');
        $this->assertArrayHasKey('testEntryMod', $js_groupings,'JSGrouping array was not concatenated correctly, module entry is missing');

    }

}
