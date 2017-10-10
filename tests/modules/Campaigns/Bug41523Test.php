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


require_once 'include/SubPanel/SubPanelTiles.php';

/**
 * Bug #41523
 * Subject Blank Rows Are Displayed In Campaign Status "Leads Created" Subpanel If Leads Are Deleted
 *
 * @ticket 41523
 */
class Bug41523Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @var MysqliManager
     */
    private $db;

    public function setUp()
    {
        $this->markTestIncomplete("This test breaks on stack66 - working with dev to fix");
        global $focus;

        // Init session user settings
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('max_tabs', 2);

        $this->campaign = SugarTestCampaignUtilities::createCampaign();
        $this->db       = $GLOBALS['db'];
        $focus          = $this->campaign;

        // Setting for SubPanel
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_REQUEST['module']        = 'Campaigns';
        $_REQUEST['action']        = 'TrackDetailView';
        $_REQUEST['record']        = $this->campaign->id;
    }

    public function tearDown()
    {
        unset($_SERVER['REQUEST_METHOD']);

        // Delete created campaings
        SugarTestCampaignUtilities::removeAllCreatedCampaigns();

        // Delete users
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group 41523
     */
    public function testDeletedLeadsOnCapmaingStatusPage()
    {
        // Create 2 leads
        $lead1 = $this->createLeadFromWebForm('User1');
        $lead2 = $this->createLeadFromWebForm('User2');

        // Delete one lead
        $lead1->mark_deleted($lead1->id);

        $this->assertEquals($this->campaign->getDeletedCampaignLogLeadsCount(), 1);

        // Test SubPanel output
        $subpanel = new SubPanelTiles($this->campaign, 'Campaigns');
        $html = $subpanel->display();

        preg_match('|<div id="list_subpanel_lead">.*?<table.*?</table>.*?</table>.*?</tr>(.*?)</table>|s', $html, $match);
        preg_match_all('|<tr|', $match[1], $match);

        $this->assertEquals(count($match[0]), 2);
    }

    /**
     * @param $lastName Last name for new lead
     *
     * @return Lead
     */
    private function createLeadFromWebForm($lastName)
    {
        $postData = array(
            'last_name' => $lastName,
            'campaign_id' => $this->campaign->id,
        );

        // Send request for add lead
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $GLOBALS['sugar_config']['site_url'] . '/index.php?entryPoint=WebToLeadCapture');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        $this->assertEquals('Thank You For Your Submission.', $response);

        curl_close($ch);

        // Fetch last created lead
        $createdLead = new Lead();
        $query = 'SELECT * FROM leads ORDER BY date_entered DESC LIMIT 1';
        $createdLead->fromArray($this->db->fetchOne($query));

        return $createdLead;
    }
}
