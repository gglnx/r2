<?php
/**
 * @package     R2
 * @version     1.0
 * @link        https://github.com/gglnx/r2
 * @author      Dennis Morhardt <info@dennismorhardt.de>
 * @copyright   Copyright 2013, Dennis Morhardt
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

// Location
define('APP', dirname(__FILE__));

// Load config
include 'config.php';

// Load composer components
include 'vendor/autoload.php';

// UTF8
header("Content-Type: text/html; charset=utf-8");

// Filter value
$filter_value = function($value) {
	$value = preg_replace("/<br\\s*?\/??>/i", "\n", $value);
	$value = strip_tags($value);
	$value = html_entity_decode($value);
	$value = trim($value);
	return $value;
};

// Login into programm.ard.de
$login_request = \Requests::post("http://programm.ard.de/1675_1", array(), array(
	"username" => ARD_USERNAME,
	"passwort" => ARD_PASSWORD,
	"action" => "login",
	"location" => "/1664_1&order=&dir=asc&curpage=1&event_id=",
	"userName" => ""
), array(
	"follow_redirects" => false
));

// Get session cookie
$session_id = str_replace(" path=/", "", $login_request->headers["set-cookie"]);

// Get list of saved shows
$shows_raw = \Requests::get("http://programm.ard.de/Radio/Steuerseiten/PRINT-Listenansicht?order=date&dir=&curpage=1&is_bookmark=1&is_personal=1", array(
	"Cookie" => $session_id
), array(
	"follow_redirects" => false
));

// Load html into the parser
$shows_raw_html = \Sunra\PhpSimple\HtmlDomParser::str_get_html($shows_raw->body);

// Shows
$shows = (array) @json_decode(file_get_contents(APP . "/shows.json"));

// Get show data
foreach ( $shows_raw_html->find(".sendungen .eintrag") as $show_raw ):
	// Show ID
	$show = (object) array("id" => str_replace("program_", "", $show_raw->id));

	// Check if show is already recorded
	if ( in_array( $show->id, array_keys( $shows ) ) )
		continue;
	
	// Get show detail page
	$show_detail_raw = \Requests::get("http://programm.ard.de/Radio/Steuerseiten/PRINT-Einzelsendung?event_id=" . $show->id);
	$show_detail_html = \Sunra\PhpSimple\HtmlDomParser::str_get_html($show_detail_raw->body);
	
	// Show meta data
	$album = $filter_value($show_raw->find(".dachzeile", 0)->innertext);
	$title = $filter_value($show_raw->find(".titel", 0)->innertext);
	$subtitle = $filter_value($show_raw->find(".untertitel", 0)->innertext);
	$publisher = $filter_value($show_raw->find(".sendeanstalt", 0)->innertext);
	$description = $filter_value($show_detail_html->find(".spalte2", 1)->innertext);
	
	// Get ical data for start and end time
	preg_match("#(.*)DTSTART;TZID=(.*):(.*)DTEND;TZID=(.*):(.*)DTSTAMP;(.*)#", str_replace("\n", "", \Requests::get("http://programm.ard.de/ICalendar/iCal---Sendung?sendung=" . $show->id)->body), $matches);
	$start_time = \DateTime::createFromFormat("Ymd*His", $matches[3], new \DateTimeZone($matches[2]))->modify("-2 minutes");
	$end_time = \DateTime::createFromFormat("Ymd*His", $matches[5], new \DateTimeZone($matches[4]))->modify("+2 minutes");
	$length = $end_time->getTimestamp() - $start_time->getTimestamp();
	
	// Create job file
	$command_file = 'streamripper ' . $streams[$publisher] . ' -d ' . APP . '/raw/ -s -a ' . $show->id . '.mp3 -A -l ' . $length . ' -i --quiet' . "\n" . 'curl -X POST https://auphonic.com/api/simple/productions.json -u "' . AUPHONIC_USERNAME . ':' . AUPHONIC_PASSWORD . '" -F "preset=' . AUPHONIC_PRESET . '" -F "action=start" -F "title=' . addslashes($title) . '" -F "artist=' . addslashes($publisher) . '" -F "album=' . addslashes($album) . '" -F "subtitle=' . addslashes($subtitle) . '" -F "summary=' . addslashes($description) . '" -F "input_file=@' . APP . '/raw/' . $show->id . '.mp3" > /dev/null 2>&1';
	file_put_contents(APP . "/scripts/show_{$show->id}.sh", $command_file);
	
	// Create job
	exec("at -f " . APP . "/scripts/show_{$show->id}.sh " . $start_time->format("H:i y-m-d") . " 2>&1", $output, $return);
			  
	// Get job ID
	foreach ( $output as $i => $line ):
		if ( ( substr( $line , 0, 4 ) ) == "job " ):
			$jobid_length = strpos($line, " ", 5) - 4;
			$jobid = substr($line , 4, $jobid_length);
			break;
		endif;
	endforeach;

	// Delete exec file
	unlink(APP . "/scripts/show_{$show->id}.sh");
	
	// Add show
	if ( $jobid != -1 && $return == 0 ):
		$show->jobid = $jobid;
		$shows[$show->id] = $show;
	endif;
	
	// Unset
	unset($output);
	unset($return);
endforeach;

// Save shows.json
file_put_contents(APP . "/shows.json", json_encode($shows));
