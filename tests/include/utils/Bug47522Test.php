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


 
require_once 'include/utils.php';

/*
 This unit test simulates cleaning the key values in a POST/REQUEST when mbstring.encoding_translation is on.
 When you turn on  mbstring.encoding_translation in php.ini, it's supposed to translate the characters in the POST array during an http request.
 It's not supposed to touch the key names though.  This test is for the code that cleans those key names
  */
class Bug47522Test extends Sugar_PHPUnit_Framework_TestCase
{

    var $orig_ini_encoding_val;
    	public function setUp()
	{
        $this->orig_ini_encoding_val = ini_get('mbstring.encoding_translation');

        //set http translation on
        ini_set('mbstring.encoding_translation',1);

    }

    public function tearDown()
    {
        //set back value of ini setting
        ini_set('mbstring.encoding_translation',$this->orig_ini_encoding_val);

        unset($this->orig_ini_encoding_val);
    }

    public function testEncodedKeyCleaning()
    {


	//continue this test only if encoding_translation is turned on.
        if(ini_get('mbstring.encoding_translation')==='1'){
            //inject bad string into request
            $key = "'you'shall'not'pass!";
            $val = ' must.. not.. die..';
            $_REQUEST[$key] = $val;

            //assert the key is in the request object
            $this->assertsame($_REQUEST[$key], $val, 'request key was not set, rest of test is invalid');

            //clean the string, it should fail but since encoding translation is on, it should only remove the
            //key from request object
            securexsskey($key,false);

            //assert the key is no longer in request
            $this->assertNotContains($key,$_REQUEST,'Key should not hav passed xss security check, but still exists in request, this is wrong.');

        }else{
            //encoding_translation is turned off, this test will not work, so let's skip it
    	    $this->markTestSkipped('mbstring.encoding_translation is turned off, mark as skipped for now.');
        }



    }
}

