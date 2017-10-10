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


//This test is based off of Bug45709Text.php
require_once "include/SearchForm/SearchForm2.php";

class Bug47735Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $user = null;
	var $requestArray = null;
	var $searchForm = null;

    public function setUp()
    {
		$GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
		$GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
		$GLOBALS['current_user'] = $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);
    }

    /**
     *verify that Users search metadata are set up correctly to create a concatenated search on full name from the
     * GenerateSearchWhere function in SearchForm2.php
     */
    public function testGenerateSearchWhereForUsesConcatenatedFullName()
    {
        require 'modules/Users/vardefs.php';
        require 'modules/Users/metadata/SearchFields.php';
        require 'modules/Users/metadata/searchdefs.php';

    	//array to simulate REQUEST object, this simulates a basic search using both the first and
        //last name of the newly created anonymous user
    	$this->requestArray['module'] = 'Users';
    	$this->requestArray['action'] = 'index';
    	$this->requestArray['searchFormTab'] = 'basic_search';
    	$this->requestArray['search_name_basic'] = $this->user->first_name. " ". $this->user->last_name;
    	$this->requestArray['query'] = 'true';

        //create new searchform. populate it's values and generate query
    	$this->searchForm = new SearchForm($this->user,'Users');
        $this->searchForm->searchFields = $searchFields[$this->searchForm->module];
        $this->searchForm->searchdefs = $searchdefs[$this->searchForm->module];
        $this->searchForm->fieldDefs = $this->user->getFieldDefinitions();
    	$this->searchForm->populateFromArray($this->requestArray,'basic_search',false);
    	$whereArray = $this->searchForm->generateSearchWhere(true, $this->user->module_dir);

        //use the where query to search for the user
    	$test_query = "SELECT id FROM users WHERE " . $whereArray[0];
    	$result = $GLOBALS['db']->query($test_query);
    	$row = $GLOBALS['db']->fetchByAssoc($result);

        //make sure row is not empty
        $this->assertEquals($this->user->id, $row['id'], "Did not retrieve any users using the following query: ".$test_query);

        //make sure retrieved correct user
    	$this->assertEquals($this->user->id, $row['id'], "The query returned records but not the correct one: ".$test_query);
    }
}
