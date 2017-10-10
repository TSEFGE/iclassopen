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

 
require_once('include/SugarFields/SugarFieldHandler.php');

class Bug50117Test extends Sugar_PHPUnit_Framework_TestCase
{
   		
	private $_listViewSmartyOutput1;
	private $_listViewSmartyOutput2;
	
	public function setUp()
    {
        $enumField = SugarFieldHandler::getSugarField('enum');
   		$parentFieldArray = array(
		    					'ACCEPT_STATUS_NAME' => 'Accepted',		
							);
		$vardef = array(
					    'name' => 'accept_status_name',
					    'type' => 'enum',
					    'source' => 'non-db',
					    'vname' => 'LBL_LIST_ACCEPT_STATUS',
					    'options' => 'dom_meeting_accept_status',
					    'massupdate' => false,
					    'studio' => Array
					        (
					            'listview' => false,
					            'searchview' => false,
					        )
					);
		$displayParams = array(
							'vname' => 'LBL_LIST_ACCEPT_STATUS',
						    'width' => '11%',
						    'sortable' => false,
						    'linked_field' => 'users',
						    'linked_field_set' => 'users',
						    'name' => 'accept_status_name',
							'module' => 'Users',
						);
		$col = 1;
		
		$this->_listViewSmartyOutput1 = trim($enumField->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col));
		
		$vardef['name'] = 'just_another_name';
		$parentFieldArray['JUST_ANOTHER_NAME'] = 'None';
		
		$this->_listViewSmartyOutput2 = trim($enumField->getListViewSmarty($parentFieldArray, $vardef, $displayParams, $col));
	}
    
     /**
     * @bug 50117
     */
	public function testListViewSmarty()
	{	
		$this->assertEquals("Accepted", $this->_listViewSmartyOutput1);
		$this->assertEquals("None", $this->_listViewSmartyOutput2);
    }
}
