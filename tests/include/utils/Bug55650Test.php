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
 * 
 * Check if getTrackerSubstring() utils function returns a html decoded value
 * which is also chopped to the tracker_max_display_length parameter
 * 
 * @ticket 55650
 * @author avucinic@sugarcrm.com
 *
 */
class Bug55650Test extends Sugar_PHPUnit_Framework_TestCase
{
	
    /**
     * @dataProvider providerTestGetTrackerSubstring
     */
    public function testGetTrackerSubstring($value, $expected)
    {
    	// Setup some helper values
    	$add = "";
    	$cut = $GLOBALS['sugar_config']['tracker_max_display_length'];
    	// If the length is longer then the set tracker_max_display_length, the substring length for asserting equal will be
    	// -3 the length of the tracker_max_display_length, and we should add ...
    	if (strlen($expected) > $GLOBALS['sugar_config']['tracker_max_display_length'])
    	{
    		$add = "...";
    		$cut = $cut - 3;
    	}
    	
    	// Test if the values got converted, and if the original string was chopped to the expected string
        $this->assertEquals(getTrackerSubstring($value), substr($expected, 0, $cut) . $add, '');
    }
    
    function providerTestGetTrackerSubstring()
    {
        return array(
        	0 => array("A lot of quotes &#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;", "A lot of quotes '''''''''''''''"),
        	1 => array("A lot of quotes <>'\" &#34; &#62; &#60; &#8364; &euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;", "A lot of quotes <>'\" \" > < € €€€€€€€€"),
        	2 => array("A lot of quotes &amp;", "A lot of quotes &"),
        );
    }
    
}

?>