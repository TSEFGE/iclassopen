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


require_once('modules/ProspectLists/ProspectList.php');

class SugarTestProspectListsUtilities
{

    private static $_aCreatedProspectLists = array();
    private static $_aCreatedProspectListsIds = array();

    /**
     * @static
     * @param mixed $prospect_list_id
     */
    public static function removeProspectLists($prospect_list_id)
    {
        if (is_array($prospect_list_id)) {

            $prospect_list_id = implode("','", $prospect_list_id);
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id IN ('{$prospect_list_id}')");
        } else {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id = '{$prospect_list_id}'");
        }
    }

    /**
     * @static
     * @param string $prospect_list_id
     * @param string $prospect_id
     */
    public static function removeProspectsListToProspectRelation($prospect_list_id, $prospect_id)
    {

        $GLOBALS['db']->query("DELETE FROM prospect_lists_prospects WHERE prospect_list_id='{$prospect_list_id}' AND related_id='{$prospect_id}'");
    }


    public static function createProspectList($id = '', $aParams = array())
    {
        $time = mt_rand();
        $oProspectList = new ProspectList();
        $oProspectList->name = 'ProspectList' . $time;
        if (!empty($id))
        {
            $oProspectList->id = $id;
        }
        if (!empty($aParams))
        {
            foreach ($aParams as $key => $val)
            {
                $oProspectList->$key = $val;
            }
        }
        $oProspectList->save();
        self::$_aCreatedProspectLists[] = $oProspectList;
        self::$_aCreatedProspectListsIds[] = $oProspectList->id;
        return $oProspectList;
    }

    /**
     * @static
     * @param mixed $prospect_list_id
     */
    public static function removeCreatedProspectLists($id = '')
    {
        if (!empty($id))
        {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id = '{$id}'");
        }
        elseif (!empty(self::$_aCreatedProspectLists))
        {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id IN ('" . implode("','", self::$_aCreatedProspectListsIds) . "')");
        }
    }
}
