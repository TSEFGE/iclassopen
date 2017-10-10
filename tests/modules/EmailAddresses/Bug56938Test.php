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
 * @ticket 56938
 */
class Bug56938Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var User */
    private $user, $duplicate;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        SugarTestEmailAddressUtilities::addAddressToPerson(
            $this->user,
            'bug-56938-test@example.com'
        );
    }

    /**
     * Ensure that a new instance of EmailAddress is created during creating
     * User duplicate
     */
    public function testCreateDuplicate()
    {
        // retrieve created user from database in order to populate email addresses
        $original = new User();
        $original->retrieve($this->user->id);

        // simulate request parameters of "Duplicate" web form
        $address = $original->emailAddress->addresses[0];
        $_REQUEST = array(
            'Users_email_widget_id' => '1',
            'Users1emailAddress0'   => $address['email_address'],
            'Users1emailAddressId0' => $address['email_address_id'],
        );

        // create a duplicate and retrieve it from database as well
        $duplicate = $this->duplicate = new User();
        $duplicate->save();

        $retrieved = new User();
        $retrieved->retrieve($duplicate->id);

        // ensure that email address is created in duplicate
        $this->assertEquals(1, count($retrieved->emailAddress->addresses));

        // ensure that it's value is the same as original email address
        $this->assertEquals(
            $original->emailAddress->addresses[0]['email_address'],
            $retrieved->emailAddress->addresses[0]['email_address']
        );

        // ensure that new instance of EmailAddress is created instead of
        // sharing the same instance between users
        $this->assertNotEquals(
            $original->emailAddress->addresses[0]['email_address_id'],
            $retrieved->emailAddress->addresses[0]['email_address_id']
        );
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $_REQUEST = array();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        if ($this->duplicate)
        {
            $this->duplicate->mark_deleted($this->duplicate->id);
        }
    }
}
