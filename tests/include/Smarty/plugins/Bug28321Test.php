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


require_once 'include/connectors/formatters/FormatterFactory.php';
class Bug28321Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function testCustomCodeRenderField()
	{
		$params = array(
			'var' => array(
				'name' => 'name',
				'comment' => 'Name of the Company',
				'label' => 'LBL_NAME',
				'displayParams' => array
					(
						'enableConnectors' => '1',
						'module' => 'Accounts',
					),

				'customCodeRenderField' => '1',
				'customCode' => '<a href="http://www.google.com?q={$fields.name.value}">Search</a>',
				'tabindex' => '100'
			),

			'colData' => array
			(
				'field' => array
					(
						'name' => 'name',
						'comment' => 'Name of the Company',
						'label' => 'LBL_NAME',
						'displayParams' => array
							(
								'enableConnectors' => '1',
								'module' => 'Accounts',
							),

						'customCodeRenderField' => '1',
						'customCode' => '<a href="http://www.google.com?q={$fields.name.value}">Search</a>',
						'tabindex' => '100'
					)

        	)
        );

    	require_once('include/Sugar_Smarty.php');
    	$ss = new Sugar_Smarty();
    	$ss->left_delimiter = '{{';
    	$ss->right_delimiter = '}}';
    	require_once 'include/Smarty/plugins/function.sugar_evalcolumn.php';
		$output = smarty_function_sugar_evalcolumn($params, $ss);

		//Doing this the hack way...customCode is 65 chars long.
		//if customCodeRenderField is set then the connectors have already been processed
		$this->assertEquals(65, strlen($output),'Connectors should not be processed when customCode is set to customCodeRenderField');

		//now if customCodeRenderField is not set then process as normal
		unset($params['var']['customCodeRenderField']);
		unset($params['colData']['field']['customCodeRenderField']);
		$output = smarty_function_sugar_evalcolumn($params, $ss);

		$this->assertGreaterThan(65, strlen($output),'Connectors should not be processed when customCode is set to customCodeRenderField');

	}
}
