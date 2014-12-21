<?php
/**
 * Unicode Normalizer
 *
 * File: tests/reports/benchmark/UnormVsMediawiki-0.01.php
 * Generated automatically by: tests_Benchmark_UnormVsMediawiki::testUnormVsMediawiki
 * From: tests/benchmark/data/*.txt
 * Date: Saturday, 09-Jun-07 13:54:08 UTC
 * Normalizer Benchmark test vs MediaWiki. Denormalization ratio = 0.01
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
 * @version SVN: $Id: UnormVsMediawiki-0.01.php 17 2007-07-02 09:16:11Z mcorne $
 * @link http://pear.php.net/package/I18N_UnicodeNormalizer
 */

return array (
  'summary' => 
  array (
    'NFC' => 
    array (
      'performance (X)' => 3.6,
      'I18N_UnicodeNormalizer (s)' => 0.6,
      'UtfNormal (s)' => 2.1,
    ),
    'NFD' => 
    array (
      'performance (X)' => 2.7,
      'I18N_UnicodeNormalizer (s)' => 0.6,
      'UtfNormal (s)' => 1.3,
    ),
    'NFKC' => 
    array (
      'performance (X)' => 3.7,
      'I18N_UnicodeNormalizer (s)' => 0.6,
      'UtfNormal (s)' => 2.1,
    ),
    'NFKD' => 
    array (
      'performance (X)' => 2.6,
      'I18N_UnicodeNormalizer (s)' => 0.6,
      'UtfNormal (s)' => 1.4,
    ),
  ),
  'details' => 
  array (
    'arabic' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 2.3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.4,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 1.8,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.8,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.5,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 2.5,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.3,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 1.8,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.8,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.5,
          'is_normalized' => false,
        ),
      ),
    ),
    'english' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 3.9,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.8,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 4.6,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.3,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 4.6,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.4,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.9,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 3.9,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.3,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
      ),
    ),
    'french' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 3.9,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 2.5,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 3.8,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.9,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 2.3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
      ),
    ),
    'german' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 3.7,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.9,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 3.3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.4,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 3.7,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.9,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 3.4,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.4,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.3,
          'is_normalized' => false,
        ),
      ),
    ),
    'japanese' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 2.3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.3,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 1.3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 0.8,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 2.1,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 1.3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 0.8,
          'is_normalized' => false,
        ),
      ),
    ),
    'javanese' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 3.7,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.2,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 4.1,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.4,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.5,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 3.6,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.2,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 4.4,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.3,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.5,
          'is_normalized' => false,
        ),
      ),
    ),
    'korean' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 7.5,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 3.7,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 1.2,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1.7,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.1,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 8,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
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
        'performance (X)' => 1.3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1.7,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.2,
          'is_normalized' => false,
        ),
      ),
    ),
    'mandarin' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 2.5,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.4,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.1,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 1.5,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 0.7,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 2.2,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.1,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 1.5,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 0.7,
          'is_normalized' => false,
        ),
      ),
    ),
    'russian' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 2.8,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 3.3,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 1.9,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1.1,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.2,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 2.8,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 3.3,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 2,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 1.1,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.2,
          'is_normalized' => false,
        ),
      ),
    ),
    'spanish' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 4,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.8,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 3.2,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.4,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 4,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.9,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.4,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.2,
          'is_normalized' => false,
        ),
      ),
    ),
    'tagalog' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 3.6,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.1,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 4.5,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.3,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.4,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 3.7,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 2.1,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 4.4,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.3,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.5,
          'is_normalized' => false,
        ),
      ),
    ),
    'turkish' => 
    array (
      'NFC' => 
      array (
        'performance (X)' => 3.6,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.8,
          'is_normalized' => false,
        ),
      ),
      'NFD' => 
      array (
        'performance (X)' => 2.1,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.1,
          'is_normalized' => false,
        ),
      ),
      'NFKC' => 
      array (
        'performance (X)' => 3.4,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.5,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.6,
          'is_normalized' => false,
        ),
      ),
      'NFKD' => 
      array (
        'performance (X)' => 2.3,
        'same' => true,
        'I18N_UnicodeNormalizer' => 
        array (
          'time (s)' => 0.6,
          'is_normalized' => false,
        ),
        'UtfNormal' => 
        array (
          'time (s)' => 1.3,
          'is_normalized' => false,
        ),
      ),
    ),
  ),
);
?>
