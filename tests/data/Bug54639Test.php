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


require_once('data/SugarBean.php');

/**
 * @ticket 47731
 * @ticket 54639
 */
class Bug54639Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $contact = null;

    /**
     *
     */
    public function setUp()
    {
        $this->contact = new Contact();
        $this->contact->field_defs["as_tetrispe_accounts_name"] = array (
            'name' => 'as_tetrispe_accounts_name',
            'type' => 'relate',
            'source' => 'non-db',
            'vname' => 'LBL_AS_TETRISPERSON_ACCOUNTS_FROM_ACCOUNTS_TITLE',
            'save' => true,
            'id_name' => 'as_tetrispac95ccounts_ida',
            'link' => 'as_tetrisperson_accounts',
            'table' => 'accounts',
            'module' => 'Accounts',
            'rname' => 'name',
        );

        $this->contact->field_defs["as_tetrispac95ccounts_ida"] = array (
            'name' => 'as_tetrispac95ccounts_ida',
            'type' => 'link',
            'relationship' => 'as_tetrisperson_accounts',
            'source' => 'non-db',
            'reportable' => false,
            'side' => 'right',
            'vname' => 'LBL_AS_TETRISPERSON_ACCOUNTS_FROM_AS_TETRISPERSON_TITLE',
        );
    }

    /**
     * Test getting import fields from a bean when a relationship has been defined and the id field is only defined as a link
     * and not a relate entry. The id field should be exposed so that users can select it from a list during the import process.
     *
     * @group bug54639
     * @return void
     */
    public function testGetImportableFields()
    {
        $c = new Contact();
        $importableFields = $c->get_importable_fields();
        $this->assertTrue(isset($importableFields['as_tetrispac95ccounts_ida']));
    }
}