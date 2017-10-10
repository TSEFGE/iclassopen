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

require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/utils/ConnectorUtils.php');

/*
 * This test makes sure that connectors::getConnectors() can handle a badly formed custom metadata file that is either
 * missing the connectors array or the array has been defined as a string
 * @ticket 50800
 */
class Bug50800Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $custom_path = 'custom/modules/Connectors/metadata';
    var $custom_contents;

    function setUp() {
        SugarTestHelper::setUp('app_strings');
        if(file_exists($this->custom_path.'/connectors.php'))
        {
           $this->custom_contents = file_get_contents($this->custom_path.'/connectors.php');
           unlink($this->custom_path.'/connectors.php');
        } else {
            mkdir_recursive($this->custom_path);
        }
    }
    
    function tearDown() {
        //remove connector file
        unlink($this->custom_path.'/connectors.php');

        if(!empty($this->custom_contents))
        {
           file_put_contents($this->custom_path.'/connectors.php', $this->custom_contents);
        }
        SugarTestHelper::tearDown();
    }
    
    function testConnectorFailsStringGracefully()
    {
        //now write a connector file with a string instead of an array for the connector var
        file_put_contents($this->custom_path.'/connectors.php',"<?php\n \$connector = 'Connector String ';");

        //create the connector and call getConnectors
        $cu = new ConnectorUtils();
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $cu->getConnectors(true), 'ConnectorsUtils::getConnectors() failed to return an array when $connectors is a string');
    }

    function testConnectorFailsNullGracefully()
    {
        //now write a connector file with missing array info instead of an array for the connector var
        file_put_contents($this->custom_path.'/connectors.php',"<?php\n ");

        //create the connector and call getConnectors
        $cu = new ConnectorUtils();
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $cu->getConnectors(true), 'ConnectorsUtils::getConnectors() failed to return an array when connectors array was missing. ');
    }
}
