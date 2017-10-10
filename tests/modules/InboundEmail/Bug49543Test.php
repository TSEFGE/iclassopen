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


require_once('modules/InboundEmail/InboundEmail.php');
/**
 * Bug #49543
 * Email cache reset issue causing email deletion slowness
 * @ticket 49543
 */
class Bug49543Test extends Sugar_PHPUnit_Framework_TestCase
{
    private function createMail($subj, $from, $to, $imap_uid, $date, $uid)
    {
        $mail = new stdClass();
        $mail->subject = $subj;
        $mail->from = $from;
        $mail->to = $to;
        $mail->imap_uid = $imap_uid;
        $mail->date = $date;
        $mail->message_id = '';
        $mail->size = '1234';
        $mail->uid = $uid;
        $mail->msgno = 0;
        $mail->recent = 0;
        $mail->flagged = 0;
        $mail->answered = 0;
        $mail->deleted = 0;
        $mail->draft = 0;
        $mail->seen = 1;
        
        return $mail;
    }
    
    /**
     * @group 49543
     */
    public function testSetCacheValue()
    {
        global $timedate;
        
        $ie_id = '123';
        $mailbox = 'trash';
        $time = mt_rand();
        $subj = 'test ' . $time;
        $GLOBALS['db']->query(sprintf("INSERT INTO email_cache (ie_id, mbox, subject, fromaddr, toaddr, imap_uid) 
                                VALUES ('%s', '%s', '%s', 'from@test.com', 'to@test.com', '11')", 
                                $ie_id, $mailbox, $subj));
        
        //deleted item from inbox which will be inserted in trash
        $insert[0] = $this->createMail($subj.'_new', 'from@test.com', 'to@test.com', '12', '2012-11-11 11:11:11', '12');
        
        //old trash item which should be updated
        $insert[1] = $this->createMail($subj.'_old', 'from@test.com', 'to@test.com', '11', '2011-11-11 11:11:11', '11');
        
        $ie = new InboundEmail();
        $ie->id = $ie_id;
        
        $ie->setCacheValue($mailbox, $insert, '', '');
        
        $fr = $GLOBALS['db']->fetchRow($GLOBALS['db']->query("SELECT subject FROM email_cache WHERE imap_uid = '11'"));
        
        //if old trash item was updated successfully then 'subject' has new value
        $this->assertTrue($fr['subject'] == $subj.'_old');
        
        $GLOBALS['db']->query(sprintf("DELETE FROM email_cache WHERE mbox = '%s'", $mailbox));
    }
}
?>