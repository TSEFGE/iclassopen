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


/**
 * Bug #57409
 * It takes 1.4 min to load Contact record edit view
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57409
 */
class Bug57409Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Contact
     */
    protected $contact = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestOpportunityUtilities::createOpportunity();
        $opp1 = SugarTestOpportunityUtilities::createOpportunity();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->load_relationship('opportunities');
        $this->contact->opportunities->add($opp1->id);
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that query returns correct number of records
     *
     * @group 57409
     * @return void
     */
    public function testGetQuery()
    {
        $query = $this->contact->opportunities->relationship->getQuery($this->contact->opportunities, array(
            'enforce_teams' => true
        ));

        $actual = 0;
        $result = $GLOBALS['db']->query($query);
        while ($GLOBALS['db']->fetchByAssoc($result, FALSE)) {
            $actual++;
        }

        $this->assertEquals(1, $actual, 'Number of fetched opportunities is incorrect');
    }
}