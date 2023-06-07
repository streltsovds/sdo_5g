<?php

/**
 * Description of Abstract
 *
 * @author tutrinov
 */
abstract class HM_Recruit_RecruitingServices_Rest_Abstract extends Zend_Rest_Client implements HM_Recruit_RecruitingServices_PlacementBehavior {
    
    protected $lastResult = null;
    protected $lastPath = null;
    protected $lastQuery = null;

    public function sendGetRequest($path, array $query = null) {

        Zend_Registry::get('log_system')->debug(sprintf('HH REQUEST: %s/%s', $path, serialize($query)));
        $result = $this->restGet($path, $query);

//        ob_start(); print_r($result); $str = ob_get_contents(); ob_end_clean();
//        if ($result) Zend_Registry::get('log_system')->debug($str);
//        if ($result) Zend_Registry::get('log_system')->debug(json_decode($result->getBody()));

        $this->setLastResult($result);
        $this->setLastPath($path);
        $this->setLastQuery($query);
        return $result;
    }

    public function sendPostRequest($path, $query = null) {

//        Zend_Registry::get('log_system')->debug(sprintf('HH: %s/%s', $path, serialize($query)));
        $result = $this->restPost($path, $query);

//        ob_start(); print_r($result); $str = ob_get_contents(); ob_end_clean();
//        if ($result) Zend_Registry::get('log_system')->debug($str);
//        if ($result) Zend_Registry::get('log_system')->debug(json_decode($result->getBody()));

        $this->setLastResult($result);
        $this->setLastPath($path);
        $this->setLastQuery($query);
        return $result;
    }

    public function sendPutRequest($path, $query = null) {
        $result = $this->restPut($path, $query);
        $this->setLastResult($result);
        $this->setLastPath($path);
        $this->setLastQuery($query);
        return $result;
    }

    public function getLastResult() {
        return $this->lastResult;
    }

    public function setLastResult($lastResult) {
        $this->lastResult = $lastResult;
    }
    
    public function getLastPath() {
        return $this->lastPath;
    }

    public function getLastQuery() {
        return $this->lastQuery;
    }

    public function setLastPath($lastPath) {
        $this->lastPath = $lastPath;
    }

    public function setLastQuery($lastQuery) {
        $this->lastQuery = $lastQuery;
    }

    static public function getRemoteFile($url)
    {
        $ch = curl_init();

        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);

        ob_start();

        curl_exec ($ch);
        curl_close ($ch);
        $string = ob_get_contents();

        ob_end_clean();

        return $string;
    }

    static public function decode($str)
    {
        ob_start(); print_r($str); $log = ob_get_contents(); ob_end_clean();
        Zend_Registry::get('log_system')->debug('HH RESPONSE: ' . $log);

        $decoded = json_decode($str);
        if (empty($decoded)) {

            $sanitized = stripslashes($str);

            ob_start(); print_r($sanitized); $log = ob_get_contents(); ob_end_clean();
            Zend_Registry::get('log_system')->debug('HH RESPONSE SANITIZED: ' . $log);

            $decoded = json_decode($sanitized);
        }

        ob_start(); print_r($decoded); $log = ob_get_contents(); ob_end_clean();
        Zend_Registry::get('log_system')->debug('HH RESPONSE DECODED: ' . $log);

        return $decoded;
    }
}