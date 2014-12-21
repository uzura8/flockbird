<?php
/**
 * Unicode Normalizer manual/unit test.
 *
 * Run the test from the tests directory.
 * #php normtest.php
 *
 * PHP version 5
 *
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * + Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * + Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation and/or
 * other materials provided with the distribution.
 * + The names of its contributors may not be used to endorse or
 * promote products derived from this software without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Internationalization
 * @package   I18N_UnicodeNormalizer
 * @author    Michel Corne <mcorne@yahoo.com>
 * @copyright 2007 Michel Corne
 * @license   http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version   SVN: $Id: normtest.php 37 2007-07-21 15:08:26Z mcorne $
 * @link      http://pear.php.net/package/I18N_UnicodeNormalizer
 */
// /
// adds the path of the package if this it is a (proper/SVN) developer package install
file_exists("../../I18N/") and set_include_path('../..' . PATH_SEPARATOR . get_include_path());
// /
require_once dirname(__FILE__) . '/benchmark/mediawiki/UtfNormal.php';
require_once 'I18N/UnicodeNormalizer.php';
require_once 'I18N/UnicodeNormalizer/String.php';
require_once 'I18N/UnicodeNormalizer/File.php';

$string = new I18N_UnicodeNormalizer_String;
$normalizer = new I18N_UnicodeNormalizer();

$GLOBALS['UNORM_DBG'] = true;
$trace = array();
$objects = array();

function processArgs($args, $toProcess = null)
{
    global $string;

    is_array($args) or $args = array($args);
    // captures the arguments to process, TRUE means all
    $toProcess === true and $toProcess = array_keys($args) or
    is_null($toProcess) and $toProcess = array() or
    is_array($toProcess) or $toProcess = array($toProcess);
    // processes the arguments
    $processed = array();
    foreach($toProcess as $idx) {
        isset($args[$idx]) and $processed[$idx] = $string->string2unicode($args[$idx]);
    }

    return $processed;
}

function dbgTraceArgs($argsToProcess = array(), $args = array())
{
    global $trace;
    global $objects;
    static $callID = -1;

    $callID++;
    // traces the current function call (calling this function)
    $backtrace = debug_backtrace();
    array_shift($backtrace);
    isset($backtrace[0]['function']) and $backtrace[0]['function'] == 'dbgTraceReturn' and array_shift($backtrace);
    $current = current($backtrace);

    if ($args) {
        // arguments are passed separately
        // used mainly to capture arguments passed by reference due to PHP Bug #42058
        is_array($args) or $args = array($args);
        $current['args'] = $args;
    } else {
        // captures the arguments from the debug trace
        $args = isset($current['args'])? array_values($current['args']) : array();
    }
    // processes the arguments
    $processed = processArgs($args, $argsToProcess) and $current['args_p'] = $processed;

    if (isset($current['object'])) {
        // captures the object details, or makes a reference to it
        ($key = array_search($current['object'], $objects)) !== false and
        $current['object'] = "object already captured in entry #$key";
        $objects[$callID] = $current['object'];
        // removes the object from the trace
        unset($current['object']);
    }

    $trace[$callID]['in'] = $current;

    return $callID;
}

function dbgTraceReturn($callID, $returns = array(), $returnsToProcess = array(),
    $argsToProcess = array(), $args = array())
{
    global $trace;

    if (is_null($callID)) {
        // arguments not traced yet
        // mainly used as a short cut for functions without embedded functions to trace
        $callID = dbgTraceArgs($argsToProcess, $args);
    }
    // traces the function file and line details
    $backtrace = debug_backtrace();
    $backtrace = current($backtrace);
    $current['file'] = $backtrace['file'];
    $current['line'] = $backtrace['line'];

    if (isset($trace[$callID])) {
        // the arguments are already traced
        is_array($returns) or $returns = array($returns);
        // captures and process the returned parameters
        $current['returns'] = $returns;
        $processed = processArgs($returns, $returnsToProcess) and $current['returns_p'] = $processed;
    } else {
        // wrong call ID
        // trace the current function + file + line TO DO !!!
        $current['ERROR'] = "Wrong Call ID: $callID";
    }

    $trace[$callID]['out'] = $current;
}

$test = array(// /
    'type' => 'NFD',
    'string_ucn' => '\u0044\u0307\u0323', // the string to test
    'string_utf8' => null,
    'normed_ucn' => null,
    'normed_utf8' => null,
    'char_info' => null,
    );

$test['string_utf8'] = $string->unicode2string($test['string_ucn']);
$test['normed_utf8'] = $normalizer->normalize($test['string_utf8'], $test['type']);
$test['normed_ucn'] = $string->string2unicode($test['normed_utf8']);

$test['char_info'] = $normalizer->getCharInfo($test['string_utf8'], $test['type']);

print_r($test);

$test['dbg_trace'] = $trace;
$test['objects'] = $objects;
// sets the default time zone to UTC
date_default_timezone_set('UTC');
ob_start();
phpinfo();
$test['phpinfo'] = ob_get_contents();
ob_end_clean();

file_put_contents('reports/debug.txt', print_r($test, true));

?>