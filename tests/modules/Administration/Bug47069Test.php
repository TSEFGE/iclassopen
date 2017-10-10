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


require_once('modules/Notes/Note.php');

class Bug47069Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        parent::setUp();
        
        $GLOBALS['action'] = 'async';
        $GLOBALS['module'] = 'Administration';
        $GLOBALS['app_strings'] = return_application_language('en_us');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $GLOBALS['mod_strings'] = return_module_language('en_us','Administration');
        $GLOBALS['db'] = DBManagerFactory::getInstance();
        $GLOBALS['current_user'] = new User();
        $GLOBALS['current_user']->retrieve('1');
    }
    
    public function tearDown() {
        unset($GLOBALS['module']);
        unset($GLOBALS['action']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['current_user']);
        unset($_REQUEST);
        $GLOBALS['db']->query("DELETE FROM notes WHERE id IN ('".$this->note1->id."','".$this->note2->id."')");
        // Just in case there is a custom table here
        if($GLOBALS['db']->tableExists('notes_cstm'))
        {
            $GLOBALS['db']->query("DELETE FROM notes_cstm WHERE id_c IN ('".$this->note1->id."','".$this->note2->id."')");
        }
        parent::tearDown();
    }

    public function testRepairXSSNotDuplicating()
    {
        $this->note1 = new Note();
        $this->note1->id = create_guid();
        $this->note1->new_with_id = true;
        $this->note1->name = "[Bug47069] Not deleted Note";
        $this->note1->description = "This note shouldn't be deleted.";
        $this->note1->save();

        $this->note2 = new Note();
        $this->note2->id = create_guid();
        $this->note2->new_with_id = true;
        $this->note2->name = "[Bug47069] Deleted Note";
        $this->note2->description = "This note should be deleted.";
        $this->note2->deleted = 1;
        $this->note2->save();

        ob_start();
        $_REQUEST['adminAction'] = 'refreshEstimate';
        $_REQUEST['bean'] = 'Notes';
        require_once('modules/Administration/Async.php');
        $firstEstimate = $out;
        ob_end_clean();

        ob_start();
        $_REQUEST['adminAction'] = 'repairXssExecute';
        $_REQUEST['bean'] = 'Notes';
        $_REQUEST['id'] = json_encode(array($this->note1->id,$this->note2->id));
        require_once('modules/Administration/Async.php');
        ob_end_clean();

        ob_start();
        $_REQUEST['adminAction'] = 'refreshEstimate';
        $_REQUEST['bean'] = 'Notes';
        require_once('modules/Administration/Async.php');
        $secondEstimate = $out;
        ob_end_clean();

        $this->assertEquals($firstEstimate['count'],$secondEstimate['count'], 'The record count should not increase after a repair XSS');
    }
}