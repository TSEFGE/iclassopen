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


require_once "modules/Tasks/Task.php";
require_once "modules/Contacts/Contact.php";
require_once "include/SearchForm/SearchForm2.php";

/**
 * 
 * Test checks if SearchDef with 'force_unifiedsearch' => true concatenates the db_field array properly,
 * when the search value is a multiple word term (contains space between the words)
 * 
 * @author snigam@sugarcrm.com, avucinic@sugarcrm.com
 *
 */
class Bug45709_53785_Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $task = null;
	var $contact = null;
	var $team = null;
	var $requestArray = null;
	var $searchForm = null;

    public function setUp()
    {
		SugarTestHelper::setUp('app_list_strings');
		SugarTestHelper::setUp('app_strings');
		SugarTestHelper::setUp('current_user');
		
		$this->contact = SugarTestContactUtilities::createContact();
    	$this->task = SugarTestTaskUtilities::createTask();
    	$this->task->contact_id = $this->contact->id;
    	$this->task->save();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestHelper::tearDown();
    }

    /**
     * @ticket 45709
     */
    public function testGenerateSearchWhereForFieldsWhenFullContactNameGiven()
    {
    	// Array to simulate REQUEST object
    	$this->requestArray['module'] = 'Tasks';
    	$this->requestArray['action'] = 'index';
    	$this->requestArray['searchFormTab'] = 'advanced_search';
    	$this->requestArray['contact_name_advanced'] = $this->contact->first_name . " " . $this->contact->last_name; //value of a contact name field set in REQUEST object
    	$this->requestArray['query'] = 'true';

		// Initialize search form
    	$this->searchForm = new SearchForm($this->task, 'Tasks');

    	// Load the vardefs and search metadata
    	require 'modules/Tasks/vardefs.php';
    	require 'modules/Tasks/metadata/SearchFields.php';
    	require 'modules/Tasks/metadata/searchdefs.php';
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->task->getFieldDefinitions();
        
        // Fill the data from the array we are using to simulate REQUEST
    	$this->searchForm->populateFromArray($this->requestArray,'advanced_search',false);
    	
    	// Get the generated search clause
    	$whereArray = $this->searchForm->generateSearchWhere(true, $this->task->module_dir);
    	
    	// And use it to load the contact created
    	$test_query = "SELECT id FROM contacts WHERE " . $whereArray[0];
    	$result = $GLOBALS['db']->query($test_query);
    	$row = $GLOBALS['db']->fetchByAssoc($result);
    	
    	// Check if the contact was successfully loaded
    	$this->assertEquals($this->contact->id, $row['id'], "Didn't find the correct contact id");

    	// Load the task using the contact_id we got from the previous query
    	$result2 = $GLOBALS['db']->query("SELECT * FROM tasks WHERE tasks.contact_id='{$this->task->contact_id}'");
        $row2 = $GLOBALS['db']->fetchByAssoc($result2);
        
    	// Check if the task is loaded properly	
        $this->assertEquals($this->task->id, $row2['id'], "Couldn't find the expected related task");
    }

}
