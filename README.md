About Flockbird
===============
* Flockbird is an OSS SNS Engine.
* Flockbird is builted by PHP Framework FuelPHP1.8
* License is MIT.
* [Flockbird](https://uzura8.github.io/flockbird/)
* [Demo](https://demo.flockbird.uzura.work)

Environment
============
* Apache2.2 or more (need mod_rewrite)
* PHP5.4 or more
* MySQL5.0 or more

Getting Started
===============

### 1. Set and edit config file (config.php) ###

~~~~
$ cp config.php.sample config.php
$ vi config.php
~~~~


#### Domain setting (optional)

~~~~
define('FBD_DOMAIN', 'sns.example.com');// if use Internationalized Domain, set Punycode here.
~~~~
* If you send mails by task, You need this.


#### Path setting (optional)

~~~~
define('FBD_URI_PATH', '/');// set setting path, if not set on document root
~~~~
* If you set at other than DocumentRoot、you need this.
* You need git commit after step "2. Run script to setup", because a file under managed by git is changed.


#### DB Connectuion setting

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
* If use 4byte UTF8 charset on MySQL, set utf8mb4 at charset section.


#### Encryption Key setting (by ASCII chars)
~~~~
define('FBD_ENCRYPTION_KEY', 'put_some_key_for_encryption_in_here');
~~~~

### 2. Run script to setup ###
~~~~
$ sh bin/setup/setup.sh
~~~~

### 3. Cron setting ###
* Add the following on /etc/crontab

~~~~
# Send notification mail by 5 minutes
*/5 * * * * root php /path_to_Flockbird/oil r notice::sendmail > /dev/null
*/5 * * * * root php /path_to_Flockbird/oil r message::sendmail > /dev/null
~~~~


### Optional settings ###

#### If save uploaded files on AWS S3

Add the following in composer.json

~~~~
"aws/aws-sdk-php": "2.*"
~~~~

Set about AWS on config.php

~~~~
// AWS settings
define('FBD_AWS_ACCESS_KEY', '');
define('FBD_AWS_SECRET_KEY', '');
define('FBD_AWS_S3_BUCKET', '');
define('FBD_AWS_S3_PATH', '');
~~~~

