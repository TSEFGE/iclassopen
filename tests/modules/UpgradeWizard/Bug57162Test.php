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


require_once('modules/UpgradeWizard/uw_utils.php');

/**
 * Bug #57162
 * Upgrader needs to handle 3-dots releases and double digit values
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57162
 */
class Bug57162Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return array(
            array('656', array('6.5.6')),
            array('660', array('6.6.0beta1')),
            array('640', array('6.4.0rc2')),
            array('600', array('6', 3)),
            array('6601', array('6.6.0.1')),
            array('6601', array('6.6.0.1', 0)),
            array('660', array('6.6.0.1', 3)),
            array('660', array('6.6.0.1', 3, '')),
            array('66x', array('6.6.0.1', 3, 'x')),
            array('660x', array('6.6.0.1', 0, 'x')),
            array('6.6.x', array('6.6.0.1', 3, 'x', '.')),
            array('6-6-0-beta2', array('6.6.0.1', 0, 'beta2', '-')),
            array('6601', array('6.6.0.1', 0, '', '')),
            array('', array('test342lk')),
            array('650', array('6.5.6' ,0, '0')),
            array('60', array('6.5.6', 2, 0)),
        );
    }

    /**
     * Test asserts result of implodeVersion function
     *
     * @group 57162
     * @dataProvider dataProvider
     * @param string $expect version
     * @param array $params for implodeVersion function
     */
    public function testImplodeVersion($expected, $params)
    {
        $actual = call_user_func_array('implodeVersion', $params);
        $this->assertEquals($expected, $actual, 'Result is incorrect');
    }
}
