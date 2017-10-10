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


require_once('include/SubPanel/SubPanelTilesTabs.php');

/**
 * Bug #44344
 * Custom relationships under same module only show once in subpanel tabs
 *
 * @ticket 44344
 */
class Bug44344Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $account;
    private $subPanel;
    private $group_label;

    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user']->setPreference('max_tabs', '7');

        // create vardef to add new relation account - cases
        $this->addNewRelationships();

        // add new tabgroup whit cases module
        unset($GLOBALS['tabStructure']);
        $this->group_label = 'LBL_GROUPTAB_'.mktime();
        $GLOBALS['tabStructure'][$this->group_label] = array(
            'label' => $this->group_label,
            'modules' => array('Cases')
        );

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($this->account);

        unset($GLOBALS['tabStructure']);
        unset($this->subPanel, $this->group_label);
        unset($GLOBALS['dictionary']["accounts_cases_10000"]);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    /**
     * add new relation accounts_cases_10000 (Account to Cases: one-to-many)
     * @return void
     */
    private function addNewRelationships()
    {
        $GLOBALS['dictionary']["accounts_cases_10000"] = array (
            'true_relationship_type' => 'one-to-many',
            'from_studio' => true,
            'relationships' =>
            array (
                'accounts_cases_10000' =>
                array (
                    'lhs_module' => 'Accounts',
                    'lhs_table' => 'accounts',
                    'lhs_key' => 'id',
                    'rhs_module' => 'Cases',
                    'rhs_table' => 'cases',
                    'rhs_key' => 'id',
                    'relationship_type' => 'many-to-many',
                    'join_table' => 'accounts_cases_10000_c',
                    'join_key_lhs' => 'accounts_cases_10000accounts_ida',
                    'join_key_rhs' => 'accounts_cases_10000cases_idb',
                ),
            ),
            'table' => 'accounts_cases_10000_c',
            'fields' =>
            array (
                0 => array ('name' => 'id', 'type' => 'varchar', 'len' => 36),
                1 => array ('name' => 'date_modified', 'type' => 'datetime'),
                2 => array ('name' => 'deleted', 'type' => 'bool', 'len' => '1', 'default' => '0', 'required' => true),
                3 => array ('name' => 'accounts_cases_10000accounts_ida', 'type' => 'varchar', 'len' => 36),
                4 => array ('name' => 'accounts_cases_10000cases_idb', 'type' => 'varchar', 'len' => 36),
            ),
            'indices' =>
            array (
                0 => array ('name' => 'accounts_cases_10000spk', 'type' => 'primary', 'fields' => array (0 => 'id')),
                1 => array ('name' => 'accounts_cases_10000_ida1', 'type' => 'index', 'fields' => array (0 => 'accounts_cases_10000accounts_ida')),
                2 => array ('name' => 'accounts_cases_10000_alt', 'type' => 'alternate_key', 'fields' => array (0 => 'accounts_cases_10000cases_idb')),
            ),
        );
    }

    /**
     * generate mock layout_defs for SubPanelDefinitions object
     * add two subpanels: cases (default relation) and accounts_cases_10000 (test created relation)
     * @return array
     */
    private function getLayoutDefs()
    {
        $layout_defs = array();

        $layout_defs["subpanel_setup"]['cases'] = array(
            'order' => 100,
            'sort_order' => 'desc',
            'sort_by' => 'case_number',
            'module' => 'Cases',
            'subpanel_name' => 'ForAccounts',
            'get_subpanel_data' => 'cases',
            'add_subpanel_data' => 'case_id',
            'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopButtonQuickCreate'),
                array('widget_class' => 'SubPanelTopSelectButton', 'mode'=>'MultiSelect')
            ),
        );

        $layout_defs["subpanel_setup"]['accounts_cases_10000'] = array (
            'order' => 100,
            'module' => 'Cases',
            'subpanel_name' => 'default',
            'sort_order' => 'asc',
            'sort_by' => 'id',
            'title_key' => 'LBL_ACCOUNTS_CASES_FROM_CASES_TITLE',
            'get_subpanel_data' => 'accounts_cases_10000',
        );
        return $layout_defs;
    }

    /**
     * @group 44344
     * @outputBuffering enabled
     */
    public function testSubPanelTilesTabsGetTabs()
    {
        $tabs = array('cases', 'accounts_cases_10000');
        $this->subPanel = new SubPanelTilesTabs($this->account, '', $this->getLayoutDefs());

        // get tabs by selected group ($this->group_label)
        $returned_tabs = $this->subPanel->getTabs($tabs, true, $this->group_label);

        foreach ( $tabs as $tab )
        {
            $this->assertContains($tab, $returned_tabs);
        }
    }
}