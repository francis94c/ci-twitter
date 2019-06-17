![License MIT](https://img.shields.io/github/license/francis94c/ci-twitter.svg) ![Splint Identifier](https://splint.cynobit.com/shields/iconIdentifier/5TR612RF6Y) ![Splint Version](https://splint.cynobit.com/shields/iconVersion/5TR612RF6Y) ![Latest Release](https://img.shields.io/github/release/francis94c/ci-twitter.svg) ![Commits](https://img.shields.io/github/last-commit/francis94c/ci-twitter.svg)

![Twitter Logo](https://help.twitter.com/content/dam/help-twitter/brand/logo.png)

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

// OR call initialize() with parameters.

$this->load->splint("francis94c/ci-twitter", "+Twitter", null, "twitter");
$this->twitter->initalize($params)

```

 OR Alternatively load with the below steps.

 * create a file ```twitter_config.php``` in your ```application/config``` folder.
 * Add the below contents into the file created above.

```php
defined('BASEPATH') OR exit('No direct script access allowed');

$config["twitter_config"] = array(
  "api_key"             => "api_key_here",
  "api_secret_key"      => "api_secret_key_here",
  "access_token"        => "access_token_here",
  "access_token_secret" => "access_token_secret_here",
  "verify_host"         => true
)
```
* Then load with the single line of code.

```php
$this->load->package("francis94c/ci-twitter");
```
## Usage ##

## 3 Staged Authentication Process

After loading the package from a controller, call the function below

```php
$token = $this->twitter->requestToken("https://call_back_url"); //  See https://developer.twitter.com/en/docs/basics/apps/guides/callback-urls.html for call back urls
if ($token["oauth_callback_confirmed"] == true) redirect($this->twitter->getAuthorizeUrl($token["oauth_token"]));
```

This will redirect the user to the twitter authorization page. If Authorization is successful, a request will be made to the callback URL of your Twitter  App.

In the controller that handles the callback you can then get the access token with the returned ```oauth_token``` and the ```oauth_verifier``` with the code below

```php
$access_token = $this->twitter->getAccessToken($oauth_token, $oauth_verifier);

var_dump($access_token);

// Output:
// array (
//   "oauth_token"        => "6253282-eWudHldSbIaelX7swmsiHImEL4KinwaGloHANdrY",
//   "oauth_token_secret" => "2EEfA6BG3ly3sR3RjE0IBSnlQu4ZrUzPiYKmrkVU",
//   "user_id"            => "6253282",
//   "screen_name"        => "twitterapi"
// )

// You can then use the oauth_token and oauth_token_secret for requests that need /////user authentication.
```

## Get a User's Info ##

To get a user's info which is most likely needed just after the 3 staged authentication process when implementing a sign up or sign in with twitter, use the below code the request a user's credential using an `oauth_token` and an `oauth_token_secret`.

```php
$credentials = $this->twitter->getCredentials($oauth_token, $oauth_token_secret);
```

See https://developer.twitter.com/en/docs/accounts-and-users/manage-account-settings/api-reference/get-account-verify_credentials.html for the key-value structure of ```$credentials``` response variable.

## Tweet ##

To tweet, you simply need an `api_key`,` api_secret_key`, `access_token` and an `access_token_secret`

Note that ```access_token``` and ```access_token_secret``` is also referred to as ```oauth_token``` and ```oauth_token_secret```. If you've already initialized the API with these values, you can go ahead and tweet with the below code.

```php
$this->twitter->tweet("A Sample Tweet Here.");
```

This will return `true` if successful, or `false` otherwise.

Please see the Wiki at <https://github.com/francis94c/ci-twitter/wiki> for proper documentation of package methods.
