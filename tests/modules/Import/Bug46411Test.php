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


require_once('modules/Import/Importer.php');
require_once('modules/Calls/Call.php');
/**
 * Bug #46411
 * Importing Calls will not populate Leads or Contacts Subpanel
 *
 * @author adetskin@sugarcrm.com
 * @ticket 46411
 */
class Bug46411Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Importing Calls will not populate Leads or Contacts Subpanel
     *
     * @group 46411
     */
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        
        $this->importSource = new stdClass();
        $this->importSource->columncount = 2;
        $this->importSource->colnum_0 = 'date_entered';
        $this->importSource->colnum_1 = 'last_name';
        $this->importSource->import_module = 'Calls';
        $this->importSource->importlocale_charset = 'UTF-8';
        $this->importSource->importlocale_dateformat = 'm/d/Y';
        $this->importSource->importlocale_timeformat = 'h:i a';
        $this->importSource->importlocale_timezone = 'GMT';
        $this->importSource->importlocale_default_currency_significant_digits = '2';
        $this->importSource->importlocale_currency = '-99';
        $this->importSource->importlocale_dec_sep = '.';
        $this->importSource->importlocale_currency = '-99';
        $this->importSource->importlocale_default_locale_name_format = 's f l';
        $this->importSource->importlocale_num_grp_sep = ',';
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestLeadUtilities::removeAllCreatedLeads();
    }

    public function testMissingFields()
    {
        $bean = $this->getMock('Call',array(
            'get_importable_fields',
            'populateDefaultValues',
            'beforeImportSave',
            'save',
            'afterImportSave',
            'writeRowToLastImport'));

        $bean->expects($this->any())
            ->method('get_importable_fields')
            ->will($this->returnValue(array(
            'account_id'		=> 'accounts',
            'opportunity_id'	=> 'opportunities',
            'contact_id'		=> 'contacts',
            'case_id'			=> 'cases',
            'user_id'			=> 'users',
            'assigned_user_id'	=> 'users',
            'note_id'			=> 'notes',
            'lead_id'			=> 'leads',
        )));

        $bean->expects($this->any())
            ->method('populateDefaultValues')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('beforeImportSave')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('save')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('afterImportSave')
            ->will($this->returnValue('foo'));

        $bean->expects($this->any())
            ->method('writeRowToLastImport')
            ->will($this->returnValue('foo'));

        $bean->date_modified = 'true';
        $bean->fetched_row = array('date_modified' => '');
        $bean->object_name = '';

        $lead = SugarTestLeadUtilities::createLead();
        $a = new bug46411_Importer_mock($this->importSource, $bean);
//        $b = new Call();

        $bean->parent_type = 'leads';
        $bean->parent_id = $lead->id;
//        $bean->relationship_fields = $b->relationship_fields;

        $a->saveImportBean($bean, false);
        $this->assertEquals($bean->parent_id, $bean->lead_id);
    }
}

class bug46411_Importer_mock extends Importer
{
    public function saveImportBean($focus, $newRecord)
    {
        return parent::saveImportBean($focus, $newRecord);
    }

}


