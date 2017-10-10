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

/**
 * Bug #61144: remove doc link in field: Related Document, if linked doc is got deleted from the system
 *
 * @ticket 61144
 */

require_once('modules/Documents/Document.php');

class Bug61144Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $parent_doc;
    protected $relate_doc;

    /**
     * @var DocumentRevision
     */
    protected $revision;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->relate_doc = new Document();
        $this->relate_doc->document_name = 'Relate doc';
        $this->relate_doc->save();

        $this->parent_doc = new Document();
        $this->parent_doc->document_name = 'Parent doc';
        $this->parent_doc->related_doc_id = $this->relate_doc->id;
        $this->parent_doc->save();

        $this->revision = new DocumentRevision();
        $this->revision->revision = 1;
        $this->revision->doc_id = $this->relate_doc->id;
        $this->revision->save();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        $GLOBALS['db']->query("DELETE FROM documents WHERE id in ('{$this->relate_doc->id}', '{$this->parent_doc->id}')");
        $GLOBALS['db']->query("DELETE FROM {$this->revision->table_name} WHERE id in ('{$this->revision->id}')");
    }

    /**
     * @group 61144
     */
    public function testDeleteRelateDocument()
    {
        $this->relate_doc->mark_deleted($this->relate_doc->id);
        $this->parent_doc->fill_in_additional_detail_fields();
        $this->assertEmpty($this->parent_doc->related_doc_name);
    }

    /**
     * @group 61144
     */
    public function testRealteDocumentExist()
    {
        $this->parent_doc->fill_in_additional_detail_fields();
        $this->assertEquals('Relate doc', $this->parent_doc->related_doc_name);
    }

    /**
     * @group 61144
     */
    public function testDeleteRelateDocumentRevision()
    {
        $this->revision->mark_deleted($this->revision->id);
        $actual = $this->revision->get_document_revision_name($this->revision->id);
        $this->assertEmpty($actual, 'Deleted data is returned');
    }

    /**
     * @group 61144
     */
    public function testRealteDocumentRevision()
    {
        $actual = $this->revision->get_document_revision_name($this->revision->id);
        $this->assertEquals($this->revision->revision, $actual, 'Revision is incorrect');
    }
}
