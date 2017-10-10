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


require_once('data/SugarBean.php');
require_once('modules/Contacts/Contact.php');
require_once('include/SubPanel/SubPanel.php');
require_once('include/SubPanel/SubPanelDefinitions.php');

/**
 * @itr 27836
 */
class ITR27836Test extends Sugar_PHPUnit_Framework_TestCase
{   	
    protected $bean;

	public function setUp()
	{
	    global $moduleList, $beanList, $beanFiles;
        require('include/modules.php');
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = new Contact();
	}

	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

  		require_once('ModuleInstall/ModuleInstaller.php');
  		$moduleInstaller = new ModuleInstaller();
  		$moduleInstaller->silent = true; // make sure that the ModuleInstaller->log() function doesn't echo while rebuilding the layoutdefs
  		$moduleInstaller->rebuild_layoutdefs();
	}


    public function subpanelProvider()
    {
        return array(
            //Hidden set to true

            array(
                'data' => array(
                    'testpanel' => array(
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => array(),
                    ),
                    'default_hidden' => true,
                    'subpanel_name' => 'history',
                    'module' => 'Contacts'
                ),
            ),

            //Hidden set to false
            array
            (
                'data' => array(
                    'testpanel' => array(
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => array(),
                    ),
                    'default_hidden' => false,
                    'subpanel_name' => 'history',
                    'module' => 'Contacts'
                ),
            ),

            //Hidden not set
            array(
                'data' => array(
                    'testpanel' => array(
                        'order' => 20,
                        'sort_order' => 'desc',
                        'sort_by' => 'date_entered',
                        'type' => 'collection',
                        'top_buttons' => array(),
                    ),
                    'subpanel_name' => 'history',
                    'module' => 'Contacts'
                ),
            ),
        );
    }
    
    /**
     * testSubpanelDisplay
     *
     * @dataProvider subpanelProvider
     */
    public function testSubPanelDisplay($subpanel)
    {
        $subpanel_def = new aSubPanel("testpanel", $subpanel, $this->bean);

        if(isset($subpanel['default_hidden']) && $subpanel['default_hidden'] === true)
        {
            $this->assertTrue($subpanel_def->isDefaultHidden());
        } else {
            $this->assertFalse($subpanel_def->isDefaultHidden());
        }
    }

}
