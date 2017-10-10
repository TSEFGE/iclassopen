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
 * This tests whether a relationship with parent bean is saved during import.  We simulate a call being imported with
 * parent_id and parent_type columns filled out, which should save the relationship even during import
 * @ticket 50438
 */

require_once('modules/Import/Importer.php');
require_once('modules/Import/sources/ImportFile.php');

class Bug50438Test extends Sugar_PHPUnit_Framework_TestCase
{

    var $contact;
    var $fileArr;
    var $call_id;
    public function setUp()
    {
        global $currentModule ;
        $this->call_id = create_guid();
		$mod_strings = return_module_language($GLOBALS['current_language'], "Contacts");
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        //create a contact
        $this->contact = new Contact();
        $this->contact->first_name = 'Joe UT ';
        $this->contact->last_name = 'Smith UT 50438';
        $this->contact->disable_custom_fields = true;
        $this->contact->save();

        //create array to output as import file using the new contact as the related parent
        $this->fileArr = array(
            0=> "\"{$this->call_id}\",\"Call for Unit Test 50438\",\"Planned\", \"{$this->contact->module_dir}\",\"{$this->contact->id}\""
        );
    }

    public function tearDown()
    {

        $GLOBALS['db']->query("DELETE FROM calls WHERE id='{$this->call_id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id='{$this->contact->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->call_id);
        unset($this->contact);
        unset($this->fileArr);
        unset( $GLOBALS['current_user']);
        unset( $GLOBALS['mod_strings']);
    }



    public function testParentsAreRelatedDuringImport()
    {

        $file = 'upload://test50438.csv';
        $ret = file_put_contents($file, $this->fileArr);
        $this->assertGreaterThan(0, $ret, 'Failed to write to '.$file .' for content '.var_export($this->fileArr,true));

        $importSource = new ImportFile($file, ',', '"');

        $bean = loadBean('Calls');

        $_REQUEST['columncount'] = 5;
        $_REQUEST['colnum_0'] = 'id';
        $_REQUEST['colnum_1'] = 'subject';
        $_REQUEST['colnum_2'] = 'status';
        $_REQUEST['colnum_3'] = 'parent_type';
        $_REQUEST['colnum_4'] = 'parent_id';
        $_REQUEST['import_module'] = 'Contacts';
        $_REQUEST['importlocale_charset'] = 'UTF-8';
        $_REQUEST['importlocale_timezone'] = 'GMT';
        $_REQUEST['importlocale_default_currency_significant_digits'] = '2';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_dec_sep'] = '.';
        $_REQUEST['importlocale_currency'] = '-99';
        $_REQUEST['importlocale_default_locale_name_format'] = 's f l';
        $_REQUEST['importlocale_num_grp_sep'] = ',';
        $_REQUEST['importlocale_dateformat'] = 'm/d/y';
        $_REQUEST['importlocale_timeformat'] = 'h:i:s';

        $importer = new Importer($importSource, $bean);
        $importer->import();

        //fetch the bean using the passed in id and get related contacts
        require_once('modules/Calls/Call.php');
        $call = new Call();
        $call->retrieve($this->call_id);
        $call->load_relationship('contacts');
        $related_contacts = $call->contacts->get();

        //test that the contact id is in the array of related contacts.
        $this->assertContains($this->contact->id, $related_contacts,' Contact was not related during simulated import despite being set in related parent id');
        unset($call);

        /*
        if (is_file($file)) {
            unlink($file);
        }
        */
    }

}