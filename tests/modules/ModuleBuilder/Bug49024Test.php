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


require_once 'modules/ModuleBuilder/parsers/relationships/OneToOneRelationship.php' ;
require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;
require_once 'modules/ModuleBuilder/parsers/relationships/UndeployedRelationships.php' ;

/**
 * Bug 49024
 * Relationships Created in Earlier Versions Cause Conflicts and AJAX Errors After Upgrade 
 * 
 * @ticket 49024
 */
class Bug49024Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $objOneToOneRelationship;

    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->objOneToOneRelationship = $this->getMockBuilder('OneToOneRelationship')
            ->disableOriginalConstructor()
            ->setMethods(array('getDefinition'))
            ->getMock();

        $this->objOneToOneRelationship->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue(array(
                    'lhs_module' => 'lhs_module',
                    'rhs_module' => 'rhs_module'
                )));

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($this->objOneToOneRelationship);
    }

    /**
     * @group 49024
     */
    public function testDeployedRelationshipsUniqName()
    {
        $objDeployedRelationships = $this->getMockBuilder('DeployedRelationshipsBug49024Test')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getRelationshipList'))
            ->getMock();

        $objDeployedRelationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue(array()));

        $name = $objDeployedRelationships->getUniqueNameBug49024Test($this->objOneToOneRelationship);
        $this->assertEquals('lhs_module_rhs_module_1', $name);
    }

    /**
     * @group 49024
     */
    public function testDeployedRelationshipsUniqName2()
    {
        $objDeployedRelationships = $this->getMockBuilder('DeployedRelationshipsBug49024Test')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getRelationshipList'))
            ->getMock();

        $objDeployedRelationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue(array(
            'lhs_module_rhs_module_1' => true, 'lhs_module_rhs_module_2' => true
        )));

        $name = $objDeployedRelationships->getUniqueNameBug49024Test($this->objOneToOneRelationship);
        $this->assertEquals('lhs_module_rhs_module_3', $name);
    }

    /**
     * @group 49024
     */
    public function testUndeployedRelationshipsUniqName()
    {
        $objUndeployedRelationships = $this->getMockBuilder('UndeployedRelationshipsBug49024Test')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getRelationshipList'))
            ->getMock();

        $objUndeployedRelationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue(array()));

        $name = $objUndeployedRelationships->getUniqueNameBug49024Test($this->objOneToOneRelationship);
        $this->assertEquals('lhs_module_rhs_module', $name);
    }

    /**
     * @group 49024
     */
    public function testUndeployedRelationshipsUniqName2()
    {
        $objUndeployedRelationships = $this->getMockBuilder('UndeployedRelationshipsBug49024Test')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'getRelationshipList'))
            ->getMock();

        $objUndeployedRelationships->expects($this->any())
            ->method('getRelationshipList')
            ->will($this->returnValue(array(
                'lhs_module_rhs_module' => true, 'lhs_module_rhs_module_1' => true, 'lhs_module_rhs_module_2' => true
            )));

        $name = $objUndeployedRelationships->getUniqueNameBug49024Test($this->objOneToOneRelationship);
        $this->assertEquals('lhs_module_rhs_module_3', $name);
    }
}

class DeployedRelationshipsBug49024Test extends DeployedRelationships
{
    public function getUniqueNameBug49024Test ($relationship)
    {
        return $this->getUniqueName($relationship);
    }
}

class UndeployedRelationshipsBug49024Test extends UndeployedRelationships
{
    public function getUniqueNameBug49024Test ($relationship)
    {
        return $this->getUniqueName($relationship);
    }
}