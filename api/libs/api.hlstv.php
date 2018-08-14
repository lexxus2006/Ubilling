<?php

class HlsTV {

    /**
     * Contains system alter config as key=>value
     *
     * @var array
     */
    protected $altCfg = array();

    /**
     * Contains public key
     *
     * @var string
     */
    protected $publicKey = '';

    /**
     * Contains private key
     *
     * @var string
     */
    protected $privateKey = '';

    /**
     * Current timestamp for all API requests
     *
     * @var int
     */
    protected $currentTimeStamp = 0;

    /**
     * Default HLS API URL
     */
    const URL_API = 'https://apiua2.hls.tv/';

    /**
     * Creates new low-level API object instance
     * 
     * @return void
     */
    public function __construct() {
        $this->loadConfigs();
        $this->setOptions();
    }

    /**
     * Loads required configs into protected properties for further usage
     * 
     * @global object $ubillingConfig
     * 
     * @return void
     */
    protected function loadConfigs() {
        global $ubillingConfig;
        $this->altCfg = $ubillingConfig->getAlter();
    }

    /**
     * Sets default options to object instance properties
     * 
     * @return void
     */
    protected function setOptions() {
        $this->publicKey = 'xxxxxxxx';
        $this->publicKey = 'yyyyyyyy';
        $this->currentTimeStamp = time();
    }

    /**
     * Returns new API_HASH for some message
     * 
     * @param array $message
     * 
     * @return string
     */
    protected function generateApiHash($message = array()) {
        $result = hash_hmac('sha256', $this->currentTimeStamp . $this->publicKey, http_build_query($message, '', '&'), $this->privateKey);
        return ($result);
    }

    /**
     * Pushes some request to remote API and returns decoded array or raw JSON reply.
     * 
     * @param array $request
     * @param bool $raw
     * 
     * @return array/json
     */
    public function pushApiRequest($request, $raw = false) {
        $curl = curl_init(self::URL_API);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'API_ID: ' . $this->publicKey,
            'API_TIME: ' . $this->currentTimeStamp,
            'API_HASH:' . $this->generateApiHash($request)
        ));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        $jsonResponse = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($status != 200) {
            die('Error: call to URL ' . self::URL_API . ' failed with status ' . $status . ', response ' . $jsonResponse . ', curl_error ' . curl_error($curl) . ', curl_errno ' . curl_errno($curl));
        }
        curl_close($curl);
        if (!$raw) {
            $result = json_decode($jsonResponse, true);
        } else {
            $result = $jsonResponse;
        }
        return ($result);
    }

}