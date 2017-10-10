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


require_once('modules/ModuleBuilder/parsers/relationships/ActivitiesRelationship.php');

/**
 * Bug #56425
 * see duplicate modules name in Report's Related Modules box
 *
 * @author mgusev@sugarcrm.com
 * @ticked 56425
 * @ticket 42169
 */
class Bug56425Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test asserts genereated labels for activities
     *
     * @param array $definition
     * @param array $expected
     * @dataProvider getDefinitions
     * @group 56425
     * @group 42169
     * @return void
     */
    public function testBuildLabels($definition, $expected)
    {
        ActivitiesRelationship56425::reset($definition['lhs_module']);
        $relationship = new ActivitiesRelationship56425($definition);
        $labels = $relationship->buildLabels();
        foreach ($labels as $label) {
            $this->assertArrayHasKey($label['module'], $expected, 'Incorrect label was generated');
            $this->assertEquals($expected[$label['module']], $label['display_label'], 'Labels are incorrect');
            unset($expected[$label['module']]);
        }
        $this->assertEmpty($expected, 'Not all labels were generated');
    }

    /**
     * Method returns definition for relationship & expected result
     *
     * @return array
     */
    public function getDefinitions()
    {
        return array(
            array(
                array(
                    'rhs_label' => 'Activities',
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship'
                ),
                array(
                    'Contacts' => 'Activities:Users',
                    'Users' => 'Activities:Contacts'
                )
            ),
            array(
                array(
                    'rhs_label' => 'Activities 123',
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship'
                ),
                array(
                    'Contacts' => 'Activities 123:Users',
                    'Users' => 'Activities 123:Contacts'
                )
            ),
            array(
                array(
                    'rhs_module' => 'Users',
                    'lhs_module' => 'Contacts',
                    'relationship_name' => 'users_contacts_relationship'
                ),
                array(
                    'Contacts' => 'Users',
                    'Users' => 'Contacts'
                )
            ),
            array(
                array(
                    'lhs_module' => 'lhs_module',
                    'lhs_label' => 'lhs_label',
                    'rhs_module' => 'rhs_module',
                    'rhs_label' => 'rhs_label',
                ),
                array(
                    'lhs_module' => 'rhs_label:rhs_module',
                    'rhs_module' => 'rhs_label:lhs_module'
                )
            )
        );
    }
}

class ActivitiesRelationship56425 extends ActivitiesRelationship
{
    static public function reset($module)
    {
        self::$labelsAdded = array(
            $module => true
        );

    }
}
