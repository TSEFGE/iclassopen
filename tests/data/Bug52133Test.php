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


class Bug52133Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $bean;
    protected $hook;

    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public static function tearDownAfterClass()
    {
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function setUp()
    {
        $this->bean = new Account();
        Bug52113TestHook::$count = 0;
        LogicHook::refreshHooks();
	}

	public function tearDown()
	{
	    if(!empty($this->hook)) {
	        call_user_func_array('remove_logic_hook', $this->hook);
	    }
	}

    /**
     * @ticket 52113
     */
    public function testCallLogicHookLoop()
    {
        $this->hook = array('Accounts', 'test_event', Array(1, 'Test hook', __FILE__, 'Bug52113TestHook', 'count'));
        call_user_func_array('check_logic_hook_file', $this->hook);
        for($i=0;$i<50;$i++) {
            $this->bean->call_custom_logic("test_event");
        }
        $this->assertEquals(50, Bug52113TestHook::$count);
    }

	/**
     * @ticket 52113
     */
    public function testCallLogicHookRecursive()
    {
        $this->hook = array('Accounts', 'test_event', Array(1, 'Test hook', __FILE__, 'Bug52113TestHook', 'recurse'));
        call_user_func_array('check_logic_hook_file', $this->hook);
        for($i=0;$i<20;$i++) {
            $this->bean->call_custom_logic("test_event");
        }
        $this->assertEquals(220, Bug52113TestHook::$count);
    }
}

class Bug52113TestHook
{
    static public $count = 0;
    public function count()
    {
        self::$count++;
    }

    public function recurse($bean, $event)
    {
        $this->count();
        $bean->call_custom_logic($event);
    }
}
