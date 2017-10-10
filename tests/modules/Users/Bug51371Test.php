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


require_once('modules/Users/User.php');
/**
 * @ticket 51371
 */
class Bug51371Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function getHashes()
    {
        $checks = array(
            // plain MD5
            array("my passw0rd", "0db22d09a263d458c79581aefcbdb300"),
           // whatever User has
            array("my passw0rd", User::getPasswordHash("my passw0rd")),
            );
       if(defined('CRYPT_EXT_DES') && constant('CRYPT_EXT_DES')) {
            // extended crypt
            $checks[] = array("my passw0rd", "_.012saltIO.319ikKPU");
       }
       if(defined('CRYPT_MD5') && constant('CRYPT_MD5')) {
            // md5 crypt
            $checks[] = array("my passw0rd", '$1$F0l3iEs7$sT3th960AcuSzp9kiSmxh/');
       }
       if(defined('CRYPT_BLOWFISH') && constant('CRYPT_BLOWFISH')) {
            // blowfish
            $checks[] = array("my passw0rd", '$2a$07$usesomesillystringforeETvnK0/TgBVIVHViQjGDve4qlnRzeWS');
       }
       /* Temporarily skip this test due to a php bug on StackDB2 running PHP Version 5.3.8-ZS5.5.0
       if(defined('CRYPT_SHA256') && constant('CRYPT_SHA256')) {
            // sha-256
            $checks[] = array("my passw0rd", '$5$rounds=5000$usesomesillystri$aKwd34p0LSvMZdW1LolZOPCCsx1mYdTynQn9ZrWrO87');
       }
       */
       return $checks;
    }

    /**
     * @dataProvider getHashes
     */
    public function testUserhash($password, $user_hash)
    {
        $this->assertTrue(User::checkPassword($password, $user_hash));
    }
}