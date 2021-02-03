<?php

if (!defined('_PS_VERSION_'))
	exit;

class BalticPostAPI
{
    const PRODUCT_URL = 'http://api.balticpost.lt';
    const DEVELOPMENT_URL = 'http://apibeta.balticpost.lt';

    private $url;
    private $url_wsdl;
    private $url_wsdl_ns;
    private $url_pdf;
    private $url_manifest;

    private $last_error = null;

    /**
	 * BalticPostAPI instance
	 * @var static
	 */
	private static $_oInstance = null;

	/**
	 * SOAP client used for connection
	 * @var SoapClient
	 */
	protected $_oClient;

	/**
	 * SoapHeader object contains authentication data
	 * @var SoapHeader
	 */
	protected $_oLoginHeader;

	/**
	 * Baltic Post Partner ID
	 * @var string
	 */
	protected $_sLoginID = null;

	/**
	 * Baltic Post Partner Password
	 * @var string
	 */
	protected $_sLoginPass = null;

    /**
     * Creates a static instance of BalticPostAPI ant returns it
     * @param null $dev_mode
     * @return BalticPostAPI
     */
	public static function getInstance($dev_mode = null)
	{
		if (self::$_oInstance === null)
		{
			self::$_oInstance = new BalticPostAPI($dev_mode);
			self::$_oInstance->init();
		}
		return self::$_oInstance;
	}

	private function __construct($dev_mode = false)
    {
        $this->url = self::PRODUCT_URL;
        if ($dev_mode)
        {
            $this->url = self::DEVELOPMENT_URL;
        }

        $this->url_wsdl = $this->url.'/bpdcws/wsdl';
        $this->url_wsdl_ns = $this->url.'/bpdcws';
        $this->url_pdf = $this->url.'/getpdf/label';
        $this->url_manifest = $this->url.'/getpdf/manifest';
    }

    /**
	 * Initializes SoapClient
	 * @return bool
	 */
	protected function init()
	{
		$aOptions = array(
			'connection_timeout' => 5,
			'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
			'cache_wsdl' => WSDL_CACHE_NONE,
			'trace' => 1,
		);

		if (!class_exists('SoapClient'))
			return false;

		try
		{
			$this->_oClient = new SoapClient($this->url_wsdl, $aOptions);
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Executes requested API method with provided parameters
	 * @param string $sMethod
	 * @param array $oParams
	 * @return bool|array
	 */
	protected function _callMethod($sMethod, $oParams = array())
	{
		if (!$this->_oClient instanceof SoapClient)
			return false;

		try
		{
			$this->_oClient->__setSoapHeaders(array($this->_getAuthHeader()));
			$oResp = $this->_oClient->__soapCall($sMethod, $oParams);
		}
		catch (SoapFault $e)
		{
            $this->last_error = $e;
			return false;
		}
		catch (Exception $e)
		{
            $this->last_error = $e;
            return false;
		}

		// Convert stdClass into array
		return !empty($oResp) ? json_decode(json_encode($oResp), true) : false;
	}

	/**
	 * Prepares SoapHeader with authorization information
	 * @return SoapHeader
	 */
	protected function _getAuthHeader()
	{
		if ($this->_oLoginHeader == null)
			self::getInstance()->_rebuildAuthHeader();

		return $this->_oLoginHeader;
	}

	/**
	 * Rebuilds SoapHeader with current authentication data
	 */
	protected function _rebuildAuthHeader()
	{
		$sLoginXML = '<UserAuth><userid>'.$this->_sLoginID.'</userid><password>'.$this->_sLoginPass.'</password></UserAuth>';
		$oHeaderVar = new SoapVar($sLoginXML, XSD_ANYXML, null, null, null);
		$this->_oLoginHeader = new SoapHeader($this->url_wsdl_ns, 'UserAuth', $oHeaderVar);
	}

	/**
	 * Changes authentication ID and password and forces Auth SoapHeader header to be rebuilt
	 * @param string $id Partner ID
	 * @param string $pass Partner Password
	 */
	public static function setAuthData($id, $pass)
	{
		$self = self::getInstance();
		$self->_sLoginID   = (string)$id;
		$self->_sLoginPass = (string)$pass;
		$self->_rebuildAuthHeader();
	}

	/**
	 * Tests connection and authentication to the Baltic Post API service
	 * @return bool
	 */
	public static function testAuth()
	{
		$response = self::getInstance()->_callMethod('hello_authorization', array('teststring' => 'rp'));
		return !empty($response);
	}

	/**
	 * Returns array of available parcel terminals, pre-formatted for model insert
	 * @return array
	 */
	public static function getPublicTerminals()
	{
		$terminals = self::getInstance()->_callMethod('public_terminals', array());
		$isResponse = (!empty($terminals) && is_array($terminals));
		return $isResponse ? $terminals : false;
	}

	/**
	 * Returns closes post office data from provided ZIP code
	 * @param string $sZip
	 * @return bool|stdClass
	 */
	public static function getPostOfficeByZip($sZip)
	{
		$response = self::getInstance()->_callMethod('zip2postoffice', array('zip' => $sZip));
		return !empty($response) ? $response : false;
	}

	public static function setEnvironment($development_mode = false)
    {
        self::getInstance($development_mode);
    }

    /**
     * @return Exception
     */
    public static function getLastError()
    {
        return self::getInstance()->last_error;
    }

	public static function addLabel(array $parameters)
    {
        $response = self::getInstance()->_callMethod('add_labels', $parameters);
        return !empty($response) ? $response : false;
    }

	public static function confirmLabel(array $parameters)
    {
        $response = self::getInstance()->_callMethod('confirm_labels', $parameters);
        return !empty($response) ? $response : false;
    }

	public static function cancelLabel(array $parameters)
    {
        $response = self::getInstance()->_callMethod('cancel_labels', $parameters);
        return !empty($response) ? $response : false;
    }

    protected function _getLabel($order_pdf_id, $header = false)
    {
        $ch = curl_init($this->url_pdf.'/'.$order_pdf_id.'?lfl=lfl_a4_3');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        $output = curl_exec($ch);
        return $output;
    }

    public static function getLabel($order_pdf_id, $header = false)
    {
        $response = self::getInstance()->_getLabel($order_pdf_id, $header);
        return $response;
    }

    public function _getManifestURL($manifest_id)
    {
        return $this->url_manifest.'/'.$manifest_id;
    }
    public static function getManifestURL($manifest_id)
    {
        $response = self::getInstance()->_getManifestURL($manifest_id);
        return $response;
    }

    protected function _getManifest($manifest_id, $header = false)
    {
        $ch = curl_init($this->url_manifest.'/'.$manifest_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        $output = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200)
        {
            return $output;
        }
        return false;
    }

    public static function getManifest($manifest_id, $header = false)
    {
        $response = self::getInstance()->_getManifest($manifest_id, $header);
        return $response;
    }

    public static function callCourier(array $parameters)
    {
        $response = self::getInstance()->_callMethod('call_courier', $parameters);
        return !empty($response) ? $response : false;
    }
}