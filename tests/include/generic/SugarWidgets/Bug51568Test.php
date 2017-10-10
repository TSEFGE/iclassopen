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

require_once 'include/generic/LayoutManager.php';

/**
 * Bug #51568
 *  Currency symbol didn't export to the CVS or pdf file in report module
 *
 * @author aryamrchik@sugarcrm.com
 * @ticket 51568
 */
class Bug51568Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var LayoutManager
     */
    protected $lm;

    /**
     * @var Currency
     */
    protected $currency_51568;

    /**
     * @var Currency
     */
    protected $currency_system;

    /**
     * @var string
     */
    protected $backupSymbol;

    public function setUp()
    {
        global $current_user, $sugar_config;
        SugarTestHelper::setUp('current_user', array(true));
        $current_user->setPreference('dec_sep', ',');
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('default_currency_significant_digits', 3);

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        parent::setUp();

        $this->lm = new LayoutManager();
        $this->lm->setAttribute('reporter', new stdClass());

        $this->currency_51568 = new Currency();
        $this->currency_51568->symbol = 'TT';
        $this->currency_51568->conversion_rate = 0.5;
        $this->currency_51568->save(false);
        $this->currency_system = new Currency();
        $this->currency_system->retrieve(-99);
        $this->backupSymbol = $this->currency_system->symbol;
        $this->currency_system->symbol = '¥';
        $this->currency_system->save(false);
        $sugar_config['default_currency_symbol'] = '¥';
        get_number_seperators(true);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithLayoutDef()
    {
        $data = array(
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol
        );
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithCurrencyField()
    {
        $data = array(
            'fields' => array(
                '51568table_some_field_currency' => $this->currency_51568->id)
        );
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithAnotherCurrencyField()
    {
        $data = array(
            'fields' => array(
                '51568TABLE_SOME_FIELD_CURRENCY' => $this->currency_51568->id)
        );
        $result = $this->getResults($data);
        $this->assertEquals('TT100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithSystemCurrencyField()
    {
        format_number(0, 0, 0, array(
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol
        ));

        format_number(0, 0, 0, array(
            'currency_id' => -99,
            'currency_symbol' => $this->currency_51568->getDefaultCurrencySymbol()
        ));

        $data = array(
            'name' => 'some_field_usdoll',
            'column_key' => 'self::some_field_usdoll',
            'fields' => array(
                '51568TABLE_SOME_FIELD_USDOLL' => 100500
            )
        );
        $result = $this->getResults($data);
        $this->assertEquals('¥100.500,000', $result);
    }

    /**
     * @group 51568
     */
    public function testFieldCurrencyPlainWithWrongCurrency()
    {
        $data = array(
            'currency_id' => '-51568',
            'currency_symbol' => '£'
        );
        $result = $this->getResults($data);
        $this->assertEquals('¥100.500,000', $result);
    }

    protected function getResults($layout_def_addon)
    {
        $layout_def = array(
            'column_key' => 'self::some_field',
            'fields' => array(
                '51568TABLE_SOME_FIELD' => 100500,
            ),
            'name' => 'some_field',
            'table_key' => 'self',
            'table_alias' => '51568table',
            'type' => 'currency'
        );
        foreach($layout_def_addon as $k => $v)
        {
            if(is_array($v))
            {
                $layout_def = array_merge_recursive($layout_def, array($k => $v));
            }
            else
            {
                $layout_def[$k] = $v;
            }
        }
        $sf = $this->getMock('SugarWidgetFieldCurrency',
            array('getTruncatedColumnAlias'),
            array(&$this->lm));
        $sf->expects($this->any())
            ->method('getTruncatedColumnAlias')
            ->will($this->returnArgument(0));
        return $sf->displayListPlain($layout_def);
    }

    public function tearDown()
    {
        global $sugar_config;
        $this->currency_system->symbol = $this->backupSymbol;
        $this->currency_system->save(false);
        $sugar_config['default_currency_symbol'] = $this->backupSymbol;

        format_number(0, 0, 0, array(
            'currency_id' => $this->currency_51568->id,
            'currency_symbol' => $this->currency_51568->symbol
        ));

        format_number(0, 0, 0, array(
            'currency_id' => -99,
            'currency_symbol' => $this->currency_51568->getDefaultCurrencySymbol()
        ));

        $this->currency_51568->mark_deleted($this->currency_51568->id);
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }
}
