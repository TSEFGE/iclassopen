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


require_once 'include/vCard.php';

/**
 * Test vCard import with/without all required fields
 * Should not allow import when all required fields are present
 *
 * @author avucinic
 */
class Bug60613Test extends Sugar_PHPUnit_Framework_TestCase
{
    // Since we are creating Beans using vCard Import, must save IDs for cleaning
    private $createdContacts = array();
    private $filename;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->filename = $GLOBALS['sugar_config']['upload_dir'] . 'test.vcf';
    }

    public function tearDown()
    {
        // Clean the Contacts created using vCard Import
        foreach ($this->createdContacts as $contactId)
        {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$contactId}'");
        }
        unlink($this->filename);
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider dataProvider
     * @group bug60613
     */
    public function testImportVCard($contents, $module, $allRequiredPresent)
    {
        file_put_contents($this->filename, $contents);

        $vcard = new vCard();
        $beanId = $vcard->importVCard($this->filename, $module);

        if ($allRequiredPresent)
        {
            $this->createdContacts[] = $beanId;
            $this->assertNotEmpty($beanId);
        }
        else
        {
            $this->assertEmpty($beanId);
        }
    }

    public function dataProvider()
    {
        return array(
            array(
                'BEGIN:VCARD
                N:person;test;
                FN: person lead
                BDAY:
                TEL;FAX:
                TEL;HOME:
                TEL;CELL:
                TEL;WORK:
                EMAIL;INTERNET:
                ADR;WORK:;;;;;;
                TITLE:
                END:VCARD', // vCard with all required fields
                'Contacts',
                true),
            array(
                'BEGIN:VCARD
                BDAY:
                TEL;FAX:
                TEL;HOME:
                TEL;CELL:
                TEL;WORK:
                EMAIL;INTERNET:
                ADR;WORK:;;;;;;
                TITLE:
                END:VCARD', // vCard without last_name
                'Contacts',
                false),
            array(
                '', // Empty vCard
                'Contacts',
                false),
        );
    }
}
