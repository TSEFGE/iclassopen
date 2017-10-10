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


/**
* @ticket 51596
*/
class Bug51596Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
    * @var Contact
    */
    protected $contact1,
        $contact2;

    /**
     * @var Account
     * @var Account
     */
    protected $account1,
        $account2;

    protected $field_name = 'bug51596test';

    /**
    * Sets up the fixture, for example, open a network connection.
    * This method is called before a test is executed.
    *
    * @return void
    */
    public function setUp()
    {
        SugarTestHelper::setUp('mod_strings', array('Administration'));
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        // add an extra relationship that will be used for search
        self::registerExtension('Contacts', 'bug51596test.php', array(
            'Contact' => array(
                'fields' => array(
                    $this->field_name => array (
                        'name'      => $this->field_name,
                        'rname'     => 'name',
                        'id_name'   => 'account_id',
                        'join_name' => 'accounts',
                        'type'      => 'relate',
                        'link'      => 'accounts',
                        'table'     => 'accounts',
                        'module'    => 'Accounts',
                        'source'    => 'non-db',
                    ),
                ),
            ),
        ));

        // this is needed for newly created extension to be loaded for new beans
        $_SESSION['developerMode'] = true;
        $GLOBALS['reload_vardefs'] = true;

        // create a set of contacts and related accounts
        $this->contact1 = new Contact();
        $this->contact1->do_not_call = 0;
        $this->contact1->save();

        $this->contact2 = new Contact();
        $this->contact2->do_not_call = 0;
        $this->contact2->save();

        $this->account1 = new Account();
        $this->account1->name = 'Bug51596Test_Account1';
        $this->account1->save();

        $this->account2 = new Account();
        $this->account2->name = 'Bug51596Test_Account2';
        $this->account2->save();

        $this->contact1->load_relationship('accounts');
        $this->contact2->load_relationship('accounts');

        /** @var Link2 $accounts1 */
        $accounts1 = $this->contact1->accounts;
        $accounts1->add(array($this->account1->id));

        /** @var Link2 $accounts2 */
        $accounts2 = $this->contact2->accounts;
        $accounts2->add(array($this->account2->id));

        // will update "do_not_call" attribute of found contacts
        $_REQUEST['massupdate'] = 'true';
        $_REQUEST['entire']     = true;
        $_REQUEST['module']     = 'Contacts';
        $_POST['do_not_call']   = 1;
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($_REQUEST['massupdate'], $_REQUEST['entire'], $_REQUEST['module'], $_POST['do_not_call']);

        if (!empty($this->account2))
        {
            $this->account2->mark_deleted($this->account2->id);
        }
        if (!empty($this->account1))
        {
            $this->account1->mark_deleted($this->account1->id);
        }
        if (!empty($this->contact2))
        {
            $this->contact2->mark_deleted($this->contact2->id);
        }
        if (!empty($this->contact1))
        {
            $this->contact1->mark_deleted($this->contact1->id);
        }



        self::unregisterExtension('Contacts', 'bug51596test.php');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        unset($GLOBALS['reload_vardefs'], $_SESSION['developerMode']);
        SugarTestHelper::tearDown();
    }

    /**
     * Verifies that objects are found and updated by name of custom related
     * object
     *
     * @return void
     */
    public function testSearchAndUpdate()
    {
        $contact = new Contact();

        require_once 'include/MassUpdate.php';
        $mass_update = new MassUpdate();
        $mass_update->sugarbean = $contact;

        // search for contacts related to Bug51596Test_Account1 (e.g. Contact1)
        $current_query_by_page = array (
            'searchFormTab'              => 'basic_search',
            $this->field_name . '_basic' => 'Bug51596Test_Account1',
        );

        // perform mass update
        $current_query_by_page = base64_encode(serialize($current_query_by_page));
        $mass_update->generateSearchWhere('Contacts', $current_query_by_page);
        $mass_update->handleMassUpdate();

        // ensure that "do_not_call" attribute of Contact1 has been changed
        $contact->retrieve($this->contact1->id);
        $this->assertEquals(1, $contact->do_not_call);

        // ensure that "do_not_call" attribute of Contact2 has not been changed
        $contact->retrieve($this->contact2->id);
        $this->assertEquals(0, $contact->do_not_call);
    }

    /**
     * Utility function. Registers vardef extension for specified module.
     *
     * @static
     * @param string $module
     * @param string $filename
     * @param array $data
     * @return void
     */
    protected static function registerExtension($module, $filename, array $data)
    {
        $directory = 'custom/Extension/modules/' . $module . '/Ext/Vardefs';

        if (!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }

        $path = $directory . '/' . $filename;
        $data = var_export($data, true);

        $contents = <<<HERE
<?php
\$dictionary = array_merge_recursive(\$dictionary, {$data});
HERE;

        file_put_contents($path, $contents);

        self::rebuildExtensions($module);
    }

    /**
     * Utility function. Unregisters vardef extension for specified module.
     *
     * @static
     * @param string $module
     * @param string $filename
     * @return void
     */
    protected static function unregisterExtension($module, $filename)
    {
        $directory = 'custom/Extension/modules/' . $module . '/Ext/Vardefs';

        if (!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }

        $path = $directory . '/' . $filename;
        unlink($path);

        self::rebuildExtensions($module);
    }

    /**
     * Utility function. Rebuilds extensions for specified module.
     *
     * @static
     * @param string $module
     * @return void
     */
    protected static function rebuildExtensions($module)
    {
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array('rebuildExtensions'), array($module), false, false);
    }

}
