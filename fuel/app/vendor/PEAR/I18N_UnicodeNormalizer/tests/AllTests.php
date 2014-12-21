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
 * @version   SVN: $Id: AllTests.php 38 2007-07-23 11:42:30Z mcorne $
 * @link      http://pear.php.net/package/I18N_UnicodeNormalizer
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'I18N_UnicodeNormalizer_AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
// adds the path of the package if this is a raw install
file_exists("../../I18N/") and set_include_path('../..' . PATH_SEPARATOR . get_include_path());
require_once 'UnicodeNormalizerTest.php';
require_once 'UnicodeNormalizer/CompilerTest.php';
require_once 'UnicodeNormalizer/FileTest.php';
require_once 'UnicodeNormalizer/StringTest.php';
require_once 'benchmark/UnormVsMediawiki.php';

/**
 * You may want to customize the tests below
 */
tests_UnicodeNormalizerTest::$runNormalizationTest = true;
tests_UnicodeNormalizerTest::$forceCompile = false;
tests_UnicodeNormalizerTest::$generateUCN = false;

tests_UnicodeNormalizerTest::$mediawikiNormalization = false;

I18N_UnicodeNormalizer_AllTests::$runBenchmark = false;
tests_Benchmark_UnormVsMediawiki::$textFiles = '*'; // * | english | french | ...
tests_Benchmark_UnormVsMediawiki::$denormalizationRatio = 0.001; // 0 | 0.001 | 0.01 | ...
// /
// IMPORTANT!!! set $codeCoverageTest to true before releasing the package
// otherwise phpunit will crash during the QA PEAR-wide unit test
// this is due to the large unicode/compiled data files
// when phpunit runs with the --report option
// /
tests_UnicodeNormalizerTest::$codeCoverageTest = true;

/**
 * Test suite
 *
 * Run the tests from the tests directory.
 * #phpunit I18N_UnicodeNormalizer_AllTests AllTests.php
 *
 * To run the code coverage test, 3 steps:
 * set tests_UnicodeNormalizerTest::$codeCoverageTest to true
 * #phpunit --report reports/coverage I18N_UnicodeNormalizer_AllTests AllTests.php
 * browse the results in index.html file in reports/coverage
 *
 * The code coverage is close to 100%, except for a few die() statements in
 * Compiler.php (which do not justify a proper exception mechanism btw), a few lines in
 * String.php which can only be run on 64 bits machines, a few require() statements
 * in UnicodeNormalizer.php that load the same data in different ways and a few
 * lines that are theoritical but non pratical cases.
 *
 * Test results are located in the reports directory: benchmark, coverage, regression.
 *
 * @category  Internationalization
 * @package   I18N_UnicodeNormalizer
 * @author    Michel Corne <mcorne@yahoo.com>
 * @copyright 2007 Michel Corne
 * @license   http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/I18N_UnicodeNormalizer
 * @see       UnicodeNormalizerTest.php
 */
class I18N_UnicodeNormalizer_AllTests
{
    public static $runBenchmark;

    /**
     * Runs the test suite
     *
     * @return void  
     * @access public
     * @static
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Runs the test suite
     *
     * @return object the PHPUnit_Framework_TestSuite object
     * @access public
     * @static
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('I18N_UnicodeNormalizer Tests');

        $suite->addTestSuite('tests_UnicodeNormalizer_FileTest');
        $suite->addTestSuite('tests_UnicodeNormalizer_StringTest');
        $suite->addTestSuite('tests_UnicodeNormalizer_CompilerTest');
        $suite->addTestSuite('tests_UnicodeNormalizerTest');
        // do not run the benchmark while running the code coverage test
        // see note in UnicodeNormalizerTest.php
        tests_UnicodeNormalizerTest::$codeCoverageTest or
        self::$runBenchmark and $suite->addTestSuite('tests_Benchmark_UnormVsMediawiki');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'I18N_UnicodeNormalizer_AllTests::main') {
    I18N_UnicodeNormalizer_AllTests::main();
}

?>