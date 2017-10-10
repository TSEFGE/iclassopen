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


/*
 * Bug 45181: Please remove "Log Memory Usage" if useless
 * @ticket 45181
 */

class Bug45181 extends Sugar_PHPUnit_Framework_TestCase {
    private $sugar_config;
    private $sugarView;

    function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        global $sugar_config;
        $this->sugar_config = $sugar_config;
        $this->sugarView = new Bug45181TestSugarViewMock();
        $this->sugarView->module = 'Contacts';
        $this->sugarView->action = 'EditView';
        if (is_file('memory_usage.log'))
        {
            unlink('memory_usage.log');
        }
    }

    function tearDown()
    {
        global $sugar_config;
        if (is_file('memory_usage.log'))
        {
            unlink('memory_usage.log');
        }
        $sugar_config = $this->sugar_config;
        unset($this->sugar_config);
        unset($GLOBALS['app_strings']);
    }


    /**
     * testLogMemoryUsageOn
     * This test asserts that when log_memory_usage is set to true we receive a log message from the function
     * call and the memory_usage.log file is created.
     *
     * @outputBuffering enabled
     */
    function testLogMemoryUsageOn()
    {
        if(!function_exists('memory_get_usage') || !function_exists('memory_get_peak_usage'))
        {
            $this->markTestSkipped('Skipping test since memory_get_usage and memory_get_peak_usage function are unavailable');
            return;
        }
        global $sugar_config;
        $sugar_config['log_memory_usage'] = true;
        $output = $this->sugarView->logMemoryStatisticsTest("\n");
        $this->assertNotEmpty($output, "Failed to recognize log_memory_usage = true setting");
        $this->assertFileExists('memory_usage.log', 'Unable to create memory_usage.log file');
    }

    /**
     * testLogMemoryUsageOff
     * This test asserts that when log_memory_usage is set to false we do not receive a log message from the function
     * call nor is the memory_usage.log file created.
     *
     * @outputBuffering enabled
     *
     */
    function testLogMemoryUsageOff()
    {
        if(!function_exists('memory_get_usage') || !function_exists('memory_get_peak_usage'))
        {
            $this->markTestSkipped('Skipping test since memory_get_usage and memory_get_peak_usage function are unavailable');
            return;
        }
        global $sugar_config;
        $sugar_config['log_memory_usage'] = false;
        $output = $this->sugarView->logMemoryStatisticsTest("\n");
        $this->assertEmpty($output, "Failed to recognize log_memory_usage = false setting");
        $this->assertFileNotExists('memory_usage.log');
    }
}

require_once('include/MVC/View/SugarView.php');
class Bug45181TestSugarViewMock extends SugarView
{
    public function logMemoryStatisticsTest($newline)
    {
        return $this->logMemoryStatistics($newline);
    }
}