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


require_once "include/generic/LayoutManager.php";
require_once "include/generic/SugarWidgets/SugarWidgetFielddatetime.php";

class Bug49008Test extends PHPUnit_Framework_TestCase
{
    var $sugarWidgetField;

    public function setUp()
    {
        $this->sugarWidgetField = new SugarWidgetFieldDateTime49008Mock(new LayoutManager());
        global $current_user, $timedate;
        $timedate = TimeDate::getInstance();
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->setPreference('timezone', 'America/Los_Angeles');
        $current_user->save();
        $current_user->db->commit();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     *
     */
    public function testExpandDateLosAngeles()
    {
        $start = $this->sugarWidgetField->expandDate('2011-12-17');
        $this->assertRegExp('/\:00\:00/',  $start->asDb(), 'Assert for expandDate without end set, we use 00:00:00');
        $end = $this->sugarWidgetField->expandDate('2011-12-18', true);
        $this->assertRegExp('/\:59\:59/', $end->asDb(), 'Assert for expandDate with end set to true we use 23:59:59');
    }
}

class SugarWidgetFieldDateTime49008Mock extends SugarWidgetFieldDateTime
{
     public function expandDate($date, $end=false) {
         return parent::expandDate($date, $end);
     }
}

?>