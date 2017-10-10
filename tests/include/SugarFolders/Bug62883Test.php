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


require_once('include/SugarFolders/SugarFolders.php');
require_once('modules/Emails/EmailUI.php');

/**
 * Bug #62883
 * upgrade from 6.4.5 to 6.5.x, does not have "My Archived Email" folder in Emails module
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 62883
 */
class Bug62883Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $folder = null;

    public function setUp()
    {
        global $current_user, $currentModule;

        SugarTestHelper::setUp('mod_strings', array('Emails'));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        $GLOBALS['db']->query(
            "DELETE FROM folders_subscriptions WHERE assigned_user_id='{$GLOBALS['current_user']->id}'"
        );
        $GLOBALS['db']->query(
            "DELETE FROM folders WHERE created_by='{$GLOBALS['current_user']->id}' OR name='Bug62883Test'"
        );
        $GLOBALS['db']->query(
            "DELETE FROM folders_subscriptions WHERE assigned_user_id='{$GLOBALS['current_user']->id}'"
        );

        SugarTestHelper::tearDown();
    }

    /**
     * Test to ensure that for a new user, the My Email, My Drafts, Sent Email, etc. folders can be retrieved.
     *
     */
    public function testGetUserFolders()
    {
        $foldersId = array();

        $emailUI = new EmailUI();
        $emailUI->preflightUser($GLOBALS['current_user']);
        $rootNode = new ExtNode('', '');

        $this->createNewSugarFolder();
        $ret = $this->folder->getUserFolders($rootNode, "", $GLOBALS['current_user'], true);

        foreach ($ret as $childFolder) {
            array_push($foldersId, $childFolder['id']);
        }

        $this->clearFolder($this->folder->id);

        $this->assertEquals(1, count($ret));
        $this->assertEquals($GLOBALS['mod_strings']['LNK_MY_INBOX'], $ret[0]['text']);
        //Should contain 'My Drafts', 'My Sent Mail', 'My Archive'

        $folderTypes = array();
        foreach ($ret[0]['children'] as $p) {
            $folderTypes[] = $p['folder_type'];
        }

        $this->assertContains("draft", $folderTypes);
        $this->assertContains("sent", $folderTypes);
        $this->assertContains("archived", $folderTypes);
    }

    /**
     * Test to ensure that whenever a folder is deleted, it will be created for a user
     *
     */
    public function testCreationOnDeletedFolder()
    {
        $folder_type = "draft";

        $this->createNewSugarFolder();

        // Call preflightUser() to re-create all folders
        $emailUI = new EmailUI();
        $emailUI->preflightUser($GLOBALS['current_user']);

        $rootNode = new ExtNode('', '');

        // Delete one folder type
        $GLOBALS['db']->query(
            "DELETE FROM folders WHERE folder_type='{$folder_type}' AND created_by='{$GLOBALS['current_user']->id}'"
        );

        // Call preflightUser() to re-create missing folder
        $emailUI->preflightUser($GLOBALS['current_user']);

        // Retrieve folders
        $ret = $this->folder->getUserFolders($rootNode, "", $GLOBALS['current_user'], true);

        // Should contain deleted folder after preflightUser
        $folderTypes = array();
        foreach ($ret[0]['children'] as $p) {
            $folderTypes[] = $p['folder_type'];
        }

        $this->assertContains("draft", $folderTypes);
        $this->assertContains("sent", $folderTypes);
        $this->assertContains("archived", $folderTypes);
    }

    /**
     * Test to ensure that whenever the inboud folder is deleted, it will be created for a user
     * and the parent_id for the other folders will be updated accordingly.
     *
     */
    public function testCreationOnDeletedInboundFolder()
    {
        $this->createNewSugarFolder();

        // Call preflightUser() to re-create all folders
        $emailUI = new EmailUI();
        $emailUI->preflightUser($GLOBALS['current_user']);

        $error_message = "Unable to get user folders";
        $rootNode = new ExtNode('', '');

        // Delete one folder type
        $GLOBALS['db']->query(
            "DELETE FROM folders WHERE folder_type = 'inbound' AND created_by='{$GLOBALS['current_user']->id}'"
        );

        // Retrieve folders
        $ret = $this->folder->getUserFolders($rootNode, "", $GLOBALS['current_user'], true);

        $this->assertEquals(0, count($ret), $error_message);

        // Call preflightUser() to re-create missing folder
        $emailUI->preflightUser($GLOBALS['current_user']);

        // Retrieve folders
        $ret = $this->folder->getUserFolders($rootNode, "", $GLOBALS['current_user'], true);

        $this->assertEquals(1, count($ret));
        // Should contain all folders after preflightUser
        $folderTypes = array();
        foreach ($ret[0]['children'] as $p) {
            $folderTypes[] = $p['folder_type'];
        }

        $this->assertContains("draft", $folderTypes);
    }

    private function createNewSugarFolder()
    {
        $this->folder = new SugarFolder();
        $this->folder->new_with_id = true;
        $this->folder->name = "Bug62883Test";
        $this->folder->created_by = $this->folder->modified_by = $GLOBALS['current_user']->id;
        $this->folder->save();
    }

    private function clearFolder($folder_id)
    {
        $GLOBALS['db']->query(
            "DELETE FROM folders_subscriptions WHERE assigned_user_id='{$GLOBALS['current_user']->id}'"
        );
        $GLOBALS['db']->query("DELETE FROM folders_subscriptions WHERE folder_id='{$folder_id}'");
        $GLOBALS['db']->query("DELETE FROM folders WHERE id='{$folder_id}'");
    }
}
