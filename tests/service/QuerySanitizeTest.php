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


require_once 'include/SugarSQLValidate.php';

class QuerySanitizeTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function getQueries()
    {
        return array(
            array("", "", true),
            array("", "accounts.name", true),
            array("", "something BAD", false),
            array("", "something BAD", false),
            array("accounts.name like 'Underwater%'", "", true),
            array("name like 'Underwater%'", "accounts.name", true),
            array("name like 'Underwater%' AND MONTH(accounts.date_created) < MONTH(opportunities.date_modified)+1", "date_created DESC, lcase(account.name) ASC", true),
            array("accounts.name like 'Underwater%'", "something BAD", false),
            array("accounts.name like 'Underwater%'", "also, something BAD", false),
            array("z=1 UNION SELECT * from users", "", false),
            array("z=1 UNION ALL SELECT * from users", "", false),
            array("z=1 UNION ALL SELECT * from users#", "", false),
            array("z=1 UNION ALL SELECT * from users -- test", "", false),
            array("", "something BAD", false),
            array("id='' AND 1=0 UNION SELECT from_addr,1,to_addrs,description FROM emails_text LIMIT 1#", "", false),
            array("", "foo UNION ALL SELECT * from users", false),
            array("", "(leads.status='' OR leads.status IS NULL) DESC,leads.status='New' DESC,leads.status='Assigned' DESC,leads.status='In Process' DESC,leads.status='Converted' DESC,leads.status='Recycled' DESC,leads.status='Dead' DESC", true),
            // OPI email query, should pass
            array("contacts.assigned_user_id = '1' AND (contacts.first_name like '%collin.c.lee@gmail.com%' OR contacts.last_name like '%collin.c.lee@gmail.com%' OR contacts.id IN (SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE 'collin.c.lee@gmail.com%'))", "contacts.last_name asc", true),
            // Evil subselect, should not pass
            array("1=1 AND EXISTS (SELECT * FROM users WHERE is_admin=1 and id=(select id from users where is_admin=1 order by id limit 1) and ((ord(substring(id, 1, 1)) >> 5) & 1))", "", false),
            // OPI email query with evil mods, should not pass
            array("contacts.assigned_user_id = '1' AND (contacts.first_name like '%collin.c.lee@gmail.com%' OR contacts.last_name like '%collin.c.lee@gmail.com%' OR contacts.id IN (SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) JOIN users WHERE users.is_admin='1' AND eabr.deleted=0 AND ea.email_address LIKE 'collin.c.lee@gmail.com%'))", "contacts.last_name asc", false),
            // bug 50336
            array('contacts.id IN (SELECT email_addr_bean_rel.bean_id FROM email_addr_bean_rel, email_addresses WHERE email_addresses.id = email_addr_bean_rel.email_address_id AND email_addr_bean_rel.deleted = 0 AND email_addr_bean_rel.bean_module = \'Contacts\' AND email_addresses.email_address IN ("odemendez@starbucks.fr"))', '', true),
            // bug 50487 - Quoted identifiers
            array("`users`.`user_name` = 'admin'", "", true),
            array("`users`.`user_name` = 'admin' and `users`.`first_name` = 'george'", "", true),
            array("`users`.`user_name` = 'admin' and `users`.`first_name` = 'george'", "`users`.`first_name`", true),
            array("`users.user_name = 'admin'`", "", false),
            );
    }

    /**
     * @dataProvider  getQueries
     * @outputBuffering disabled
     */
    public function testCheckQuery($where, $order_by, $ok)
    {
        $helper = new SugarSQLValidate();
        $res = $helper->validateQueryClauses($where, $order_by);
        $params = array($where, $order_by);
        if($ok) {
            $this->assertTrue($res, string_format("Failed asserting that where: {0} and order by: {1} is valid", $params));
        } else {
            $this->assertFalse($res, string_format("Failed asserting that where: {0} and order by: {1} is invalid", $params));
        }
    }
}