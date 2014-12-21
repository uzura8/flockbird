<?php
/**
 * Unicode Normalizer
 *
 * File: D:\Data\dev\i18n-unicodnorm\trunk\I18N\tests/reports/benchmark/UnormVsMediawiki-0.001.php
 * Generated automatically by: tests_Benchmark_UnormVsMediawiki::testUnormVsMediawiki
 * From: D:\Data\dev\i18n-unicodnorm\trunk\I18N\tests\benchmark/data/english.txt
 * Date: Wednesday, 04-Jul-07 11:56:32 UTC
 * Normalizer Benchmark test vs MediaWiki. Denormalization ratio = 0.001
 * DO NOT MODIFY !
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
 * @category Internationalization
 * @package I18N_UnicodeNormalizer
 * @author Michel Corne <mcorne@yahoo.com>
 * @copyright 2007 Michel Corne
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version SVN: $Id: UnormVsMediawiki-0.001.php 29 2007-07-04 16:01:23Z mcorne $
 * @link http://pear.php.net/package/I18N_UnicodeNormalizer
 */

return array (
  'summary' => 
  array (
    'NFC' => 
    array (
      'performance (X)' => 0.9,
      'I18N_UnicodeNormalizer (s)' => 4.5,
      'UtfNormal (s)' => 4.1,
    ),
    'NFD' => 
    array (
      'performance (X)' => 1.7,
      'I18N_UnicodeNormalizer (s)' => 1.5,
      'UtfNormal (s)' => 2.5,
    ),
    'NFKC' => 
    array (
      'performance (X)' => 0.7,
      'I18N_UnicodeNormalizer (s)' => 5.7,
      'UtfNormal (s)' => 3.8,
    ),
    'NFKD' => 
    array (
      'performance (X)' => 1.5,
      'I18N_UnicodeNormalizer (s)' => 1.7,
      'UtfNormal (s)' => 2.5,
    ),
  ),
  'details' => 
  array (
    'english' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 0.9,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 4.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 4.1,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 1.7,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.5,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 0.7,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 5.7,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 3.8,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 1.5,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1.7,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.5,
          'is_normalized' => false,
        ),
      ),
    ),
  ),
);
?>
