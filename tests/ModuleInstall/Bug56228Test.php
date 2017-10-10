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



require_once('ModuleInstall/ModuleInstaller.php');


class Bug56228Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $fileNames;

    /**
     * @group 56228
     */
    public function testUninstallNoFilesModStr()
    {
        $files = array(
            'custom/Extension/modules/relationships/language/Bugs.php' => "<?php\n//THIS FILE IS AUTO GENERATED, DO NOT MODIFY\n"
                . "\$mod_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE'] = 'Opportunities';\n",
            'custom/Extension/modules/relationships/language/Opportunities.php' => "<?php\n//THIS FILE IS AUTO GENERATED, DO NOT MODIFY\n"
                . "\$mod_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE'] = 'Opportunities';\n"
                . "\$mod_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE'] ='Bugs';",
        );

        $labelDefinitions = array(
            array(
                'module' => 'Bugs',
                'system_label' => 'LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE',
                'display_label' => 'Opportunities',
            ),
            array(
                'module' => 'Opportunities',
                'system_label' => 'LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE',
                'display_label' => 'Bugs',
            ),
        );

        $this->writeTestFiles($files);
        $this->uninstallLabels($labelDefinitions);

        $this->assertFileNotExists(
            'custom/Extension/modules/relationships/language/Bugs.php',
            'Not deleted empty file'
        );
        $this->assertFileNotExists(
            'custom/Extension/modules/relationships/language/Opportunities.php',
            'Not deleted empty file'
        );

    }

    /**
     * @group 56228
     */
    public function testUninstallWithFilesModStr()
    {
        $files = array(
            'custom/Extension/modules/relationships/language/Bugs.php' => "<?php\n//THIS FILE IS AUTO GENERATED, DO NOT MODIFY\n"
                . "\$mod_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE'] = 'Opportunities';\n"
                . "\$mod_strings['LBL_OTHER_LABEL'] = 'Other label';\n",
            'custom/Extension/modules/relationships/language/Opportunities.php' => "<?php\n//THIS FILE IS AUTO GENERATED, DO NOT MODIFY\n"
                . "\$mod_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE'] = 'Opportunities';\n"
                . "\$mod_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE'] ='Bugs';",
        );

        $labelDefinitions = array(
            array(
                'module' => 'Bugs',
                'system_label' => 'LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE',
                'display_label' => 'Opportunities',
            ),
            array(
                'module' => 'Opportunities',
                'system_label' => 'LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE',
                'display_label' => 'Bugs',
            ),
        );

        $this->writeTestFiles($files);
        $this->uninstallLabels($labelDefinitions);


        $this->assertFileNotExists('custom/Extension/modules/relationships/language/Opportunities.php');

        $this->assertFileExists('custom/Extension/modules/relationships/language/Bugs.php');
        require('custom/Extension/modules/relationships/language/Bugs.php');
        $this->assertArrayNotHasKey('LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE', $mod_strings);
        $this->assertArrayNotHasKey('LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE', $mod_strings);

    }

    /**
     * @group 56228
     */
    public function testUninstallWithFilesAppStr()
    {
        $files = array(
            'custom/Extension/modules/relationships/language/application.php' => "<?php\n//THIS FILE IS AUTO GENERATED, DO NOT MODIFY\n"
                . "\$app_list_strings['LBL_OTHER_LABEL'] = 'Other label';\n"
                . "\$app_list_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE'] = 'Opportunities';\n"
                . "\$app_list_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE'] ='Bugs';",
        );

        $labelDefinitions = array(
            array(
                'module' => 'application',
                'system_label' => 'LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE',
                'display_label' => 'Opportunities',
            ),

            array(
                'module' => 'application',
                'system_label' => 'LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE',
                'display_label' => 'Bugs',
            ),

        );

        $this->writeTestFiles($files);
        $this->uninstallLabels($labelDefinitions);

        $this->assertFileExists('custom/Extension/modules/relationships/language/application.php');
        require('custom/Extension/modules/relationships/language/application.php');
        $this->assertArrayNotHasKey('LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE', $app_list_strings);
        $this->assertArrayNotHasKey('LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE', $app_list_strings);

    }

    /**
     * @group 56228
     */
    public function testUninstallNoFilesAppStr()
    {
        $files = array(
            'custom/Extension/modules/relationships/language/application.php' => "<?php\n//THIS FILE IS AUTO GENERATED, DO NOT MODIFY\n"
                . "\$app_list_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE'] = 'Opportunities';\n"
                . "\$app_list_strings['LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE'] ='Bugs';",
        );

        $labelDefinitions = array(
            array(
                'module' => 'application',
                'system_label' => 'LBL_OPPORTUNITIES_BUGS_1_FROM_OPPORTUNITIES_TITLE',
                'display_label' => 'Opportunities',
            ),
            array(
                'module' => 'application',
                'system_label' => 'LBL_OPPORTUNITIES_BUGS_1_FROM_BUGS_TITLE',
                'display_label' => 'Bugs',
            ),
        );

        $this->writeTestFiles($files);
        $this->uninstallLabels($labelDefinitions);

        $this->assertFileNotExists('custom/Extension/modules/relationships/language/application.php');

    }

    /**
     * @group 56228
     */
    public function testUninstallExtLabels()
    {
        $files = array(
            'custom/Extension/modules/Bugs/Ext/Language/en_us.customopportunities_bugs_1.php' => 'some text',
            'custom/Extension/modules/Opportunities/Ext/Language/en_us.customopportunities_bugs_1.php' => 'some text',
            'custom/Extension/application/Ext/Language/en_us.customopportunities_bugs_1.php' => 'some text'
        );
        $labelDefinitions = array(
            array(
                'module' => 'Bugs',
            ),
            array(
                'module' => 'Opportunities',
            ),
            array(
                'module' => 'application',
            ),
        );

        $this->writeTestFiles($files);

        $oModuleInstaller = new ModuleInstaller();
        $oModuleInstaller->id_name = 'customopportunities_bugs_1';
        $oModuleInstaller->uninstallExtLabels($labelDefinitions);

        foreach(array_keys($files) as $fileName){
            $this->assertFileNotExists($fileName);
        }

    }

    private function uninstallLabels($labelDefinitions)
    {
        $oModuleInstaller = new ModuleInstaller();
        $oModuleInstaller->uninstallLabels('custom/Extension/modules/relationships/language/', $labelDefinitions);
    }

    private function writeTestFiles($files)
    {
        foreach ($files as $fileName => $sContent) {
            $this->fileNames[] = $fileName;

            if (!file_exists($fileName)){
                mkdir_recursive(dirname($fileName));
            }
            file_put_contents($fileName, $sContent);
        }
    }

    public function tearDown()
    {
        foreach ($this->fileNames as $fileNames) {
            @unlink($fileNames);
        }

        parent::tearDown();
    }

}
