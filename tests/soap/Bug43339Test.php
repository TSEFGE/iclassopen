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


require_once("service/v4_1/SugarWebServiceImplv4_1.php");
require_once('tests/service/SOAPTestCase.php');
require_once('soap/SoapError.php');

/**
 * Bug #43339
 * get_entries_count doesn't work with custom fields
 *
 * @ticket 43339
 */
class Bug43339Test extends SOAPTestCase
{

    private $_module = NULL;
    private $_moduleName = 'Contacts';
    private $_customFieldName = 'test_custom_c';
    private $_field = null;
    private $_df = null;

    protected $session = array();

    public function setUp()
    {
        $this->session['use_cookies'] = ini_get('session.use_cookies');
        $this->session['cache_limiter'] = ini_get('session.session.cache_limiter');
        ini_set('session.use_cookies', false);
        ini_set('session.cache_limiter', false);

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $_SERVER['REMOTE_ADDR'] = '0.0.0.0';

        $this->_setupTestUser();

        $this->_field = get_widget('varchar');
        $this->_field->id = $this->_moduleName . $this->_customFieldName;
        $this->_field->name = $this->_customFieldName;
        $this->_field->vanme = 'LBL_' . strtoupper($this->_customFieldName);
        $this->_field->comments = NULL;
        $this->_field->help = NULL;
        $this->_field->custom_module = $this->_moduleName;
        $this->_field->type = 'varchar';
        $this->_field->label = 'LBL_' . strtoupper($this->_customFieldName);
        $this->_field->len = 255;
        $this->_field->required = 0;
        $this->_field->default_value = '';
        $this->_field->date_modified = '2012-03-14 02:23:23';
        $this->_field->deleted = 0;
        $this->_field->audited = 0;
        $this->_field->massupdate = 0;
        $this->_field->duplicate_merge = 0;
        $this->_field->reportable = 1;
        $this->_field->importable = 'true';
        $this->_field->ext1 = NULL;
        $this->_field->ext2 = NULL;
        $this->_field->ext3 = NULL;
        $this->_field->ext4 = NULL;

        $className = $beanList[$this->_moduleName];
        require_once($beanFiles[$className]);
        $this->_module = new $className();

        $this->_df = new DynamicField($this->_moduleName);

        $this->_df->setup($this->_module);
        $this->_df->addFieldObject($this->_field);
    }

    /**
     * get_entries_count doesn't work with custom fields
     *
     * @group 43339
     */
    public function testGetEntriesCountForCustomField()
    {
        $api = new SugarWebServiceImplv4_1();
        $auth = $api->login(array('user_name' => self::$_user->user_name, 'password' => self::$_user->user_hash, 'version' => '.01'), 'SoapTest', array());
        $assert = $api->get_entries_count($auth['id'], $this->_moduleName, $this->_customFieldName . ' LIKE \'\'', 0);
        $api->logout($auth['id']);

        $this->assertNotEquals(NULL, $assert['result_count']);
    }

    /**
     * Test for soap/SoapSugarUsers.php::get_entries_count()
     *
     * @group 43339
     */
    public function testGetEntriesCountFromBasicSoap()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();

        $auth = $this->_soapClient->call('login',
            array('user_auth' =>
            array(
                'user_name' => $this->_user->user_name,
                'password' => $this->_user->user_hash,
                'version' => '.01'),
                'application_name' => 'SoapTest', "name_value_list" => array()
            )
        );

        $params = array(
            'session' => $auth['id'],
            'module_name' => $this->_moduleName,
            'query' => $this->_customFieldName . ' LIKE \'\'',
            'deleted' => 0
        );
        $assert = $this->_soapClient->call('get_entries_count', $params);

        $this->assertNotEquals(NULL, $assert['result_count']);
    }

    public function tearDown()
    {
        $this->_df->deleteField($this->_field);

        SugarTestHelper::tearDown();
        unset($_SERVER['REMOTE_ADDR']);

        $this->_tearDownTestUser();

        ini_set('session.use_cookies', $this->session['use_cookies']);
        ini_set('session.cache_limiter', $this->session['cache_limiter']);
    }
}
