![Licence MIT](https://img.shields.io/github/license/francis94c/ci-twitter.svg) ![Splint](https://img.shields.io/badge/splint--ci-francis94c%2Fci--twitter-orange.svg) ![Lates Release](https://img.shields.io/github/release/francis94c/ci-twitter.svg) ![Commits](https://img.shields.io/github/last-commit/francis94c/ci-twitter.svg)
# ci-twitter
This is a Twitter API wrapper for Code Igniter.

Currently, this library can do the following

* Request Access Tokens
* Request Bearer Tokens
* Perform the 3 staged User Authentication
* Tweet.

## Installation ##
To install, download and install Splint from <https://splint.cynobit.com/downloads/splint> and then run the below from your Code Igniter project root.

```bash
splint install francis94c/ci-twitter
```
## Loading ##
From anywhere you can access the ```CI``` instance

```php
// Get the below from https://developer.twitter.com/en/apps
$params = array(
  "api_key"             => "api_key_here",
  "api_secret_key"      => "api_secret_key_here",
  "access_token"        => "access_token_here",
  "access_token_secret" => "access_token_secret_here",
  "verify_host"         => true
);
$this->load->splint("francis94c/ci-twitter", "+Twitter", $params, "twitter");
```

 OR Alternatively load with the below steps.

 * create a file ```twitter_config.php``` in your ```application/config``` folder.
 * Add the below contents into the file created above.

 ```php
 <?php
 defined('BASEPATH') OR exit('No direct script access allowed');

 $config["twitter_config"] = array(
   "api_key"             => "api_key_here",
   "api_secret_key"      => "api_secret_key_here",
   "access_token"        => "access_token_here",
   "access_token_secret" => "access_token_secret_here",
   "verify_host"         => true
 )
 ?>
 ```
* Then load with the single line of code.

```php
$this->load->package("francis94c/ci-twitter");
```
