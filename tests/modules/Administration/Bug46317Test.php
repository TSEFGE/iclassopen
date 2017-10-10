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

require_once 'modules/Administration/updater_utils.php';

/**
 * Bug #46317
 * Automatically Check For Updates issue
 * @ticket 46317
 */
class Bug46317Test extends Sugar_PHPUnit_Framework_TestCase
{

    function versionProvider()
    {
        return array(
            array('6.3.1', '6_3_0', TRUE),
            array('6.4', '6.3.1', TRUE),
            array('6_4_0', '6.3.10', TRUE),
            array('6_3_1', '6.3.1', FALSE),
            array('6.3.0', '6_4', FALSE),
            array('6.4.0RC3', '6.3.1', TRUE),
            array('6.4.0RC3', '6.3.1.RC4', TRUE),
            array('goober', 'noober', FALSE),
            array('6.3.5b', 'noob', TRUE),
            array('noob', '6.3.5b', FALSE),
            array('6.5.0beta2', '6.5.0beta1', TRUE),
            array('6.5.5.5.5', '7.5.5.5.5', FALSE),
            array('6.3', '6.2.3.4.5.2.5.2.4superalpha', TRUE),
            array('000000000000.1', '000000000000.1', FALSE),
            array('000000000000.1', '000000000000.05', FALSE),
            array('000000000000.05', '000000000000.1', TRUE),
        );
    }

    /**
     * @dataProvider versionProvider
     * @group 46317
     */
    function testCompareVersions($last_version, $current_version, $expectedResult)
    {
        $this->assertEquals($expectedResult, compareVersions($last_version, $current_version), "Current version: $current_version, last available version: $last_version");
    }
}
?>