<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once('modules/Import/Importer.php');
require_once('modules/Import/maps/ImportMap.php');

/**
 * Bug 61172
 * saved import field mapping didn't work
 *
 * @ticket 61172
 * @author ekolotaev@sugarcrm.com
 *
 */
class Bug61172Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $testFile;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));
        $this->testFile = 'tests/modules/Tasks/Bug61172Test.csv';

        $_REQUEST = array(
            // user choice values
            'has_header' => 'off',
            'firstrow' => base64_encode(serialize(array('0' => 'Foo', '1' => 'Status'))),
            'colnum_0'    => 'foo',
            'colnum_1'    => 'status',
            'columncount' => '2',
            'custom_enclosure' => '&quot;',
            'custom_delimiter' => ',',
            'source' => 'csv',
            'save_map_as' => 'Bug61172TestSaveMap',

            // import settings values
            'importlocale_charset' => 'UTF-8',
            'importlocale_currency' => '-99',
            'importlocale_dateformat' => 'd/m/Y',
            'importlocale_dec_sep' => '.',
            'importlocale_default_currency_significant_digits' => '2',
            'importlocale_default_locale_name_format' => 's f l',
            'importlocale_num_grp_sep' => ',',
            'importlocale_timeformat' => 'H:i',
            'importlocale_timezone' => 'Europe/Helsinki',
            'import_module' => 'Leads',
        );
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        $_REQUEST = array();
    }

    public function testSaveMappingFileSavesNumberFieldAssociationCorrectly()
    {
        $lead = new Lead();
        $importSource = new ImportFile($this->testFile, ',', '', false);
        $importer = new Bug61172TestImporterMock($importSource, $lead);

        $importer->saveMappingFile();
        $mappingFile = new ImportMap();
        $mappingFile->retrieve_by_string_fields(array('name' => $_REQUEST['save_map_as']));

        $this->assertNotEmpty($mappingFile->content);

        $contentFields = explode('&', $mappingFile->content);
        $this->assertContains('1=status', $contentFields, "Field status should be associated with #1");

        $mappingFile->mark_deleted($mappingFile->id);
    }
}

class Bug61172TestImporterMock extends Importer
{
    public function saveMappingFile()
    {
        parent::saveMappingFile();
    }
}

