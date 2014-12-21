<?php
/**
 * Unicode Normalizer
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
 * @version   SVN: $Id: UnormVsMediawiki.php 38 2007-07-23 11:42:30Z mcorne $
 * @link      http://pear.php.net/package/I18N_UnicodeNormalizer
 */
// Call tests_Benchmark_UnormVsMediawiki::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "tests_Benchmark_UnormVsMediawiki::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";
require_once 'I18N/UnicodeNormalizer.php';
require_once 'I18N/UnicodeNormalizer/String.php';
require_once 'I18N/UnicodeNormalizer/File.php';

require_once dirname(__FILE__) . '/mediawiki/UtfNormal.php';

/**
 * Test class to benchmark against the Mediawiki normalizer
 *
 * The following flags can be adjusted to customize the tests:
 * self::$textFiles and self::$denormalizationRatio.
 * The result of the benchmark test is in reports/benchmark.
 *
 * The performance improvement vs the Mediawiki normalizer is:
 * 2.5X on already normalized text, 9X on 0.1% denormalized texts, 3.5X on 1%
 * denormalized text. The benchmark is run on 12 major languages.
 *
 * @category  Internationalization
 * @package   I18N_UnicodeNormalizer
 * @author    Michel Corne <mcorne@yahoo.com>
 * @copyright 2007 Michel Corne
 * @license   http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/I18N_UnicodeNormalizer
 */
class tests_Benchmark_UnormVsMediawiki extends PHPUnit_Framework_TestCase
{
    /**
     * The result file header comment
     */
    const headerComment = 'Normalizer Benchmark test vs MediaWiki. Denormalization ratio = %s';

    /**
     * The text files paths
     *
     * @var    string 
     * @access private
     */
    private $filePaths = '';

    /**
     * The text files to normalize
     *
     * For examples:
     * * : the benchmark test normalizes all files
     * english : the benchmark test normalizes the english.txt file only
     *
     * @var    string
     * @access public
     * @static
     */
    public static $textFiles = '*';

    /**
     * The denormalization ratio of the data files
     *
     * For example:
     * 0 = no denormalization
     * 0.01 = 1 every 100 characters is denormalized
     *
     * @var    float 
     * @access public
     * @static
     */
    public static $denormalizationRatio = 0.001;

    /**
     * The name list of compiled files
     *
     * @var    array  
     * @access private
     */
    private $compiled;

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite = new PHPUnit_Framework_TestSuite("tests_Benchmark_UnormVsMediawiki");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        // creates the data files pathnames
        $dir = dirname(__FILE__);
        $this->filePaths = $dir . '/data/' . self::$textFiles . '.txt';
        // creates the test results file name
        $dir = dirname($dir);
        $this->resultFile =  sprintf("$dir/reports/benchmark/UnormVsMediawiki-%s.php", self::$denormalizationRatio);
        // gets the compiled file names
        $this->compiled = I18N_UnicodeNormalizer::getFileNames("$dir/data");

        $this->string = new I18N_UnicodeNormalizer_String();
        $this->file = new I18N_UnicodeNormalizer_File();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    /**
     * Computes the time and performance averages
     *
     * @param  array   $conso the consolidated list of times and performances
     *                        for all data files
     * @return array   the average time and performance
     * @access private
     */
    private function calcAverage($conso)
    {
        $averages = array();
        foreach($conso as $type => $typeConso) {
            foreach($typeConso as $key => $values) {
                $avg = array_sum($values) / count($values);
                $averages[$type][$key] = round($avg, 1);
            }
        }

        return $averages;
    }

