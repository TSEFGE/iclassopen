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


require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

class SugarTestSugarEmailAddressUtilities
{
    private static $_createdEmailAddresses = array();

    private static $_createdContact = null;

    private function __construct() {} // not an instantiated class.

    /**
     * creates a Parent Bean to hang Emails from
     * @param $time
     * @return Contact|null
     */
    private function _createContact($time)
    {
        if (self::$_createdContact === null)
        {
            $name = 'SugarEmailAddressContact';
            $lname = 'LastName';
            $contact = new Contact();
            $contact->first_name = $name . $time;
            $contact->last_name = 'LastName';
            $contact->save();

            $GLOBALS['db']->commit();
            self::$_createdContact = $contact;
        }

        return self::$_createdContact;
    }

    /**
     * @param $contact
     * @param $time
     * @param $id
     * @param $override
     * @return SugarEmailAddress
     */
    private static function _createEmailAddress($contact,$time,$id,$override)
    {
        $params['email_address'] = 'semailaddress@'. $time. 'sugar.com';
        $params['primary'] = true;
        $params['reply_to'] = false;
        $params['invalid'] = false;
        $params['opt_out'] = false;
        foreach($override as $key => $value) {
            $params[$key] = $value;
        }


        $contact->emailAddress->addAddress($params['email_address'], $params['primary'], $params['reply_to'],
                                           $params['invalid'], $params['opt_out'], $id);
        $contact->emailAddress->save($contact->id, $contact->module_dir);
        self::$_createdEmailAddresses[] = $contact->emailAddress;
        return $contact->emailAddress;
    }

    /**
     * Create a SugarEmailAddress
     * - This version doesn't bother attaching a SugarEmailAddress to a parent bean.
     * - As such, save() doesn't work on the email addresses.
     * @access public
     * @param string $address - custom address to pass, otherwise pass null.
     * @param string $id - pass parameter to set a specific uuid for the SugarEmailAddress
     * @param array $override - pass key => value array of parameters to override the defaults
     * @return SugarEmailAddress
     */
    public static function createEmailAddress($address=null,$id = '', $override = array())
    {
        $time = mt_rand();
        $contact = self::_createContact($time);
        if (!empty($address)) {
            $override['email_address'] = $address;
        }
        $address = self::_createEmailAddress($contact, $time, $id, $override);
        return $address;
    }

    /**
     * Clean up after use
     * @access public
     */
    public static function removeAllCreatedEmailAddresses()
    {
        $address_ids = self::getCreatedEmailAddressIds();
        $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (\'' . implode("', '", $address_ids) . '\')');
    }

    /**
     * clean up the related bean and the relationship table
     * @access public
     */
    public static function removeCreatedContactAndRelationships(){
        if (self::$_createdContact === null) {
            return;
        }

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '".self::$_createdContact->id."'");
        $GLOBALS['db']->query('DELETE FROM email_addr_bean_rel WHERE bean_module=\'Contacts\' AND bean_id =\'' . self::$_createdContact->id . '\'');
        self::$_createdContact = null;
    }


    /**
     * Retrieve a list of all ids of SugarEmailAddresses created through this class
     * @access public
     * @return array ids of all SugarEmailAddresses created
     */
    public static function getCreatedEmailAddressIds()
    {
        $address_ids = array();
        foreach (self::$_createdEmailAddresses as $address) {
            $address_ids[] = $address->id;
        }
        return $address_ids;
    }

    /**
     * In case we don't have our bean's UUID - get it via address
     * @param $address - email address
     * @return string|null UUID of bean for email address.
     */
    public static function fetchEmailIdByAddress($address) {
        $email_caps = strtoupper(trim($address));
        $rs = $GLOBALS['db']->query("SELECT id from email_addresses where email_address_caps='$email_caps'");
        $a = $GLOBALS['db']->fetchByAssoc($rs);

        if (!empty($a['id'])) {
            return $a['id'];
        }
        else {
            return null;
        }
    }

    /**
     * get our parent bean
     * @return Contact|null
     */
    public static function getContact() {
        return self::_createContact(mt_rand());
    }

}
