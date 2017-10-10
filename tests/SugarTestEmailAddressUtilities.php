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


require_once 'modules/EmailAddresses/EmailAddress.php';

class SugarTestEmailAddressUtilities
{
    private static $createdAddresses = array();

    private function __construct() {}

    public static function createEmailAddress($address = null)
    {
        if (null === $address)
        {
            $address = 'address-' . mt_rand() . '@example.com';
        }

        $email_address = new EmailAddress();
        $email_address->email_address = $address;
        $email_address->save();

        self::$createdAddresses[] = $email_address;
        return $email_address;
    }

    /**
     * Add specified email address to the person
     *
     * @param Person $person
     * @param string|EmailAddress $address
     * @param array $additional_values
     * @return boolean|EmailAddress
     * @throws InvalidArgumentException
     */
    public static function addAddressToPerson(Person $person, $address, array $additional_values = array())
    {
        if (is_string($address))
        {
            $address = self::createEmailAddress($address);
        }

        if (!$address instanceof EmailAddress)
        {
            throw new InvalidArgumentException(
                'Address must be a string or an instance of EmailAddress, '
                    . gettype($address) . ' given'
            );
        }

        if (!$person->load_relationship('email_addresses'))
        {
            return false;
        }

        // create relation between user and email address
        $person->email_addresses->add(array($address), $additional_values);
        $GLOBALS['db']->commit();
        return $address;
    }

    public static function removeAllCreatedAddresses()
    {
        $ids = self::getCreatedEmailAddressIds();
        if (count($ids) > 0)
        {
            $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (\'' . implode("', '", $ids) . '\')');
        }
        self::$createdAddresses = array();
    }

    public static function getCreatedEmailAddressIds()
    {
        $ids = array();
        foreach (self::$createdAddresses as $address)
        {
            $ids[] = $address->id;
        }
        return $ids;
    }
}
