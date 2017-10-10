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
 * @group 54858
 *
 */
class Bug54858Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->user->email1 = $email = 'test'.uniqid().'@test.com';
        $this->user->save();
        $GLOBALS['current_user'] = $this->user;
        $this->vcal_url =  "{$GLOBALS['sugar_config']['site_url']}/vcal_server.php/type=vfb&source=outlook&email=" . urlencode($email);
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
    	unset($GLOBALS['current_user']);
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Test that new user gets ical key
     */
    public function testCreateNewUser()
    {
        $this->assertNotEmpty($this->user->getPreference('calendar_publish_key'), "Publish key is not set");
    }

	protected function callVcal($key)
	{
       $ch = curl_init($this->vcal_url."&key=" . urlencode($key));
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $return = curl_exec($ch);
	   $info = curl_getinfo($ch);
	   $info['return'] = $return;
	   return $info;
	}

	// test vcal service
    public function testPublishKey()
    {
        $res = $this->callVcal('');
		$this->assertEquals('401', $res['http_code']);

        $res = $this->callVcal('blah');
		$this->assertEquals('401', $res['http_code']);

		$key = $this->user->getPreference('calendar_publish_key');
        $res = $this->callVcal($key);
		$this->assertEquals('200', $res['http_code']);
		$this->assertContains('BEGIN:VCALENDAR', $res['return']);

		// now reset the key
        $this->user->setPreference('calendar_publish_key', '');
        $this->user->savePreferencesToDB();
        $GLOBALS['db']->commit();

        $res = $this->callVcal('');
		$this->assertEquals('401', $res['http_code']);
        $res = $this->callVcal($key);
		$this->assertEquals('401', $res['http_code']);
	}
}
