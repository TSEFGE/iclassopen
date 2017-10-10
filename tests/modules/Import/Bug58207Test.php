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


require_once('modules/Import/sources/ImportFile.php');

/**
 * Test checks Import when not using UTF-8 encoding
 * 
 * @ticket 58207
 * @author avucinic
 *
 */
class Bug58207Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $_file;
    private $_sugarConfig;

    public function setUp()
    {
        // SJIS encoded Japanese CSV
        $this->_file = 'tests/modules/Import/Bug58207Test.csv';
        
        global $sugar_config;
        $this->_sugarConfig = $sugar_config; 
        $sugar_config['default_export_charset'] = "SJIS";

        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        global $sugar_config;
        $sugar_config = $this->_sugarConfig;
    }

    /**
     * Import a SJIS encoded file, and check if getNextRow() properly
     * converts all the data into UTF-8
     */
    public function testFileImportEncoding()
    {
        $importFile = new ImportFile($this->_file, ',', '"', FALSE, FALSE);

        $row = $importFile->getNextRow();

        // Hardcode some Japanese strings
        $this->assertEquals('名前', $row[0]);
        $this->assertEquals('請求先郵便番号', $row[10]);
        $this->assertEquals('年間売上', $row[20]);
        $this->assertEquals('チームID', $row[30]);
    }

}