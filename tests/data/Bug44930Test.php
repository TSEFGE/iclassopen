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
 * Bug #44930
 * Issue with the opportunity subpanel in Accounts
 *
 * @author mgusev@sugarcrm.com
 * @ticked 44930
 */
class Bug44930Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test tries to emulate changing of related field and assert correct result
     *
     * @group 44930
     * @return void
     */
    public function testChangingOfRelation()
    {
        $_REQUEST['relate_id'] = '2';
        $_REQUEST['relate_to'] = 'test';

        $bean = new SugarBean();
        $bean->id = '1';
        $bean->test_id = '3';
        $bean->field_defs = array(
            'test' => array(
                'type' => 'link',
                'relationship' => 'test',
                'link_file' => 'data/SugarBean.php',
                'link_class' => 'Link44930'
            )
        );
        $bean->relationship_fields = array(
            'test_id' => 'test'
        );

        $bean->save_relationship_changes(true);

        $this->assertEquals($bean->test_id, $bean->test->lastCall, 'Last relation should point to test_id instead of relate_id');
    }
}

/**
 * Emulation of link2 class
 */
class Link44930
{
    public $lastCall = '';

    function __call($function, $arguments)
    {
        if ($function == 'add')
        {
            $this->lastCall = reset($arguments);
        }
    }
}