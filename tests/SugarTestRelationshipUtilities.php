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


require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;

class SugarTestRelationshipUtilities
{
    private static $_relsAdded = array();

    protected static $_relRequiredKeys = array(
        'relationship_type',
        'lhs_module',
        'rhs_module',
    );

    /**
     * Create a relationship
     *
     * Params should be passed in as this:
     *
     * array(
     *       'relationship_type' => 'one-to-many',
     *       'lhs_module' => 'Accounts',
     *       'rhs_module' => 'Accounts',
     *   )
     *
     * @static
     * @param array $relationship_def
     * @return ActivitiesRelationship|bool|ManyToManyRelationship|ManyToOneRelationship|OneToManyRelationship|OneToOneRelationship
     */
    public static function createRelationship(array $relationship_def)
    {

        if(!self::checkRequiredFields($relationship_def)) return false;

        $relationships = new DeployedRelationships ($relationship_def['lhs_module']);

        if(!isset($relationship_def['view_module'])) {
            $relationship_def['view_module'] = $relationship_def['lhs_module'];
        }

        $REQUEST_Backup = $_REQUEST;

        $_REQUEST = $relationship_def;

        $relationship = $relationships->addFromPost();
        $relationships->save();
        $relationships->build();
        LanguageManager::clearLanguageCache($relationship_def['lhs_module']);

        SugarRelationshipFactory::rebuildCache();
        // rebuild the dictionary to make sure that it has the new relationship in it
        SugarTestHelper::setUp('dictionary');
        // reset the link fields since we added one
        VardefManager::$linkFields = array();

        $_REQUEST = $REQUEST_Backup;
        unset($REQUEST_Backup);


        self::$_relsAdded[] = $relationship->getDefinition();

        return $relationship;
    }

    /**
     * Remove all created relationships
     *
     * @static
     */
    public static function removeAllCreatedRelationships()
    {
        foreach(self::$_relsAdded as $rel) {

            $relationships = new DeployedRelationships($rel['lhs_module']);

            $relationships->delete($rel['relationship_name']);

            $relationships->save();
            $relationships->build();
            LanguageManager::clearLanguageCache($rel['lhs_module']);
            require_once("data/Relationships/RelationshipFactory.php");
            SugarRelationshipFactory::deleteCache();

            SugarRelationshipFactory::rebuildCache();
        }
        // since we are creating a relationship we need to unset this global var
        if(isset($GLOBALS['reload_vardefs'])) {
            unset($GLOBALS['reload_vardefs']);
        }
    }

    /**
     * Make sure we have at least the required keys
     *
     * @static
     * @param array $relationship_def
     * @return bool
     */
    protected static function checkRequiredFields(array $relationship_def)
    {
        foreach(self::$_relRequiredKeys as $key) {
            if(!array_key_exists($key, $relationship_def)) {
                return false;
            }
        }

        return true;
    }

}
?>