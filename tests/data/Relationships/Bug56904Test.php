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


require_once('data/Link2.php');

/**
 * @ticket 56904
 */
class Bug56904Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Ensures that relationships for all related beans are removed and return
     * value is calculated based on the related beans remove results
     *
     * @param array $results
     * @param bool $expected
     * @dataProvider getRemoveResults
     */
    public function testAllRelationsAreRemoved(array $results, $expected)
    {
        $relationship = $this->getRelationshipMock($results);
        $link         = $this->getLinkMock(count($results));

        $result = $relationship->removeAll($link);
        if ($expected)
        {
            $this->assertTrue($result);
        }
        else
        {
            $this->assertFalse($result);
        }
    }

    /**
     * Creates mock of SugarRelationship object which will return specified
     * results on on consecutive SugarRelationship::remove() calls
     *
     * @param array $results
     * @return SugarRelationship
     */
    protected function getRelationshipMock(array $results)
    {
        $mock = $this->getMockForAbstractClass('SugarRelationship');
        $mock->expects($this->exactly(count($results)))
            ->method('remove')
            ->will(
                call_user_func_array(array($this, 'onConsecutiveCalls'), $results)
            );
        return $mock;
    }

    /**
     * Creates mock of Link2 object with specified number of related beans
     *
     * @param int $count
     * @return Link2
     */
    protected function getLinkMock($count)
    {
        if ($count > 0)
        {
            $bean  = new SugarBean();
            $bean->id = 'Bug56904Test';
            $beans = array_fill(0, $count, $bean);
        }
        else
        {
            $beans = array();
        }

        $mock = $this->getMock('Link2', array('getSide', 'getFocus', 'getBeans'), array(), '', false);
        $mock->expects($this->any())
            ->method('getSide')
            ->will($this->returnValue(REL_LHS));
        $mock->expects($this->any())
            ->method('getFocus')
            ->will($this->returnValue(new SugarBean()));
        $mock->expects($this->any())
            ->method('getBeans')
            ->will($this->returnValue($beans));
        return $mock;
    }

    /**
     * Provides results that should be returned by SugarRelationship::remove()
     * calls and expected result of SugarRelationship::removeAll()
     *
     * @return array
     */
    public static function getRemoveResults()
    {
        return array(
            array(
                array(), true,
            ),
            array(
                array(true), true,
            ),
            array(
                array(false), false,
            ),
            array(
                array(true, false), false,
            ),
            array(
                array(false, true), false,
            ),
            array(
                array(false, false), false,
            ),
            array(
                array(true, true), true,
            ),
        );
    }
}
