<?php
/**
 * Encapsulates communication via HTTP
 *
 * requires libcurl and openssl
 *
 * @author SOFORT AG (integration@sofort.com)
 *
 * @copyright 2010-2014 SOFORT AG
 *
 * @license Released under the GNU LESSER GENERAL PUBLIC LICENSE (Version 3)
 * @license http://www.gnu.org/licenses/lgpl.html
 *
 * @version SofortLib 2.1.2
 *
 * @link http://www.sofort.com/ official website
 */
class SofortLibHttp {
	
	/**
	 * Compression on/off?
	 *
	 * @var bool
	 */
	public $compression;
	
	/**
	 * Method to be used
	 *
	 * @var string
	 */
	public $connectionMethod;
	
	/**
	 * Error Code and Description
	 *
	 * @var array
	 */
	public $error;
	
	/**
	 * Headers to be sent
	 * 
	 * @var array
	 */
	public $headers;
	
	/**
	 * HTTP-Status Code
	 *
	 * @var int
	 */
	public $httpStatus = 200;
	
	/**
	 * Information for the last transfer
	 *
	 * @var mixed
	 */
	public $info;
	
	/**
	 * Proxy to be used
	 * 
	 * @var string
	 */
	public $proxy;
	
	/**
	 * API-url
	 * 
	 * @var string
	 */
	public $url;
	
	/**
	 * Api Key as provided in User Account on sofort.com
	 *
	 * @var string
	 */
	protected $_apiKey = '';
	
	/**
	 * Complete Config Key as provided in User Account on sofort.com
	 *
	 * @var string
	 */
	protected $_configKey = '';
	
	/**
	 * Project ID from sofort.com
	 *
	 * @var string
	 */
	protected $_projectId = '';
	
	/**
	 * Provides the parsed response.
	 *
	 * @var string
	 */
	protected $_response = '';
	
	/**
	 * User ID from sofort.com
	 * 
	 * @var string
	 */
	protected $_userId = '';
	
	
	/**
	 * Constructor for SofortLibHttp
	 *
	 * @param string $url
	 * @param bool $compression (default false)
	 * @param string $proxy (optional)
	 * @return \SofortLibHttp
	 */
	public function __construct($url, $compression = false, $proxy = '') {
		$this->url = $url;
		$this->compression = $compression;
		$this->proxy = $proxy;
	}
	
	
	/**
	 * HTTP error handling
	 *
	 * @return array(code, message, response[if available])
	 */
	public function getHttpCode() {
		switch ($this->httpStatus) {
			case(200):
				return array(
					'code' => 200,
					'message' => $this->_xmlError($this->httpStatus, 'OK'),
					'response' => $this->_response
				);
				break;
			case(301):
			case(302):
				return array(
					'code' => $this->httpStatus,
					'message' => $this->_xmlError($this->httpStatus, 'Redirected Request'),
					'response' => $this->_response
				);
				break;
			case(401):
				$this->error = 'Unauthorized';
				return array(
					'code' => 401,
					'message' => $this->_xmlError($this->httpStatus, $this->error),
					'response' => $this->_response
				);
				break;
			case(0):
			case(404):
				$this->httpStatus = 404;
				$this->error = 'URL not found '.$this->url;
				return array(
					'code' => 404,
					'message' => $this->_xmlError($this->httpStatus, $this->error),
					'response' => ''
				);
				break;
			case(500):
				$this->error = 'An error occurred';
				return array(
					'code' => 500,
					'message' => $this->_xmlError($this->httpStatus, $this->error),
					'response' => $this->_response
				);
				break;
			default:
				$this->error = 'Something went wrong, not handled httpStatus';
				return array(
					'code' => $this->httpStatus,
					'message' => $this->_xmlError($this->httpStatus, $this->error),
					'response' => $this->_response
				);
				break;
		}
	}
	
	
	/**
	 * Getter for HTTP status code
	 *
	 * @return string
	 */
	public function getHttpStatusCode() {
		$status = $this->getHttpCode();
		
		return $status['code'];
	}
	
	
	/**
	 * Getter for HTTP status message
	 *
	 * @return string
	 */
	public function getHttpStatusMessage() {
		$status = $this->getHttpCode();
		
		return $status['message'];
	}
	
	
	/**
	 * Getter for information
	 *
	 * @param string $opt (optional)
	 * @return string
	 */
	public function getInfo($opt = '') {
		if (!empty($opt)) {
			return $this->info[$opt];
		} else {
			return $this->info;
		}
	}
	
	
	/**
	 * Setter for ConfigKey and parsing ConfigKey into userId, ProjectId, apiKey
	 * 
	 * @param string $configKey
	 * @return void
	 */
	public function setConfigKey($configKey) {
		$this->_configKey = $configKey;
		list($this->_userId, $this->_projectId, $this->_apiKey) = explode(':', $configKey);
		$this->setHeaders();
	}
	
	
	/**
	 * Setting Headers to be sent
	 * 
	 * @return void
	 */
	public function setHeaders() {
		$header[] = 'Authorization: Basic '.base64_encode($this->_userId.':'.$this->_apiKey);
		$header[] = 'Content-Type: application/xml; charset=UTF-8';
		$header[] = 'Accept: application/xml; charset=UTF-8';
		$header[] = 'X-Powered-By: PHP/'.phpversion();
		$this->headers = $header;
	}
	
	
	/**
	 * Output an xml error
	 * 
	 * @param string $code
	 * @param string $message
	 * @return string xml error
	 */
	protected function _xmlError($code, $message) {
		return '<errors><error><code>0'.$code.'</code><message>'.$message.'</message></error></errors>';
	}
}