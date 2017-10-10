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


require_once('include/ListView/ListViewFacade.php');
require_once('modules/Import/views/view.last.php');

class Bug48496Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    var $backup_config;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['module']='Imports';
        $_REQUEST['module']='Imports';
        $_REQUEST['import_module']='Accounts';
        $_REQUEST['action']='last';
        $_REQUEST['type']='';
        $_REQUEST['has_header'] = 'off';
        sugar_touch('upload/import/status_'.$GLOBALS['current_user']->id.'.csv');
    }

    public function tearDown()
    {
        unlink('upload/import/status_'.$GLOBALS['current_user']->id.'.csv');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['module']);
        unset($_REQUEST['module']);
        unset($_REQUEST['import_module']);
        unset($_REQUEST['action']);
        unset($_REQUEST['type']);
        unset($_REQUEST['has_header']);
    }

    public function testQueryDoesNotContainDuplicateUsersLastImportClauses() {
        global $current_user;

        $params = array(
            'custom_from' => ', users_last_import',
            'custom_where' => " AND users_last_import.assigned_user_id = '{$current_user->id}'
                AND users_last_import.bean_type = 'Account'
                AND users_last_import.bean_id = accounts.id
                AND users_last_import.deleted = 0
                AND accounts.deleted = 0",
        );

        $seed = SugarModule::get('Accounts')->loadBean();

        $lvfMock = $this->getMock('ListViewFacade', array('setup', 'display', 'build'), array($seed, 'Accounts'));

        $lvfMock->expects($this->any())
            ->method('setup')
            ->with($this->anything(),
            '',
            $params,
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything());

        $viewLast = new ImportViewLastWrap();
        $viewLast->init($seed);
        $viewLast->lvf = $lvfMock;

        $viewLast->publicGetListViewResults();
    }

}

class ImportViewLastWrap extends ImportViewLast {
    public function publicGetListViewResults() {
        return $this->getListViewResults();
    }
}
