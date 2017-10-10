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


require_once 'include/SubPanel/SubPanelDefinitions.php';

class Bug56652Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Contact */
    protected $contact;

    /**
     * Account names are randomly sorted in order to make sure that the data is
     * properly sorted by the application
     *
     * @var array
     */
    protected $account_names = array(
        'E', 'G', 'A', 'D', 'B', 'H', 'F', 'C'
    );

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        parent::setUp();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('opportunities');
        foreach ($this->account_names as $account_name)
        {
            $account = SugarTestAccountUtilities::createAccount();
            $account->name = $account_name;
            $account->save(false);

            $opportunity = SugarTestOpportunityUtilities::createOpportunity(null, $account);
            $this->contact->opportunities->add($opportunity);
        }
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();
        parent::tearDown();

        SugarTestHelper::tearDown();
    }

    /**
     * @param string $order
     * @param string $function
     * @dataProvider getOrders
     */
    public function testSubPanelDataIsSorted($order, $function)
    {
        // create a minimum required subpanel definition
        $subPanel = new aSubPanel(null, array(
            'module'            => 'Opportunities',
            'subpanel_name'     => null,
            'get_subpanel_data' => 'opportunities',
        ), $this->contact);

        // fetch subpanel data
        $response = SugarBean::get_union_related_list(
            $this->contact, 'account_name', $order, '', 0, -1, -1, 0, $subPanel
        );

        $this->assertArrayHasKey('list', $response);

        $account_names = array();

        /** @var Opportunity $opportunity */
        foreach ($response['list'] as $opportunity)
        {
            $account_names[] = $opportunity->account_name;
        }

        $sorted = $account_names;
        $function($sorted);

        // ensure that opportunities are sorted by account name in the needed order
        $this->assertSame($sorted, $account_names);
    }

    /**
     * @return array
     */
    public static function getOrders()
    {
        return array(
            array('asc',  'sort'),
            array('desc', 'rsort'),
        );
    }
}
