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

 
require_once 'include/SearchForm/SugarSpot.php';

class SugarSpotTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }
    
    public function tearDown()
    {
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 41236
     */
    public function testSearchGrabsModuleDisplayName() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('moduleList',array('Foo'=>'Bar'));
        $langpack->save();
        
        $result = array(
            'Foo' => array(
                'data' => array(
                    array(
                        'ID' => '1',
                        'NAME' => 'recordname',
                        ),
                    ),
                'pageData' => array(
                    'offsets' => array(
                        'total' => 1,
                        'next' => 0,
                        ),
                    'bean' => array(
                        'moduleDir' => 'Foo',
                        ),
                    ),
                ),
                'readAccess' => true,
            );
        
        $sugarSpot = $this->getMock('SugarSpot', array('_performSearch'));
        $sugarSpot->expects($this->any())
            ->method('_performSearch')
            ->will($this->returnValue($result));
            
        $returnValue = $sugarSpot->searchAndDisplay('','');

        $this->assertRegExp('/Bar/',$returnValue);
        $this->assertRegExp('/DCMenu\.showQuickView\s*?\(\'Foo\'/',$returnValue);
    }

    /**
     * @ticket 43080
     */
    public function testSearchGrabsMore() 
    {
        $app_strings = return_application_language($GLOBALS['current_language']); 
        $this->assertTrue(array_key_exists('LBL_SEARCH_MORE', $app_strings));

        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppString('LBL_SEARCH_MORE', 'XXmoreXX');
        $langpack->save();
        
        $result = array(
            'Foo' => array(
                'data' => array(
                    array(
                        'ID' => '1',
                        'NAME' => 'recordname',
                        ),
                    ),
                'pageData' => array(
                    'offsets' => array(
                        'total' => 100,
                        'next' => 0,
                        ),
                    'bean' => array(
                        'moduleDir' => 'Foo',
                        ),
                    ),
                ),
                'readAccess' => true,
            );
        
        $sugarSpot = $this->getMock('SugarSpot', array('_performSearch'));
        $sugarSpot->expects($this->any())
            ->method('_performSearch')
            ->will($this->returnValue($result));
            
        $returnValue = $sugarSpot->searchAndDisplay('','');

        $this->assertNotContains('(99 more)',$returnValue);
        $this->assertContains('(99 XXmoreXX)',$returnValue);
    }


    /**
     * providerTestSearchType
     * This is the provider function for testFilterSearchType
     *
     */
    public function providerTestSearchType()
    {
        return array(
              array('phone', '777', true),
              array('phone', '(777)', true),
              array('phone', '%777', true),
              array('phone', '77', false),
              array('phone', '%77) 7', false),
              array('phone', '88-88-88', false),
              array('int', '1', true),
              array('int', '1.0', true),
              array('int', '.1', true),
              array('int', 'a', false),
              array('decimal', '1.0', true),
              array('decimal', '1', true),
              array('decimal', '1,000', true),
              array('decimal', 'aaaaa', false),
              array('float', '1.0', true),
              array('float', '1', true),
              array('float', '1,000', true),
              array('float', 'aaaaa', false),
              array('id', '1', false),
              array('datetime', '2011-01-01 10:10:10', false),
              array('date', '2011-01-01', false),
              array('bool', true, false),
              array('bool', false, false),
              array('foo', 'foo', true),
        );
    }

    /**
     * testFilterSearchType
     * This function uses a provider to test the filter search type
     * @dataProvider providerTestSearchType
     */
    public function testFilterSearchType($type, $query, $expected)
    {
        $sugarSpot = new Bug50484SugarSpotMock();
        $this->assertEquals($expected, $sugarSpot->filterSearchType($type, $query),
            ('SugarSpot->filterSearchType expected type ' . $type . ' with value ' . $query . ' to return ' . $expected ? 'true' : false));
    }

}


class Bug50484SugarSpotMock extends SugarSpot
{
    public function filterSearchType($type, $query)
    {
        return parent::filterSearchType($type, $query);
    }
}