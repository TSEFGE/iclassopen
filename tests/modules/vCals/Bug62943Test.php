<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once 'modules/vCals/vCal.php';

/**
 * Bug #62943
 * GOTO MEETING ceation in SugarCRM does not dispay goto info in attachment
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 62943
 */
class Bug62943Test extends Sugar_PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		$GLOBALS['current_user']->full_name = "Boro Sitnikovski";
		$GLOBALS['current_user']->email1 = "bsitnikovski@sugarcrm.com";
	}

	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($GLOBALS['current_user']);
	}

    public function testiCalNewline()
	{
		$res = vCal::get_ical_event($this->_getDummyBean("http://www.sugarcrm.com/"), $GLOBALS['current_user']);

        $desc = $this->grabiCalField($res, "DESCRIPTION");
        // Test to see if there are two newlines after url for description
        $this->assertContains("http://www.sugarcrm.com/\r\n\r\n", $desc);
	}

	public function testiCalEmptyJoinURL()
	{
		$res = vCal::get_ical_event($this->_getDummyBean(), $GLOBALS['current_user']);

        $desc = $this->grabiCalField($res, "DESCRIPTION");

		// Test to see if there are no newlines for empty url for description
		$this->assertNotContains("\\n\\n", $desc);
	}

    private function grabiCalField($iCal, $field)
    {
        $ical_arr = vCal::create_ical_array_from_string($iCal);

        foreach ($ical_arr as $ical_val) {
            if ($ical_val[0] == $field) {
                return $ical_val[1];
            }
        }

        return "";
    }

	private function _getDummyBean($join_url = "")
	{
		$bean = new SugarBean();
		$bean->id = 123;
		$bean->date_start = $bean->date_end = $GLOBALS['timedate']->nowDb();
		$bean->name = "Dummy Bean";
		$bean->location = "Sugar, Cupertino; Sugar, EMEA";
		$bean->join_url = $join_url;
		$bean->description = "Hello, this is a dummy description.\nIt contains newlines, backslash \\ semicolon ; and commas";
		return $bean;
	}

}
