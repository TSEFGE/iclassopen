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

require_once 'include/dir_inc.php';
require_once('modules/UpgradeWizard/UpgradeRemoval.php');

class Bug49955Test extends Sugar_PHPUnit_Framework_TestCase  {
	
var $merge;

function setUp() {
   SugarTestMergeUtilities::setupFiles(array('Notes'), array('editviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');

    if(file_exists('custom/backup/modules/DocumentRevisions'))
    {
  	   rmdir_recursive('custom/backup/modules/DocumentRevisions');
    }

    file_put_contents('modules/DocumentRevisions/EditView.html', 'test');
    file_put_contents('modules/DocumentRevisions/DetailView.html', 'test');
    file_put_contents('modules/DocumentRevisions/EditView.php', 'test');
    file_put_contents('modules/DocumentRevisions/DetailView.php', 'test');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();

   if(file_exists('custom/backup/modules/DocumentRevisions'))
   {
  	   rmdir_recursive('custom/backup/modules/DocumentRevisions');
   }

   if(file_exists('modules/DocumentRevisions/EditView.html'))
   {
       unlink('modules/DocumentRevisions/EditView.html');
   }

   if(file_exists('modules/DocumentRevisions/DetailView.html'))
   {
       unlink('modules/DocumentRevisions/DetailView.html');
   }

   if(file_exists('modules/DocumentRevisions/EditView.php'))
   {
       unlink('modules/DocumentRevisions/EditView.php');
   }

   if(file_exists('modules/DocumentRevisions/DetailView.php'))
   {
       unlink('modules/DocumentRevisions/DetailView.php');
   }
}

function test_filename_convert_merge() {
   require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
   $this->merge = new EditViewMerge();
   $this->merge->merge('Notes', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Notes/metadata/editviewdefs.php','modules/Notes/metadata/editviewdefs.php','custom/modules/Notes/metadata/editviewdefs.php');
   require('custom/modules/Notes/metadata/editviewdefs.php');

   $foundFilename = 0;
   $fileField = '';

   foreach ( $viewdefs['Notes']['EditView']['panels'] as $panel ) {
       foreach ( $panel as $row ) {
           foreach ( $row as $col ) {
               if ( is_array($col) ) {
                   $fieldName = $col['name'];
               } else {
                   $fieldName = $col;
               }
               
               if ( $fieldName == 'filename' ) {
                   $fileField = $col;
                   break;
               }
           }
       }
   }

   $this->assertNotEmpty($fileField,'Filename field doesn\'t exit, it should');
   $this->assertTrue(is_string($fileField) && $fileField == 'filename', 'Filename field not converted to string');

   if ( file_exists('custom/modules/Notes/metadata/editviewdefs-testback.php') ) {
       copy('custom/modules/Notes/metadata/editviewdefs-testback.php','custom/modules/Notes/metadata/editviewdefs.php');
       unlink('custom/modules/Notes/metadata/editviewdefs-testback.php');
   }

   //Now test the DocumentRevisions cleanup
    $instance = new UpgradeRemoval49955Mock();
  	$instance->processFilesToRemove($instance->getFilesToRemove(624));
    $this->assertTrue(!file_exists('modules/DocumentRevisions/EditView.html'));
    $this->assertTrue(!file_exists('modules/DocumentRevisions/DetaillView.html'));
    $this->assertTrue(!file_exists('modules/DocumentRevisions/EditView.php'));
    $this->assertTrue(!file_exists('modules/DocumentRevisions/DetailView.html'));

}

}

class UpgradeRemoval49955Mock extends UpgradeRemoval
{

public function getFilesToRemove($version)
{
	$files = array();
	$files[] = 'modules/DocumentRevisions/EditView.html';
    $files[] = 'modules/DocumentRevisions/DetailView.html';
    $files[] = 'modules/DocumentRevisions/EditView.php';
    $files[] = 'modules/DocumentRevisions/DetailView.php';
	return $files;
}


}