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

 

require_once('modules/InboundEmail/InboundEmail.php');

/**
 * @ticket 49983
 */
class DecodeHeaderTest extends Sugar_PHPUnit_Framework_TestCase
{
	protected $ie = null;

	public function setUp()
    {
		$this->ie = new InboundEmail();
	}

    public function getDecodingHeader()
    {
        return array(
            array('Content-Type: text/html; charset="utf-8"', 
                array(
                    "Content-Type" => array(
                        "type" => "text/html",
                        "charset" => "utf-8",
                        ),
                    ),
                ),
            array('Content-Type: text/html; charset=utf-8', 
                array(
                    "Content-Type" => array(
                        "type" => "text/html",
                        "charset" => "utf-8",
                        ),
                    ),
                ),
            array('Content-Type: text/html; charset=    utf-8', 
                array(
                    "Content-Type" => array(
                        "type" => "text/html",
                        "charset" => "utf-8",
                        ),
                    ),
                ),
            );
    }

    /**
     * @dataProvider getDecodingHeader
     * @param string $url
     */
	function testDecodingHeader($header, $res)
	{
	    $ie = new InboundEmail();
	    $this->assertEquals($res,$ie->decodeHeader($header));
	}


    public function intlTextProvider()
    {
        return array(
            // commenting out windows-1256, since PHP doesn't have an easy way to detect this encoding.
//            array(
//                '7cvU3iDI5d7L7O3TIOUg1cfU7N0g3c4g5ezR1NPd5eHU3csg287a',
//                'يثشق بهقثىيس ه صاشىف فخ هىرشسفهلشفث غخع',
//                // 'windows-1256'
//            ),
//            array(
//                '7cjT7cjU0+3IwcbExNE=',
//                'يبسيبشسيبءئؤؤر',
//                // 'windows-1256'
//            ),
            array( // params related to 45059 ticket
                'GyRCJWYhPCU2TD4bKEI=',
                'ユーザ名',
                // 'ISO-2022-JP'
            ),
            array(
                '5LiN6KaB55u06KeG6ZmM55Sf5Lq655qE55y8552b',
                '不要直视陌生人的眼睛',
                // 'utf-8'
            )
        );

    }
    /**
     * @group bug45059
     * @dataProvider intlTextProvider
     * @param string $inputText - our input from the provider, base64'ed
     * @param string $expected - what our goal is
     */
    public function testConvertToUtf8($inputText, $expected)
    {
        // the email server is down, so this test doesn't work
        if (!function_exists('mb_convert_encoding')) {
            $this->markTestSkipped('Need multibyte encoding support');
        }

        $ie = new InboundEmail();
        $inputText = base64_decode($inputText);
        $this->assertEquals($expected, $ie->convertToUtf8($inputText), 'We should be able to convert to UTF-8');
    }

}