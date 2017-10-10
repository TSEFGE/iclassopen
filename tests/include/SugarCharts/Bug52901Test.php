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


require_once('include/SugarCharts/Jit/JitReports.php');

class Bug52901Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }


    /**
     * DataProvider function for test
     * @static
     * @return array
     */
    public static function dataFeed()
    {
        $dataSeed = array(
            // Accounts:
            // 1. type = '', industry = '';
            // 2. type = 'Bar', industry = 'Foo'
            'emptyTypeAndIndustry' => array(
                array(
                    '' =>
                    array(
                        '' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => '',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => '',
                        ),
                    ),
                    'Bar' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('', 'Foo'),
                array('type', 'industry'),
                array('', 'Foo', '', 'Foo'),
            ),
            // Accounts:
            // 1. type = 'Foo', industry = 'Bar';
            // 2. type = 'Bar', industry = 'Foo'
            'bothDifferentTypeAndIndustry' => array(
                array(
                    'Foo' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Bar' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type', 'industry'),
                array('Foo', 'Bar', 'Foo', 'Bar'),
            ),
            // Accounts:
            // 1. type = 'Foo', industry = 'Foo';
            // 2. type = 'Bar', industry = 'Bar'
            'bothEqualTypeAndIndustry' => array(
                array(
                    'Bar' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Foo' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type', 'industry'),
                array('Foo', 'Bar', 'Foo', 'Bar'),
            ),
            // Accounts: Single group by. only by type
            // 1. type = 'Foo'
            // 2. type = 'Bar'
            'onlyByType' => array(
                array(
                    'Bar' =>
                    array(
                        'Bar' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Bar',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Bar',
                        ),
                    ),
                    'Foo' =>
                    array(
                        'Foo' =>
                        array(
                            'numerical_value' => 1,
                            'group_text' => 'Foo',
                            'group_key' => 'self:account_type',
                            'count' => '',
                            'group_label' => 'someHtml',
                            'numerical_label' => 'someHtml',
                            'numerical_key' => 'count',
                            'module' => 'Accounts',
                            'group_base_text' => 'Foo',
                        ),
                    ),
                ),
                array('Foo', 'Bar'),
                array('type'),
                array(),
            ),
        );

        return $dataSeed;
    }

    /**
     * Test that <subgroups> is filled properly
     *
     * @param $dataSet  array dataSet for JitReports
     * @param $superSet array super_set for JitReports
     * @param $groupBy array array of group_by levels
     * @param $expectedSubgroupNodesTitles array expected list of values of node <title> in each node <subgroups>
     *
     * @dataProvider dataFeed
     * @group 52901
     */
    public function testXMLIsGeneratedProperly($dataSet, $superSet, $groupBy, $expectedSubgroupNodesTitles)
    {
        $JR = new JitReports();
        $JR->setData($dataSet);
        $JR->super_set = $superSet;
        $JR->setDisplayProperty('thousands', false);
        $JR->group_by = $groupBy;

        // We do this because the function which is under the test (xmlDataReportChart()) returns XML without root node and thus causes XML parse error
        $actualXML = '<data>' . $JR->xmlDataReportChart() . '</data>';

        // Get the list of <title> node value elements of each <subgroup>
        $dom = new DomDocument();
        $dom->loadXML($actualXML);
        $xpath = new DomXPath($dom);
        $nodes = $xpath->query('group/subgroups/group/title');
        $actualSubgroupNodesTitlesArray = array();
        foreach ($nodes as $node)
        {
            $actualSubgroupNodesTitlesArray[] = $node->nodeValue;
        }

        $this->assertEquals($expectedSubgroupNodesTitles, $actualSubgroupNodesTitlesArray);
    }

}