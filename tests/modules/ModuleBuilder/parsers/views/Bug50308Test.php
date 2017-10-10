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


require_once ('modules/ModuleBuilder/parsers/views/PopupMetaDataParser.php');

/*
 * This test checks to see if custom elements can be defined in a popupdef and be handled by PopupMetaDataParser.php
 * @ticket 50308
 */
class Bug50308Test extends Sugar_PHPUnit_Framework_TestCase {

    var $customFilePath = 'custom/modules/Users/metadata/popupdefs.php';
    var $customFileDir = 'custom/modules/Users/metadata';
    var $originalPopupMeta = array();
    var $newPopupMeta = array('moduleMain'=>array('one','two'), 'varName'=>array('one','two') , 'orderBy'=>array('one','two'), 'whereClauses'=>array('one','two'), 'searchInputs'=>array('one','two'), 'create'=>array('one','two'));

    public function setUp()
    {
        //back up users popup if it exists
        if(is_file($this->customFilePath)){
            include($this->customFilePath);
            $this->originalPopupMeta = $popupMeta;
            $this->newPopupMeta = $popupMeta;
        }else{
            //lets create the directory if it does not exist
            if(!is_dir($this->customFileDir)){
                sugar_mkdir($this->customFileDir);
            }
        }

        //define and add the new elements
        $this->newPopupMeta['addToReserve'] = array('whereStatement', 'templateMeta');
        $this->newPopupMeta['whereStatement'] = 'select money from yourWallet where deposit = "myPocket"';
        $this->newPopupMeta['templateMeta'] = array('one','two');
        $this->newPopupMeta['disappear'] = 'this element was not defined and should be processed';

    }

    public function tearDown() {

        //remove custom file
        unlink($this->customFilePath);
        //recreate custom file using old data if it was collected
        if(!empty($this->originalPopupMeta)){
            $meta = "<?php\n \$popupMeta = array (\n";
            foreach( $this->originalPopupMeta as $k=>$v){
    			$meta .= "    '$k' => ". var_export_helper ($v) . ",\n";
            }
            $meta .=");\n";

            sugar_file_put_contents($this->customFilePath, $meta);
        }

        unset($this->customFilePath);
        unset($this->customFileDir);
        unset($this->originalPopupMeta);
        unset($this->newPopupMeta);

    }

    /*
     * This method writes out the custom popupdef file to custom users directory, then runs the save function on the  popup metadata parser
     * the tests assert that the custom elements are preserved by the parser
     */
    public function testUsingCustomPopUpElements() {
        
	//declare the vars global and then include the modules file to make sure they are available during testing
        global $moduleList, $beanList, $beanFiles;
        include('include/modules.php');

        if (empty($GLOBALS['app_list_strings'])){
            $language = $GLOBALS['current_language'];
            $GLOBALS['app_list_strings'] = return_app_list_strings_language($language);
        }
        //write out to file and assert that the file was written, or we shouldn't continue
            $meta = "<?php\n \$popupMeta = array (\n";
            foreach( $this->newPopupMeta as $k=>$v){
    			$meta .= "    '$k' => ". var_export_helper ($v) . ",\n";
            }
            $meta .=");\n";

        $writeResult = sugar_file_put_contents($this->customFilePath, $meta);
        $this->assertGreaterThan(0,$writeResult, 'there was an error writing custom popup meta to file using this path: '.$this->customFilePath);

        //create new instance of popupmetadata parser
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->getParser(MB_POPUPLIST, 'Users');

        //run save to write out the file using the new array elements.
        $parser->handleSave(false);

        //assert the file still exists
        $this->assertTrue(is_file($this->customFilePath),' PopupMetaDataParser::handleSave() could not write out the file as expected.');

        //include the file again to get the new popup meta array
        include($this->customFilePath);
        $popupKeys = array_keys($popupMeta);
        //assert that one of the new elements is there
        $this->assertContains('whereStatement', $popupKeys,'an element that was defined in addToReserve was not processed and save within PopupMetaDataParser::handleSave()');

        //assert that the element that was written but not defined in 'addToReserve' is no longer there
        $this->assertNotContains('disappear', $popupKeys, 'an element that was added but NOT defined in addToReserve was incorrectly processed and saved within PopupMetaDataParser::handleSave().');
    }
}
