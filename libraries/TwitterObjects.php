<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * [TwitterObjects description]
 */
class TwitterObjects {

}

/**
 * [TwitterOAUTHException description]
 */
class TwitterOAUTHException extends Exception {}

/**
 * [TwitterCURLRequest description]
 */
class TwitterCURLRequest {

  const OAUTH_NONCE          = "oauth_nonce";
  const OAUTH_TIMESTAMP      = "oauth_timestamp" ;
  const OAUTH_SIGNATURE      = "oauth_signature";

  private $request_method;
  private $url;

  private $get_parameters    = array();
  private $header_parameters = array();
  private $post_parameters   = array();
  private $custom_headers    = array();
  private $oauth_nonce;
  private $oauth_timestamp;

  private $consumer_secret;
  private $token_secret;

  private $verify_host       = true;

  private $last_response;

  private $ci;

  private $ch;

  function __construct($url, $consumer_secret=null, $token_secret=null, $request_method="POST", $verify_host=true) {
    $this->request_method = $request_method;
    $this->ci =& get_instance();
    $this->ci->load->helper("string");
    if ($consumer_secret != null) $this->consumer_secret = $consumer_secret;
    if ($token_secret != null) $this->token_secret = $token_secret;
    if ($url != null) $this->url = $url;
    $this->verify_host = $verify_host;
  }
  /**
   * [addGetParameter description]
   * @param [type] $key [description]
   * @param [type] $val [description]
   */
  function addGetParameter($key, $val=null) {
    if ($val == null && is_array($key)) {
      array_merge($this->get_parameters, $key);
      return;
    }
    $this->get_parameters[$key] = $val;
  }
  /**
   * [addHeaderParameter description]
   * @param [type] $key [description]
   * @param [type] $val [description]
   */
  function addHeaderParameter($key, $val=null) {
    if ($val == null && is_array($key)) {
      array_merge($this->header_parameters, $key);
      return;
    }
    $this->header_parameters[$key] = $val;
  }
  /**
   * [addPostParameter description]
   * @param [type] $key [description]
   * @param [type] $val [description]
   */
  function addPostParameter($key, $val=null) {
    if ($val == null && is_array($key)) {
      array_merge($this->post_parameters, $key);
      return;
    }
    $this->post_parameters[$key] = $val;
  }
  /**
   * [addCustomHeader description]
   * @param [type] $key [description]
   * @param [type] $val [description]
   */
  function addCustomHeader($key, $val) {
    $this->custom_headers[$key] = $val;
  }
  /**
   * [setRequestMethod description]
   * @param string $request_method [description]
   */
  function setRequestMethod($request_method) {
    $this->request_method = $request_method;
  }
  /**
   * [setUrl description]
   * @param [type] $url [description]
   */
  function setUrl($url) {
    $this->url = $url;
  }
  /**
   * [execute description]
   * @return [type] [description]
   */
  function execute() {
    if (count($this->header_parameters) > 0) {
      $base_string = $this->build_signature_base_string();
      $this->header_parameters[self::OAUTH_SIGNATURE] = base64_encode(hash_hmac("sha1", $base_string, rawurlencode($this->consumer_secret) . "&" . ($this->token_secret != null ? rawurlencode($this->token_secret) : ""), true));
    }
    $this->ch = curl_init();
    curl_setopt($this->ch, CURLOPT_URL, $this->url . (count($this->get_parameters) > 0 ? $this->stringify_get_parameters() : ""));
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->request_method));
    if (!$this->verify_host) {
      curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    $header = array();
    if (count($this->header_parameters) > 0) {
      $header[] = 'Authorization: ' . $this->build_header_string();
    }
    if (count($this->custom_headers) > 0) {
      foreach ($this->custom_headers as $key => $value) {
        $header[] = $key . ": " . $value;
      }
    }
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->stringify_post_parameters());
    $data = curl_exec($this->ch);
    $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    curl_close($this->ch);
    if ($http_code != 200) return false;
    $this->last_response = $this->objectify($data);
    return $this->last_response;
  }
  /**
   * [objectify description]
   * @param  [type] $data [description]
   * @return [type]       [description]
   */
  private function objectify($data) {
    if ($data == null) return null;
    $object = json_decode($data, true);
    if ($object != null) return $object;
    $object = explode("&", $data);
    $json = array();
    foreach ($object as $item) {
      $json[explode("=", $item)[0]] = (explode("=", $item)[1] == "true" ? true : explode("=", $item)[1]);
    }
    return $json;
  }
  /**
   * [stringify_get_parameters description]
   * @return [type] [description]
   */
  private function stringify_get_parameters() {
    $parameter_keys = array_keys($this->get_parameters);
    $last_key = end($parameter_keys);
    $parameter_string = "?";
    foreach ($this->get_parameters as $key => $value) {
      $parameter_string .= rawurlencode($key) . "=" . rawurlencode($value);
      if ($last_key !== $key) $parameter_string .= "&";
    }
    return $parameter_string;
  }
  /**
   * [stringify_post_parameters description]
   * @return [type] [description]
   */
  private function stringify_post_parameters() {
    $parameter_keys = array_keys($this->post_parameters);
    $last_key = end($parameter_keys);
    $parameter_string = "";
    foreach ($this->post_parameters as $key => $value) {
      $parameter_string .= rawurlencode($key) . "=" . rawurlencode($value);
      if ($last_key !== $key) $parameter_string .= "&";
    }
    return $parameter_string;
  }
  /**
   * [ready_request description]
   * @return [type] [description]
   */
  private function ready_request() {
    if ($this->oauth_nonce == null) {
      $this->oauth_nonce = base64_encode(random_string("alpha", 32));
      $this->oauth_timestamp = time();
      $this->header_parameters[self::OAUTH_NONCE] = $this->oauth_nonce;
      $this->header_parameters[self::OAUTH_TIMESTAMP] = $this->oauth_timestamp;
    }
  }
  /**
   * [build_header_string description]
   * @return [type] [description]
   */
  private function build_header_string() {
    $string_header = "OAuth ";
    for ($x = 0; $x < count($this->header_parameters); $x++) {
      $string_header .= rawurlencode(array_keys($this->header_parameters)[$x]);
      $string_header .= "=";
      $string_header .= "\"" . rawurlencode(array_values($this->header_parameters)[$x]) . "\"";
      if ($x != count($this->header_parameters) - 1) $string_header .= ", ";
    }
    return $string_header;
  }
  /**
   * [build_signature_base_string description]
   * @return [type] [description]
   */
  private function build_signature_base_string() {
    $this->ready_request();
    $parameters = array_merge($this->get_parameters, $this->header_parameters, $this->post_parameters);
    ksort($parameters);
    $parameter_string = "";
    $parameter_keys = array_keys($parameters);
    $last_key = end($parameter_keys);
    foreach ($parameters as $key => $value) {
      $parameter_string .= rawurlencode($key) . "=" . rawurlencode($value);
      if ($last_key !== $key) { $parameter_string .= "&"; } else { break; }
    }
    return strtoupper($this->request_method) . "&" . rawurlencode($this->url) . "&" . rawurlencode($parameter_string);
  }
}
?>
