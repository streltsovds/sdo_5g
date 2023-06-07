<?php
/**
 * Description of Abstract
 *
 * @author tutrinov
 */
abstract class HM_Recruit_RecruitingServices_Curl_Abstract implements HM_Recruit_RecruitingServices_PlacementBehavior {
    
    protected $curl = null;
    protected $lastResult = null;
    protected $info = null;
    
    public function __construct() {
        $this->init();
    }
    
    public function getCurl() {
        return $this->curl;
    }

    public function getLastResult() {
        return $this->lastResult;
    }

    public function getInfo($key = null) {
        if ($key !== null && array_key_exists($key, $this->info)) {
            return $this->info[$key];
        } elseif ($key !== null) {
            return null;
        }
        return $this->info;
    }
    
    public function getCustomInfo($key) {
        return curl_getinfo($this->getCurl(), $key);
    }

    public function setCurl($curl) {
        $this->curl = $curl;
    }

    public function setLastResult($lastResult) {
        $this->lastResult = $lastResult;
    }

    public function setInfo($info) {
        $this->info = $info;
    }
    
    public function execRequest() {
        $result = curl_exec($this->getCurl());
        $this->setLastResult($result);
        $this->setInfo(curl_getinfo($this->getCurl()));
        return $this;
    }
    
    public function executeWithParams(array $params) {
        if (sizeof($params) !== 0) {
            $this->setOptions($params);
            $this->execRequest();
            return;
        }
        throw new HM_Recruit_RecruitingServices_Exception_InvalidArgument("PArameters array could not be found");
    }
    
    public function setOption($key, $value) {
        if (null !== $this->getCurl()) {
            curl_setopt($this->getCurl(), $key, $value);
            return;
        }
        throw HM_Recruit_RecruitingServices_Exception_Runtime("Curl connection is not define!");
    }
    
    /**
     * Set curl conenction parameters
     * @param array $options
     * @param boolean $useNativePhpFunction use or not curl_setopt_array function
     * @return void
     * @throws HM_Recruit_RecruitingServices_Exception_InvalidArgument
     */
    public function setOptions(array $options, $useNativePhpFunction = false) {
        if (sizeof($options) !== 0) {
            if ($useNativePhpFunction) {
                curl_setopt_array($this->getCurl(), $options);
                return;
            }
            foreach ($options as $optKey => $optValue) {
                $this->setOption($optKey, $optValue);
            }
            return;
        }
        throw new  HM_Recruit_RecruitingServices_Exception_InvalidArgument("Curl options array could not be empty");
    }
    
    public function init() {
        $ch = curl_init();
        $this->setCurl($ch);
    }
    
}
