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


/**
 * Bug51721Test.php
 *
 */
require_once('modules/UpgradeWizard/uw_utils.php');
require_once('modules/Administration/UpgradeHistory.php');

class Bug51721Test extends Sugar_PHPUnit_Framework_OutputTestCase
{

private $new_upgrade;
private $new_upgrade2;

public function setUp()
{
    global $sugar_config;
    $sugar_config['cache_dir'] = 'cache/';

    $GLOBALS['db']->query("DELETE FROM upgrade_history WHERE name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3'");

    $this->new_upgrade = new UpgradeHistory();
    $this->new_upgrade->filename = 'cache/upload/upgrade/temp/Bug51721Test.zip';
    $this->new_upgrade->md5sum = md5('cache/upload/upgrade/temp/Bug51721Test.zip');
    $this->new_upgrade->type = 'patch';
    $this->new_upgrade->version = '6.4.3';
    $this->new_upgrade->status = "installed";
    $this->new_upgrade->name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3';
    $this->new_upgrade->description = 'Silent Upgrade was used to upgrade the instance';
    $this->new_upgrade->save();

    $this->new_upgrade2 = new UpgradeHistory();
    $this->new_upgrade2->filename = 'cache//upload/upgrade/temp/Bug51721Test.zip';
    $this->new_upgrade2->md5sum = md5('cache//upload/upgrade/temp/Bug51721Test.zip');
    $this->new_upgrade2->type = 'patch';
    $this->new_upgrade2->version = '6.4.3';
    $this->new_upgrade2->status = "installed";
    $this->new_upgrade2->name = 'SugarEnt-Upgrade-6.3.x-to-6.4.3';
    $this->new_upgrade2->description = 'Silent Upgrade was used to upgrade the instance';
    $this->new_upgrade2->save();
}

public function tearDown()
{
    $GLOBALS['db']->query("DELETE FROM upgrade_history WHERE id IN ('{$this->new_upgrade->id}', '{$this->new_upgrade2->id}')");
}

public function testRepairUpgradeHistoryTable()
{
    repairUpgradeHistoryTable();
    $file = $GLOBALS['db']->getOne("SELECT filename FROM upgrade_history WHERE id = '{$this->new_upgrade->id}'");
    $this->assertEquals('upload/upgrade/temp/Bug51721Test.zip', $file);
    $file = $GLOBALS['db']->getOne("SELECT filename FROM upgrade_history WHERE id = '{$this->new_upgrade2->id}'");
    $this->assertEquals('upload/upgrade/temp/Bug51721Test.zip', $file);
}

}