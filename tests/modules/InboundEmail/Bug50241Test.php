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
 * @ticket 50241
 */
class Bug50241Test extends Sugar_PHPUnit_Framework_TestCase
{

	protected $ie = null;

	public function setUp()
    {
		$this->ie = new InboundEmail();
	}

	function testEmailCleanup()
	{
	    $inStr=<<<EOS
<head>
<style><!--
.hmmessage P
{
margin:0px;
padding:0px
}
body.hmmessage
{
font-size: 10pt;
font-family:Tahoma
}
--></style></head>
<body class='hmmessage'><div dir='ltr'>
<SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">hello, <o:p></o:p></SPAN><BR>
<SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">i recently got Batman Arkham City and tried to get catwoman as an add-on character but when i put the code in it said that my code had already been used. <o:p></o:p></SPAN><BR>
<SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">what can i do, so that i can play catwoman?<o:p></o:p></SPAN><BR>
 <BR> </div></body>
</html>
EOS;

$outStr=<<<EOS
<div dir="ltr">
<span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">hello, </span><p></p><br /><span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">i recently got Batman Arkham City and tried to get catwoman as an add-on character but when i put the code in it said that my code had already been used. </span><p></p><br /><span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">what can i do, so that i can play catwoman?</span><p></p><br /><br /></div>
EOS;

	    $actual = SugarCleaner::cleanHtml($inStr);

	    // Normalize the line endings - Bug #51227
	    $outStr = str_replace("\r\n", "\n", $outStr);
	    $actual = str_replace("\r\n", "\n", $actual);
        $this->assertEquals(trim($outStr),trim($actual));
	}
}