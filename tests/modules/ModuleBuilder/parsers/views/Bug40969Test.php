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


require_once "modules/ModuleBuilder/parsers/views/ListLayoutMetaDataParser.php";
require_once 'modules/ModuleBuilder/parsers/views/DeployedMetaDataImplementation.php' ;

/**
 * Check ListLayoutMetaDataParser fills listviewdefs correctly for flex relate custom field to be displayed
 * in ListView layout.
 *
 * Field should contain:
 * 'related_fields' key - for data access (entity name)
 * 'id'                 - for entity id in link
 * 'dynamic_module'     - for entity module in link
 */
class Bug40969Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $vardefs =
        array(
            'name'         => array(
                                  'name'     => 'name',
                                  'vname'    => 'LBL_OPPORTUNITY_NAME',
                                  'type'     => 'name',
                                  'dbType'   => 'varchar',
                                  'required' => true,
                              ),
            'date_entered' => array(
                                  'name'  => 'date_entered',
                                  'vname' => 'LBL_DATE_ENTERED',
                                  'type'  => 'datetime',
                              ),
            'parent_name'  => array(
                                  'source'        => 'non-db',
                                  'name'          => 'parent_name',
                                  'vname'         => 'LBL_FLEX_RELATE',
                                  'type'          => 'parent',
                                  'options'       => 'parent_type_display',
                                  'type_name'     => 'parent_type',
                                  'id_name'       => 'parent_id',
                                  'parent_type'   => 'record_type_display',
                                  'id'            => 'Opportunitiesparent_name',
                                  'custom_module' => 'Opportunities',
                              ),
            'parent_id'    => array(
                                  'source'        => 'custom_fields',
                                  'name'          => 'parent_id',
                                  'vname'         => 'LBL_PARENT_ID',
                                  'type'          => 'id',
                                  'id'            => 'Opportunitiesparent_id',
                                  'custom_module' => 'Opportunities',
                              ),
            'parent_type'  => array(
                                  'required'      => false,
                                  'source'        => 'custom_fields',
                                  'name'          => 'parent_type',
                                  'vname'         => 'LBL_PARENT_TYPE',
                                  'type'          => 'parent_type',
                                  'dbType'        => 'varchar',
                                  'id'            => 'Opportunitiesparent_type',
                                  'custom_module' => 'Opportunities',
                              ),
        );

    /**
     * @var array
     */
    public $originalVardefs =
        array(
            'name'         => array(
                                  'width'   => 30,
                                  'label'   => 'LBL_LIST_OPPORTUNITIES_NAME',
                                  'link'    => true,
                                  'default' => true,
                              ),
            'dete_entered' => array(
                                  'width'   => 10,
                                  'label'   => 'LBL_DATE_ENTERED',
                                  'default' => true,
                              ),
        );

    public function setUp()
    {
        $_POST = array(
                     'group_0' => array('name', 'date_entered', 'parent_name'),
                 );
    }

    public function tearDown()
    {
        $_POST = array();
    }

    public function testCustomFlexFieldListViewDefs()
    {
        $methods = array('getFielddefs', 'getOriginalViewdefs', 'getViewdefs');

        // Mock ListLayoutMetaDataParser Meta Implementation and make it return test values
        $implementation = $this->getMock('DeployedMetaDataImplementation', $methods, array(), '', false);

        $implementation->expects($this->any())->method('getFielddefs')->will($this->returnValue($this->vardefs));
        $implementation->expects($this->any())->method('getOriginalViewdefs')->will($this->returnValue($this->originalVardefs));
        $implementation->expects($this->any())->method('getViewdefs')->will($this->returnValue($this->originalVardefs));

        $metaParser =  new Bug40969ListLayoutMetaDataParser($implementation, $this->vardefs);

        $metaParser->testBug40969();

        // Assert Flex Relate field contain required listview defs to be correctly displayed
        $this->assertArrayHasKey('parent_name', $metaParser->_viewdefs);
        $this->assertArrayHasKey('dynamic_module', $metaParser->_viewdefs['parent_name']);
        $this->assertArrayHasKey('id', $metaParser->_viewdefs['parent_name']);
        $this->assertArrayHasKey('link', $metaParser->_viewdefs['parent_name']);
        $this->assertTrue($metaParser->_viewdefs['parent_name']['link']);
        $this->assertArrayHasKey('related_fields', $metaParser->_viewdefs['parent_name']);
        $this->assertEquals(array('parent_id', 'parent_type'), $metaParser->_viewdefs['parent_name']['related_fields']);
    }

}

/**
 * Helper class to access protected "_populateFromRequest" method
 */
class Bug40969ListLayoutMetaDataParser extends ListLayoutMetaDataParser
{
    /**
     * @var DeployedMetaDataImplementation
     */
    public $implementation;

    public function __construct($implementation)
    {
        $this->implementation = $implementation;

        $this->_viewdefs = array_change_key_case($this->implementation->getViewdefs());
        $this->_fielddefs = $this->implementation->getFielddefs() ;
    }

    public function testBug40969()
    {
        return $this->_populateFromRequest();
    }

}
