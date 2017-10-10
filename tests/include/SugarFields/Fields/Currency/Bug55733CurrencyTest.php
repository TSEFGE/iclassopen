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



require_once('include/SugarFields/Fields/Currency/SugarFieldCurrency.php');
/*
 * This tests for precision formatting from the sugarfieldcurrency object.  Prior to bug 55733, the value would get picked up from
 * the vardefs['precision'] value, instead of the currency settings.
 */

class Bug55733CurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{

    private $value1 = '20000.0000';
    private $value2 = '20000';
    private $expectedValue = '20,000.00';
    private $vardef = array('precision' => '6');
    private $sfr;

    public function setUp()
    {
        global $locale, $current_user;
        SugarTestHelper::setUp('current_user', array(true));
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);
        get_number_seperators(true);
        parent::setUp();
        //if locale is not defined, create new global locale object.
        if(empty($locale))
        {
            require_once('include/Localization/Localization.php');
            $locale = new Localization();
        }

        //create a new SugarFieldCurrency object
        $this->sfr = new SugarFieldCurrency('currency');

    }
    
    public function testFormatPrecision()
    {
        //lets test some values with different decimals to make sure the formatting is returned correctly
        $testVal1 = $this->sfr->formatField($this->value1, $this->vardef);
        $testVal2 = $this->sfr->formatField($this->value2, $this->vardef);
        $this->assertSame($this->expectedValue, $testVal1,' The currency precision was not formatted correctly.');
        $this->assertSame($this->expectedValue, $testVal2,' The currency precision was not formatted correctly.');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }
}