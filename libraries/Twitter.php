<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Twitter {

  const PACKAGE          = "francis94c/ci-twitter";

  const CONSUMER_KEY     = "oauth_consumer_key";
  const SIGNATURE_METHOD = "oauth_signature_method";
  const OAUTH_VERSION    = "oauth_version";
  const OAUTH_CALLBACK   = "oauth_callback";
  const OAUTH_TOKEN      = "oauth_token";

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
   * [requestToken description]
   * @param  [type] $callback [description]
   * @return [type]           [description]
   */
  function requestToken($callback=null) {
    $request = new TwitterCURLRequest("https://api.twitter.com/oauth/request_token", $this->api_secret_key, null, "POST", false);
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
  function tweet($tweet) {
    
  }
}
?>
