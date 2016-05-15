==============
Flockbird とは
==============
* Flockbird は OSS の SNS エンジンです
* Flockbird は Framework に FuelPHP1.X を使用しています
* Licenseは MIT ライセンスです
* [Flockbird](http://uzura8.github.io/flockbird/)
* [Demo](http://demo.flockbird.uzuralife.com)

=======
動作環境
=======
* Apache(mod_rewrite を使用可能)
* PHP5.4 以上
* MySQL5.0 以上   

================
インストール方法
================

###　1. 設定ファイルの設置と編集 (config.php) ###

~~~~
$ cp config.php.sample config.php
$ vi config.php
~~~~


#### ドメイン設定(optional)

~~~~
define('FBD_DOMAIN', 'sns.example.com');// if use Internationalized Domain, set Punycode here.
~~~~
* task でメール配信する場合、ドメイン設定が必要になります


#### 設置パス設定(optional)

~~~~
define('FBD_URI_PATH', '/');// set setting path, if not set on document root
~~~~
* DocumentRoot 以外に設置する場合、設置パスを指定する
* git 管理ファイルが変更になるので、「2. セットアップスクリプトの実行」後、変更のコミットが必要


#### DBサーバ接続設定

~~~~
$GLOBALS['_FBD_DSN']['production'] = array(
  'default' => array(
    'connection'  => array(
      'dsn'        => 'mysql:host=localhost;dbname=dbname',
      'username'   => 'root',
      'password'   => '',
    ),
    'profiling' => true,
  ),
  'charset' => 'utf8',
);
~~~~
* MySQL で4バイト UTF8 を使用する場合は charset に utf8mb4 を指定する


#### 暗号化キーを指定(ASCII文字列)
~~~~
define('FBD_ENCRYPTION_KEY', 'put_some_key_for_encryption_in_here');
~~~~

### 2. セットアップスクリプトの実行 ###
~~~~
$ sh bin/setup/setup.sh
~~~~

### 3. cron 設定 ###
* /etc/crontab に以下を記述します。

~~~~
# 5 分ごとに新着お知らせメールを配信する
*/5 * * * * root php /path_to_Flockbird/oil r notice::sendmail > /dev/null
*/5 * * * * root php /path_to_Flockbird/oil r message::sendmail > /dev/null
~~~~


### オプション設定項目 ###

#### アップロードファイルの保存場所をAWS S3 にする場合

composer.json の require に以下の行を追加。

~~~~
"aws/aws-sdk-php": "2.*"
~~~~

config.php にて、AWSの設定を行う。

~~~~
// AWS 関連設定
define('FBD_AWS_ACCESS_KEY', '');
define('FBD_AWS_SECRET_KEY', '');
define('FBD_AWS_S3_BUCKET', '');
define('FBD_AWS_S3_PATH', '');
~~~~

