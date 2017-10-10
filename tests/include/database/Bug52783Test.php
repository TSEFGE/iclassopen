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

require_once 'include/database/MssqlManager.php';

/**
 * 
 * Test is used for testing returnOrderBy() through limitQuery() because returnOrderBy() is private
 * In MSSQL we use a different way of limiting a query using TOP and ROW_NUMBER() OVER( ORDER BY ordering )
 * the 'ordering' attribute is tested on correctness with this test
 * 
 * @author avucinic@sugarcrm.com
 *
 */
class Bug52783Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $_db;

	public function setUp()
	{
		$this->_db = new MssqlManager();
	}

	public function tearDown()
	{
	}

	/**
	 * Test if returnOrderBy() inside limitQuery() method returns a valid MSSQL ordering column for use in OVER()
	 * @dataProvider orderProvider
	 */
	public function testReturnOrderBy($sql, $expected)
	{
		$expected = strtolower($expected);
		$result = strtolower($this->_db->limitQuery($sql, 21, 20, false, '', false));
		$this->assertContains($expected, $result);
	}

	public function orderProvider()
	{
		return array(
		0 => array(
				"SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id is_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name",
			
				"LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,'')))"
			),
			1 => array(
				"SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id is_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name ASC",
			
				"LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,'')))"
			),
			2 => array(
				"SELECT  contacts.id , LTRIM(RTRIM(ISNULL(contacts.first_name,'')+' '+ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id is_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY contacts.name ASC",
			
				"contacts.name"
			),
			3 => array(
				"SELECT  contacts.id , ISNULL(contacts.last_name,''))) as name, contacts.first_name , contacts.last_name , contacts.salutation  , accounts.name account_name, jtl0.account_id account_id, contacts.title , contacts.phone_work  , LTRIM(RTRIM(ISNULL(jt1.first_name,'')+' '+ISNULL(jt1.last_name,''))) assigned_user_name , jt1.created_by assigned_user_name_owner  , 'Users' assigned_user_name_mod, contacts.date_entered , contacts.assigned_user_id  , sfav.id is_favorite  FROM contacts   LEFT JOIN  accounts_contacts jtl0 ON contacts.id=jtl0.contact_id AND jtl0.deleted=0
				LEFT JOIN  accounts accounts ON accounts.id=jtl0.account_id AND accounts.deleted=0
				AND accounts.deleted=0  LEFT JOIN  users jt1 ON contacts.assigned_user_id=jt1.id AND jt1.deleted=0
				AND jt1.deleted=0 LEFT JOIN  sugarfavorites sfav ON sfav.module ='Contacts' AND sfav.record_id=contacts.id AND sfav.created_by='1' AND sfav.deleted=0  where contacts.deleted=0 ORDER BY name ASC",
			
				"ISNULL(contacts.last_name,'')))"
			),
		);
	}
}
