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

require_once 'modules/Opportunities/Opportunity.php';

class SugarTestOpportunityUtilities
{
    
    private static $_createdOpportunities = array();
    
    private static $_createdAccount = null;
    
    private function __construct()
    {
    }
    
    private function _createAccount($time)
    {
        if (self::$_createdAccount === null) 
        {
            $name = 'SugarOpportunityAccount';
            $account = new Account();
            $account->name = $name . $time;
            $account->email1 = 'account@' . $time . 'sugar.com';
            $account->save();
            
            $GLOBALS['db']->commit();
            self::$_createdAccount = $account;
        }

        return self::$_createdAccount;
    }
    
    private function _createOpportunity($id, $time, $account)
    {
        global $timedate;
        $name = 'SugarOpportunity';

        $opportunity = new Opportunity();
            
        if (!empty($id)) 
        {
            $opportunity->new_with_id = true;
            $opportunity->id = $id;
        }

        $opportunity->name         = $name . $time;
        $opportunity->amount       = 10000;
        $opportunity->account_id   = $account->id;
        $opportunity->account_name = $account->name;
        $opportunity->date_closed  = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s"));
        $opportunity->save();

        $GLOBALS['db']->commit();
        
        self::$_createdOpportunities[] = $opportunity;
        
        return $opportunity;
    }
    
    public static function createOpportunity($id = '', Account $account = null)
    {
        $time = mt_rand();

        if ($account === null)
        {
            $account = self::_createAccount($time);
        }

        $opportunity = self::_createOpportunity($id, $time, $account);
        
        return $opportunity;
    }
    
    public static function setCreatedOpportunity($opportunity_ids)
    {
        foreach ($opportunity_ids as $opportunity_id) 
        {
            $opportunity = new Opportunity();
            $opportunity->id = $opportunity_id;
            self::$_createdOpportunities[] = $opportunity;
        }
    }
    
    public static function removeAllCreatedOpportunities()
    {
        $opportunity_ids = self::getCreatedOpportunityIds();
        
        if (!empty($opportunity_ids))
        {
            $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", $opportunity_ids) . '\')');
            $GLOBALS['db']->query('DELETE FROM opportunities_contacts WHERE opportunity_id IN (\'' . implode("', '", $opportunity_ids) . '\')');
        }

        if (self::$_createdAccount !== null && self::$_createdAccount->id)
        {
            $GLOBALS['db']->query('DELETE FROM accounts WHERE id = \'' . self::$_createdAccount->id . '\'');
        }
    }
    
    public static function getCreatedOpportunityIds()
    {
        $opportunity_ids = array();
        
        foreach (self::$_createdOpportunities as $opportunity) 
        {
            $opportunity_ids[] = $opportunity->id;
        }
        
        return $opportunity_ids;
    }
}
?>