    /**
     * Denormalizes a string
     *
     * @param  string  $string      the string to denormalize
     * @param  array   $denormChars the set of denormalized characters
     * @return string  the denormlized string
     * @access private
     */
    private function denormString($string, $denormChars)
    {
        if (self::$denormalizationRatio) {
            // denormalizes the string, randomizes the character to denormalize
            $maxRandom = 1 / abs(self::$denormalizationRatio) - 1;
            $frequency = rand(0, $maxRandom);

            $denormString = '';
            $length = strlen($string);

            for($i = 0; $i < $length;) {
                $char = $this->string->getChar($string, $i, $length);

                if ($frequency--) {
                    $denormString .= $char;
                } else {
                    $char = next($denormChars);
                    $char === false and $char = reset($denormChars);
                    $denormString .= $char;
                    // denormalizes the string, randomizes the character to denormalize
                    $frequency = rand(0, $maxRandom);
                }
            }

            return $denormString;
        } else {
            // no denormalization
            return $string;
        }
    }

    /**
     * Gets denormalized characters from the test file
     *
     * @return array   the list of denormalized characters
     * @access private
     * @see    tests_UnicodeNormalizerTest:rules
     */
    private function getDenormChars()
    {
        // sets the NFC rule
        $NFCRule = array(1 => 2, 2 => 2, 3 => 2, 4 => 4, 5 => 4);

        $chars = array();
        foreach(require $this->compiled['test_base'] as $columns) {
            foreach($NFCRule as $i => $j) {
                $columns[$i] != $columns[$j] and $chars[] = $columns[$i];
            }
        }

        $chars = array_unique($chars);
        shuffle($chars);

        return $chars;
    }

    /**
     * Times the normalization
     *
     * @param  string  $class  the name of the class/normalizer to use
     * @param  string  $type   the type of normalization: 'NFC', 'NFD', 'NFKC',  or 'NFKD'
     * @param  string  $string the string to normalize
     * @return array   the normalized string and the time to normalize the string
     * @access private
     */
    private function timeNormalize($class, $type, $string)
    {
        // starts the timer,  normalizes the string
        $start = microtime(true);
        $normalized = call_user_func(array($class, "to$type"), $string);
        // captures the elapsed time to normalize
        $time = microtime(true) - $start;

        return array($normalized, $time);
    }

    /**
     * Benchmarks the normalization against the Mediawiki normalization
     *
     * @return void  
     * @access public
     */
    public function testUnormVsMediawiki()
    {
        // gets denormalized characters from the test file
        $denormChars = $this->getDenormChars();

        $results = array('summary' => array(), 'details' => array());
        $conso = array();
        foreach(glob($this->filePaths) as $fileName) {
            $baseName = basename($fileName, '.txt');
            $string = file_get_contents($fileName);
            $string = strip_tags($string);
            $string = $this->denormString($string, $denormChars);

            foreach(array('NFC', 'NFD', 'NFKC', 'NFKD') as $type) {
                // normalizes with the package class, and the mediawiki class, calculates the performance
                list($normStr0, $time0) = $this->timeNormalize('I18N_UnicodeNormalizer', $type, $string);
                list($normStr1, $time1) = $this->timeNormalize('UtfNormal', $type, $string);
                $performance = $time1 / $time0;
                // captures results, checks that both normalizations are the same
                $results['details'][$baseName][$type] = array(// /
                    'performance (X)' => round($performance, 1),
                    'same' => $normStr0 == $normStr1,
                    'I18N_UnicodeNormalizer' => array('time (s)' => round($time0, 1),
                        'is_normalized' => $normStr0 == $string),
                    'UtfNormal' => array('time (s)' => round($time1, 1),
                        'is_normalized' => $normStr1 == $string),
                    );
                // captures the performance for significant times
                $conso[$type]['performance (X)'][] = $performance;
                $conso[$type]['I18N_UnicodeNormalizer (s)'][] = $time0;
                $conso[$type]['UtfNormal (s)'][] = $time1;
            }
        }
        // calculates the performance averages for all lanaguages
        $results['summary'] = $this->calcAverage($conso);
        // updates the results file
        $comment = sprintf(self::headerComment, self::$denormalizationRatio);
        $this->file->put($this->resultFile, $results, __CLASS__ . '::' . __FUNCTION__, $this->filePaths, $comment);
    }
}
// Call tests_Benchmark_UnormVsMediawiki::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "tests_Benchmark_UnormVsMediawiki::main") {
    tests_Benchmark_UnormVsMediawiki::main();
}

?>