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
 * Bug #64061
 * iCal Should Respect RFC 5545
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 64061
 */
class Bug64061Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test an empty string and see if it gets added
     *
     */
    public function testLineBreaks()
    {
        // this field should not be added by fold_ical_lines
        // and it is already checked because $icalstring does not contain it
        $icalarray = array();
        $icalarray[] = array("TESTLINEBREAKS", "------------------------75characters------------------------0");
        $res = vCal::create_ical_string_from_array($icalarray);

        $icalstring = "TESTLINEBREAKS:------------------------75characters------------------------\r\n\t0\r\n";
        $this->assertEquals($icalstring, $res);
    }

    /**
     * Test the function create_ical_string_from_array()
     *
     * @dataProvider iCalProvider
     */
    public function testiCalStringFromArray($icalarray, $icalstring)
    {
        $res = vCal::create_ical_string_from_array($icalarray);
        $this->assertEquals($icalstring, $res);
    }

    /**
     * Test the function create_ical_array_from_string()
     *
     * @dataProvider iCalProvider
     */
    public function testiCalArrayFromString($icalarray, $icalstring)
    {
        $res = vCal::create_ical_array_from_string($icalstring);
        $this->assertEquals($icalarray, $res);
    }

    public function iCalProvider()
    {
        $ical_array = array();
        $ical_array[] = array("BEGIN", "VCALENDAR");
        $ical_array[] = array("VERSION", "2.0");
        $ical_array[] = array("PRODID", "-//SugarCRM//SugarCRM Calendar//EN");
        $ical_array[] = array("BEGIN", "VEVENT");
        $ical_array[] = array("UID", "123");
        $ical_array[] = array("ORGANIZED;CN=Boro Sitnikovski", "bsitnikovski@sugarcrm.com");
        $ical_array[] = array("SUMMARY", "Dummy Bean");
        $ical_array[] = array("LOCATION", "Sugar, Cupertino; Sugar, EMEA");
        $ical_array[] = array("DESCRIPTION", "Hello, this is a dummy description.\nIt contains newlines, " .
            "backslash \ semicolon ; and commas. This line should also contain more than 75 characters.");
        $ical_array[] = array("END", "VEVENT");
        $ical_array[] = array("END", "VCALENDAR");

        $ical_string = "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:-//SugarCRM//SugarCRM Calendar//EN\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:123\r\n" .
            "ORGANIZED;CN=Boro Sitnikovski:bsitnikovski@sugarcrm.com\r\n" .
            "SUMMARY:Dummy Bean\r\n" .
            "LOCATION:Sugar\\, Cupertino\\; Sugar\\, EMEA\r\n" .
            "DESCRIPTION:Hello\\, this is a dummy description.\\nIt contains newlines\\, ba\r\n" .
            "\tckslash \\\\ semicolon \\; and commas. This line should also contain more tha\r\n" .
            "\tn 75 characters.\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n";

        return array(array($ical_array, $ical_string));
    }
}
