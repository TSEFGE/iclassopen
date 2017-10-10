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


require_once 'modules/InboundEmail/InboundEmail.php';
class AttachmentHeaderTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $ie = null;

    public function setUp()
    {
        $this->ie = new InboundEmail();
    }

    /**
     * @param $param -> "dparameters" | "parameters"
     * @param $a -> attribute
     * @param $v -> value
     * @return stdClass:  $obj->attribute = $a, $obj->value = $v
     */
    protected function _convertToObject($param,$a,$v)
    {
        $obj = new stdClass;
        $obj->attribute = $a;
        $obj->value = $v;

        $outer = new stdClass;
        $outer->parameters = ($param == 'parameters') ? array($obj) : array();
        $outer->isparameters = !empty($outer->parameters);
        $outer->dparameters = ($param == 'dparameters') ? array($obj) : array();
        $outer->isdparameters = !empty($outer->dparameters);

        return $outer;
    }

    public function contentParameterProvider()
    {
        return array(
            // pretty standard dparameters
            array(
                $this->_convertToObject('dparameters','filename','test.txt'),
                'test.txt'
            ),

            // how about a regular parameter set
            array(
                $this->_convertToObject('parameters','name','bonus.txt'),
                'bonus.txt'
            )
        );
    }

    /**
     * @group bug57309
     * @dataProvider contentParameterProvider
     * @param array $in - the part parameters -> will convert to object in test method
     * @param string $expected - the name digested from the parameters
     */
    public function testRetrieveAttachmentNameFromStructure($in, $expected)
    {
        $this->assertEquals($expected, $this->ie->retrieveAttachmentNameFromStructure($in),  'We did not get the attachmentName');
    }
}
