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


require_once('modules/ModuleBuilder/controller.php');
require_once('modules/ProjectTask/views/view.list.php');

/**
 * Bug #50575
 * Query Failure when searching in Project Tasks list view, using Accounts field created from Relationship
 *
 * @author asokol@sugarcrm.com
 * @ticket 50575
 */

class Bug50575Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    protected $_project;
    protected $_projectTask;
    protected $_account;

    protected $_savedSearchDefs;
    protected $_savedSearchFields;

    protected $relationship;
    protected $relationships;

    protected $_localSearchFields = array (
        'ProjectTask' => array(
            'name' => array (
                'query_type' => 'default',
            ),
            'project_name' => array (
                'query_type' => 'default',
                'db_field' => array (
                    0 => 'project.name',
                ),
            ),
        )
    );

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        parent::setUp();
        $this->relationships = new DeployedRelationships('Products');
        $definition = array(
            'lhs_module' => 'Accounts',
            'relationship_type' => 'one-to-many',
            'rhs_module' => 'ProjectTask'
        );
        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', array(
            'Accounts',
            'ProjectTask'
        ));

        $searchDefs = array(
                'layout' => array(
                    'advanced_search' => array(
                        $this->relationship->getName() . '_name' => array (
                            'type' => 'relate',
                            'link' => true,
                            'label' => '',
                            'id' => strtoupper($this->relationship->getJoinKeyLHS()),
                            'width' => '10%',
                            'default' => true,
                            'name' => $this->relationship->getName() . '_name',
                        ),
                    )
                ),
                'templateMeta' => array (
                    'maxColumns' => '3',
                    'maxColumnsBasic' => '4',
                    'widths' => array (
                        'label' => '10',
                        'field' => '30',
                    ),
                ),
        );
        // Add new field to advanced search layout
        if(file_exists("custom/modules/ProjectTask/metadata/searchdefs.php"))
        {
            $this->_savedSearchDefs = file_get_contents("custom/modules/ProjectTask/metadata/searchdefs.php");
        }

        write_array_to_file("searchdefs['ProjectTask']", $searchDefs, 'custom/modules/ProjectTask/metadata/searchdefs.php');

        if(file_exists("modules/ProjectTask/metadata/SearchFields.php"))
        {
            $this->_savedSearchFields = file_get_contents("modules/ProjectTask/metadata/SearchFields.php");
        }

        write_array_to_file("searchFields['ProjectTask']", $this->_localSearchFields['ProjectTask'], 'modules/ProjectTask/metadata/SearchFields.php');

        // Creates linked test account, project and project task
        $this->_project = SugarTestProjectUtilities::createProject();
        $this->_account = SugarTestAccountUtilities::createAccount();

        $projectTaskData = array (
            'project_id' => $this->_project->id,
            'parent_task_id' => '',
            'project_task_id' => '1',
            'percent_complete' => '0',
            'name' => 'Test Task 1',
            'duration_unit' => 'Days',
            'duration' => '1',
        );

        $this->_projectTask = SugarTestProjectTaskUtilities::createProjectTask($projectTaskData);
        $this->_projectTask->{$this->relationship->getName()}->add($this->_account);
        $this->_projectTask->save();
    }

    public function tearDown()
    {
        if(!empty($this->_savedSearchDefs))
        {
            file_put_contents("custom/modules/ProjectTask/metadata/searchdefs.php", $this->_savedSearchDefs);
        }
        else
        {
            @unlink("custom/modules/ProjectTask/metadata/searchdefs.php");
        }

        if(!empty($this->_savedSearchFields))
        {
            file_put_contents("modules/ProjectTask/metadata/SearchFields.php", $this->_savedSearchFields);
        }
        else
        {
            @unlink("modules/ProjectTask/metadata/SearchFields.php");
        }
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();
        parent::tearDown();
        SugarCache::$isCacheReset = false;
        SugarTestHelper::tearDown();
        $GLOBALS['reload_vardefs'] = true;
        $bean = new ProjectTask();
        unset($GLOBALS['reload_vardefs']);
    }

    /**
     * Test checks if advanced search provides correct result (correct SQL query)
     * @group 50575
     */
    public function testCustomAdvancedSearch()
    {
        $_REQUEST = $_POST = array(
            "module" => "ProjectTask",
            "action" => "index",
            "searchFormTab" => "advanced_search",
            "displayColumns" => "NAME|PROJECT_NAME",
            "query" => "true",
            $this->relationship->getName(). '_name_advanced' => $this->_account->name,
            "button" => "Search",
        );

        $vl = new ProjectTaskViewList();
        $vl->bean = $this->_projectTask;
        $GLOBALS['module'] = 'ProjectTask';
        $vl->module = 'ProjectTask';

        $this->expectOutputRegex("/(" . $this->_project->name . ")/");
        $vl->display();
    }
}
