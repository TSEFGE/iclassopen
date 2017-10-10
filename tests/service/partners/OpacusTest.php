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


require_once('include/SugarSQLValidate.php');

class OpacusTest extends Sugar_PHPUnit_Framework_TestCase
{


/**
 * getEntryListQueries
 *
 * These are some of the queries that may come in to the get_entry_list method from the Thunderbird plugin
 */
public function getEntryListThunderbirdPluginQueries()
{
    return array(
        array("(project_task.project_id IN (SELECT project_id FROM projects_contacts pc INNER JOIN email_addr_bean_relÊ
        eabr ON eabr.bean_id = pc.contact_id AND eabr.bean_module='Contacts' inner join email_addresses ea5 ON
        eabr.email_address_id = ea5.id WHERE ea5.email_address LIKE 'test%' AND eabr.deleted = '0' AND ea5.deleted = '0'
        AND pc.deleted = '0'))"),
    );
}

/**
 * testGetEntryListThunderbirdPlugin
 *
 * This method tests the SugarSQLValidate.php's validateQuery method.
 *
 * @param $sql String of the test SQL to simulate the Word plugin
 *
 * @outputBuffering disabled
 * @dataProvider getEntryListThunderbirdPluginQueries
 */
public function testGetEntryListThunderbirdPlugin($sql)
{
    $this->markTestIncomplete('Need to resolve the above query or investigate a workaround for Opacus');
    $valid = new SugarSQLValidate();
    $this->assertTrue($valid->validateQueryClauses($sql), "SugarSQLValidate found Bad query: {$sql}");
}

}
