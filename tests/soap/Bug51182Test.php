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


require_once('tests/service/SOAPTestCase.php');
require_once('modules/Reports/Report.php');

/**
 * Bug #51182
 * Test if get_entry() properly returns details for "Summary With Details" report
 *
 * @author avucinic@sugarcrm.com
 * @ticked 51182
 */
class Bug51182Test extends SOAPTestCase
{

    private $report;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';

        parent::setUp();

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->user = SugarTestHelper::setUp('current_user', array(true, true));

        // Create an opportunity for the report
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity->opportunity_type = 'New Business';
        $opportunity->sales_stage = 'Prospecting';
        $opportunity->save();
        // Create and save summary with details report on opportunities, filtered by id of the above created opportunity
        $this->report = new Report('{"display_columns":[{"name":"name","label":"Opportunity Name","table_key":"self"},{"name":"sales_stage","label":"Sales Stage","table_key":"self"}],"module":"Opportunities","group_defs":[{"name":"opportunity_type","label":"Type","table_key":"self","type":"enum","force_label":"Type"}],"summary_columns":[{"name":"opportunity_type","label":"Type","table_key":"self"}],"report_name":"Opp by type","chart_type":"none","do_round":1,"chart_description":"","numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":{"Filter_1":{"0":{"name":"id","table_key":"self","qualifier_name":"is","input_name0":"' . $opportunity->id . '","input_name1":"' . $opportunity->name . '","column_name":"self:id","id":"rowid0"},"operator":"AND"}}}');
        $_REQUEST['assigned_user_id'] = $GLOBALS['current_user']->id;
        $this->report->save("testSummaryReportWithDetails");
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM saved_reports WHERE id = '{$this->report->saved_report->id}'");
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestHelper::tearDown();
    }

    // Test if the returned data is proper
    public function testSummaryReportWithDetails()
    {
        $this->_login();

        // Setup call
        $client = array(
            'session'       => $this->_sessionId,
            'module_name'   => 'Reports',
            'id'            => $this->report->saved_report->id,
            'select_fields' => array(),
        );

        // Call get_entry on saved report
        $result = $this->_soapClient->call('get_entry', $client);

        // Check if the soap call returned the details rows
        $this->assertNotEmpty($result['entry_list'], "Report shouldn't be empty.");
        $this->assertEquals($result['entry_list'][1]['id'], '0.0', "Summary Report Details row shouldn't be empty.");
        // Check if the returned headers for the details have the correct type
        $this->assertEquals(
            $result['field_list'][2]['type'],
            'header',
            "Type of detail column headers should be 'header'."
        );
        $this->assertEquals(
            $result['field_list'][0]['type'],
            'groupheader',
            "Type of group column headers should be 'groupheader'."
        );
    }
}
