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

// Login for programm.ard.de/radio
define("ARD_USERNAME", "");
define("ARD_PASSWORD", "");

// Login for auphonic
define("AUPHONIC_USERNAME", "");
define("AUPHONIC_PASSWORD", "");
define("AUPHONIC_PRESET", "");

// Stream links
$streams = array(
	"Deutschlandfunk" => "http://dradio.ic.llnwd.net/stream/dradio_dlf_m_a",
	"hr2-kultur" => "http://hr-mp3-m-h2.akacast.akamaistream.net/7/786/142132/v1/gnl.akacast.akamaistream.net/hr-mp3-m-h2",
	"hr-iNFO" => "http://hr-mp3-m-hrinfo.akacast.akamaistream.net/7/698/142135/v1/gnl.akacast.akamaistream.net/hr-mp3-m-hrinfo",
	"Bayern 2" => "http://gffstream.ic.llnwd.net/stream/gffstream_w11a",
	"SWR 2" => "http://swr-mp3-m-swr2.akacast.akamaistream.net/7/721/137135/v1/gnl.akacast.akamaistream.net/swr-mp3-m-swr2",
	"WDR 2" => "http://wdr-mp3-m-wdr2-koeln.akacast.akamaistream.net/7/812/119456/v1/gnl.akacast.akamaistream.net/wdr-mp3-m-wdr2-koeln",
	"Bayern 2" => "http://gffstream.ic.llnwd.net/stream/gffstream_w14a"
);
