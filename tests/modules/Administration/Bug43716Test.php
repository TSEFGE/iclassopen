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
 * @brief Try to compare dates in different formats
 * @ticket 43716
 */
class Bug43716Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $backup = array();
    private $user = null;
    private $upgradeHistory = null;
    private $moreResetPatch = null;
    private $patchToCheck = null;
    private $moreResetPatchFile = '';
    private $patchToCheckFile = '';
    private $filenamePostfix = '-restore';

    /**
     * @brief create user, two patches with different dates
     * @return void
     */
    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->upgradeHistory = new UpgradeHistory();
        if (isset($GLOBALS['current_user']))
        {
            $this->backup['current_user'] = $GLOBALS['current_user'];
        }
        $GLOBALS['current_user'] = $this->user;

        $this->moreResetPatch=new stdClass();
        $this->moreResetPatch->id = 'moreResetPatch-id';
        $this->moreResetPatch->id_name = 'moreResetPatch-id_name';
        $this->moreResetPatch->name = 'moreResetPatch-name';
        $this->moreResetPatch->timestamp = mktime(0, 0, 0, 1, 1, 2006);
        $this->moreResetPatch->filename = 'moreResetPatch-filename';
        $i=0;
        do
        {
            $this->moreResetPatchFile = $this->moreResetPatch->filename.'.'.$i;
            ++$i;
        }
        while (is_file($this->moreResetPatchFile.$this->filenamePostfix));
        $f = fopen($this->moreResetPatchFile.$this->filenamePostfix, 'w+');
        fclose($f);
        $this->moreResetPatch->filename = $this->moreResetPatchFile.'.html';

        $this->patchToCheck=new stdClass();
        $this->patchToCheck->id = 'patchToCheck-id';
        $this->patchToCheck->id_name = 'patchToCheck-id_name';
        $this->patchToCheck->name = 'patchToCheck-name';
        $this->patchToCheck->filename = 'patchToCheck-filename';
        $this->patchToCheck->timestamp = mktime(23, 59, 59, 12, 25, 2004);
        $i=0;
        do
        {
            $this->patchToCheckFile = $this->patchToCheck->filename.'.'.$i;
            ++$i;
        }
        while (is_file($this->patchToCheckFile.$this->filenamePostfix));
        $f = fopen($this->patchToCheckFile.$this->filenamePostfix, 'w+');
        fclose($f);
        $this->patchToCheck->filename = $this->patchToCheckFile.'.html';
    }
    /**
     * @brief formats of date and time for testing
     * @return array
     */
    public function getUninstallAvailable()
    {
        $dateFormats = array_keys($GLOBALS['sugar_config']['date_formats']);
        $timeFormats = array_keys($GLOBALS['sugar_config']['time_formats']);
        $return = array();
        foreach ($dateFormats as $dateFormat)
        {
            foreach ($timeFormats as $timeFormat) {
                $return[] = array($dateFormat, $timeFormat);
            }
        }
        return $return;
    }

    /**
     * @brief creation of two dates in local format and try to compare they through UninstallAvailable
     * @dataProvider getUninstallAvailable
     * @group 43716
     *
     * @param string $dateFormat date format
     * @param string $timeFormat time format
     * @return void
     */
	public function testUninstallAvailable($dateFormat, $timeFormat)
	{
        $this->moreResetPatch->date_entered = date($dateFormat.' '.$timeFormat, $this->moreResetPatch->timestamp);
        $this->patchToCheck->date_entered = date($dateFormat.' '.$timeFormat, $this->patchToCheck->timestamp);
        $this->user->setPreference('datef', $dateFormat);
        $this->user->setPreference('timef', $timeFormat);

        $this->assertFalse(
            $this->upgradeHistory->UninstallAvailable(array($this->moreResetPatch), $this->patchToCheck),
            'UninstallAvailable should return false'
        );
	}

    /**
     * @brief remove user and patches information
     * @return void
     */
    public function tearDown()
    {
        unlink($this->moreResetPatchFile.$this->filenamePostfix);
        unlink($this->patchToCheckFile.$this->filenamePostfix);
        unset($this->upgradeHistory);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->user);
        unset($GLOBALS['current_user']);
        foreach ($this->backup as $k => $v)
        {
            global $$k;
            $$k = $v;
            $GLOBALS[$k] = $$k;
        }
    }
}