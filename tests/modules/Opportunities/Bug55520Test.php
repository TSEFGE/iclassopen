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


require_once 'include/export_utils.php';

/**
 * Bug #55520
 * Export opportunities on windows corrupts unicode characters
 *
 * @author vromanenko@sugarcrm.com
 * @ticked 55520
 */
class Bug55520Test extends Sugar_PHPUnit_Framework_TestCase
{
    const BOM = "\xEF\xBB\xBF";
    const DEFAULT_EXPORT_CHARSET_PREF_NAME = 'default_export_charset';
    const UTF8_CHARSET = 'UTF-8';
    const NON_UTF8_CHARSET = 'ISO-8859-1';

    /**
     * @var Opportunity
     */
    protected $opportunity;

    /**
     * @var string
     */
    protected $defaultExportCharset;

    /**
     * @var User
     */
    protected $currentUser;

    protected function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->currentUser = $GLOBALS['current_user'];
        $this->defaultExportCharset = $this->currentUser->getPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME);

        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
    }

    /**
     * Ensure that exported data starts with BOM
     *
     * @group 55520
     */
    public function testExportStringIncludesBOM()
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, self::UTF8_CHARSET);
        $export = export('Opportunities', $this->opportunity->id);
        $this->assertStringStartsWith(self::BOM, $export);
    }

    /**
     * Ensure that exported data does not start with BOM if the export character set is other than utf-8
     *
     * @group 55520
     */
    public function testExportStringNotIncludesBOM()
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, self::NON_UTF8_CHARSET);
        $export = export('Opportunities', $this->opportunity->id);
        $this->assertStringStartsNotWith(self::BOM, $export);
    }

    protected function tearDown()
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, $this->defaultExportCharset);
        SugarTestHelper::tearDown();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
    }

}