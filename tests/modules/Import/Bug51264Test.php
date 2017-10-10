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


require_once('modules/Import/ImportDuplicateCheck.php');

/**
 * Bug #51264
 * Importing updates to rows prevented by duplicates check
 *
 * @ticket 51264
 */
class Bug51264Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $contact;

    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        unset($this->contact);
        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @group 51264
     */
    public function testIsADuplicateRecordWithID()
    {
        $idc = new ImportDuplicateCheck($this->contact);
        $result = $idc->isADuplicateRecord(array('special_idx_email1::email1'));
        $this->assertFalse($result);
    }

    /**
     * @group 51264
     */
    public function testIsADuplicateRecordWithInvalidID()
    {
        $contact = new Contact();
        $contact->id = '0000000000000000';
        $contact->email1 = $this->contact->email1;
        $idc = new ImportDuplicateCheck($contact);
        $result = $idc->isADuplicateRecord(array('special_idx_email1::email1'));
        $this->assertTrue($result);
    }

    /**
     * @group 51264
     */
    public function testIsADuplicateRecordWithInvalidID2()
    {
        $contact = new Contact();
        $contact->id = '0000000000000000';
        $contact->email1 = 'Bug51264Test@Bug51264Test.com';
        $idc = new ImportDuplicateCheck($contact);
        $result = $idc->isADuplicateRecord(array('special_idx_email1::email1'));
        $this->assertFalse($result);
    }

    /**
     * @group 51264
     */
    public function testIsADuplicateRecord()
    {
        $contact = new Contact();
        $contact->email1 = $this->contact->email1;
        $idc = new ImportDuplicateCheck($contact);
        $result = $idc->isADuplicateRecord(array('special_idx_email1::email1'));
        $this->assertTrue($result);
    }
}