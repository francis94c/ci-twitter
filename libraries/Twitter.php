<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Twitter {

  const PACKAGE          = "francis94c/ci-twitter";

  const CONSUMER_KEY     = "oauth_consumer_key";
  const SIGNATURE_METHOD = "oauth_signature_method";
  const OAUTH_VERSION    = "oauth_version";
  const OAUTH_CALLBACK   = "oauth_callback";
  const OAUTH_TOKEN      = "oauth_token";
  const OAUTH_VERIFIER   = "oauth_verifier";

  const OAUTH_ERROR_MSG  = "Necessary Tokens not Set: (api_key, api_secret_key, access_token, or access_token_secret). All must be set.";

  private $authorize_url = "https://api.twitter.com/oauth/authorize";

  private $api_key;
  private $api_secret_key;

  private $access_token;
  private $access_token_secret;

  private $verify_host;

  private $ci;

  function __construct($params=null) {
    if ($params != null) $this->initialize($params);
    $this->ci =& get_instance();
    // Load Dependencies.
    $this->ci->load->splint(self::PACKAGE, "+TwitterObjects");
  }
  /**
   * [initialize description]
   * @param  [type] $params [description]
   * @return [type]         [description]
   */
  function initialize($params) {
    if (isset($params["api_key"])) $this->api_key = $params["api_key"];
    if (isset($params["api_secret_key"])) $this->api_secret_key = $params["api_secret_key"];
    if (isset($params["access_token"])) $this->access_token = $params["access_token"];
    if (isset($params["access_token_secret"])) $this->access_token_secret = $params["access_token_secret"];
    if (isset($params["verify_host"])) $this->verify_host = $params["verify_host"];
  }
  /**
   * [setAccessToken description]
   * @param [type] $access_token [description]
   */
  function setAccessToken($access_token) {
    $this->access_token = $access_token;
  }
  /**
   * [setAccessTokenSecret description]
   * @param [type] $access_token_secret [description]
   */
  function setAccessTokenSecret($access_token_secret) {
    $this->access_token_secret = $access_token_secret;
  }
  /**
   * [setApiKey description]
   * @param [type] $api_key [description]
   */
  function setApiKey($api_key) {
    $this->api_key = $api_key;
  }
  /**
   * [setApiKeySecret description]
   * @param [type] $api_secret_key [description]
   */
  function setApiKeySecret($api_secret_key) {
    $this->api_secret_key = $api_secret_key;
  }
  /**
   * [requestToken description]
   * @param  [type] $callback [description]
   * @return [type]           [description]
   */
  function requestToken($callback=null) {
    $request = new TwitterCURLRequest("https://api.twitter.com/oauth/request_token",
      $this->api_secret_key, null, "POST", false);
    $request->addHeaderParameter(self::CONSUMER_KEY, $this->api_key);
    $request->addHeaderParameter(self::SIGNATURE_METHOD, "HMAC-SHA1");
    $request->addHeaderParameter(self::OAUTH_VERSION, "1.0");
    if ($callback != null) $request->addPostParameter(self::OAUTH_CALLBACK, $callback);
    return $request->execute();
  }
  /**
   * [getAuthorizeUrl description]
   * @param  [type] $oauth_token [description]
   * @return [type]              [description]
   */
  function getAuthorizeUrl($oauth_token) {
    return $this->authorize_url . "?" . self::OAUTH_TOKEN . "=$oauth_token";
  }
  /**
   * [getAccessToken description]
   * @param  [type] $oauth_token    [description]
   * @param  [type] $oauth_verifier [description]
   * @return [type]                 [description]
   */
  function getAccessToken($oauth_token, $oauth_verifier) {
    $request = new TwitterCURLRequest("https://api.twitter.com/oauth/access_token",
      $this->api_secret_key, null, "POST", false);
    $request->addHeaderParameter(self::CONSUMER_KEY, $this->api_key);
    $request->addHeaderParameter(self::SIGNATURE_METHOD, "HMAC-SHA1");
    $request->addHeaderParameter(self::OAUTH_VERSION, "1.0");
    $request->addHeaderParameter(self::OAUTH_TOKEN, $oauth_token);
    $request->addGetParameter(self::OAUTH_VERIFIER, $oauth_verifier);
    return $request->execute();
  }
  function tweet($tweet, $params=null) {
    if ($this->api_key == null || $this->api_secret_key == null ||
    $this->access_token == null || $this->access_token_secret == null) {
      throw new TwitterOAUTHException(self::OAUTH_ERROR_MSG);
    }
    $request = new TwitterCURLRequest("https://api.twitter.com/1.1/statuses/update.json",
      $this->api_secret_key, $this->access_token_secret, "POST");
    $request->addHeaderParameter(self::CONSUMER_KEY, $this->api_key);
    $request->addHeaderParameter(self::SIGNATURE_METHOD, "HMAC-SHA1");
    $request->addHeaderParameter(self::OAUTH_VERSION, "1.0");
    $request->addHeaderParameter(self::OAUTH_TOKEN, $this->access_token);
    $request->addPostParameter("status", $tweet);
    if ($params != null && $this->is_assoc($params)) $request->addPostParameter($params);
    return $request->execute();
  }
  /**
   * [getCredentials description]
   * @param  [type]  $oauth_token        [description]
   * @param  [type]  $oauth_token_secret [description]
   * @param  boolean $include_email      [description]
   * @param  [type]  $params             [description]
   * @return [type]                      [description]
   */
  function getCredentials($oauth_token=null, $oauth_token_secret=null, $include_email=true, $params=null) {
    $oauth_token = $oauth_token != null ? $oauth_token : $this->access_token;
    $oauth_token_secret = $oauth_token_secret != null ? $oauth_token_secret : $this->access_token_secret;
    if ($this->api_key == null || $this->api_secret_key == null ||
    $oauth_token == null || $oauth_token_secret == null) {
      throw new TwitterOAUTHException(self::OAUTH_ERROR_MSG);
    }
    $request = new TwitterCURLRequest("https://api.twitter.com/1.1/account/verify_credentials.json",
      $this->api_secret_key, $oauth_token_secret, "GET", false);
    $request->addHeaderParameter(self::CONSUMER_KEY, $this->api_key);
    $request->addHeaderParameter(self::SIGNATURE_METHOD, "HMAC-SHA1");
    $request->addHeaderParameter(self::OAUTH_VERSION, "1.0");
    $request->addHeaderParameter(self::OAUTH_TOKEN, $oauth_token);
    $request->addGetParameter("include_email", $include_email ? "true" : "false");
    if ($params != null) $request->addGetParameter($params);
    return $request->execute();
  }
  /**
   * [is_assoc description]
   * @param  [type]  $arr [description]
   * @return boolean      [description]
   */
  private function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
  }
}
?>
