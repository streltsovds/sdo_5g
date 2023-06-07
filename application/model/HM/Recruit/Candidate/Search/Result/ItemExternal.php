<?php
/**
 * Description of Item
 *
 * @author tutrinov
 */
class HM_Recruit_Candidate_Search_Result_ItemExternal extends HM_Recruit_Candidate_Search_Result_AbstractItem {
    
    /**
     *
     * @var tring
     */
    protected $fullName = null;
    
    /**
     *
     * @var html description for output
     */
    protected $rawHtmlDescription = null;
    
    /**
     *
     * @var HM_Collection_Primitive
     */
    protected $additionalData = null;
    
    public function __construct() {
        $this->setAdditionalData(new HM_Collection_Primitive);
        return parent::__construct();
    }
    
    /**
     * 
     * @return string
     */
    public function getFullName() {
        return $this->fullName;
    }

    /**
     * 
     * @return string
     */
    public function getRawHtmlDescription() {
        return $this->rawHtmlDescription;
    }

    /**
     * 
     * @return HM_Collection_Primitive
     */
    public function getAdditionalData() {
        return $this->additionalData;
    }

    public function setFullName($fullName) {
        $this->fullName = $fullName;
    }

    /**
     * 
     * @param string $rawHtmlDescription Define parced and decorated html data from external resource
     */
    public function setRawHtmlDescription($rawHtmlDescription) {
        $this->rawHtmlDescription = $rawHtmlDescription;
    }

    /**
     * 
     * @param HM_Collection_Primitive $additionalData
     */
    public function setAdditionalData(HM_Collection_Primitive $additionalData) {
        $this->additionalData = $additionalData;
    }
    
    public function sourceName($name = null) {
        if (null !== $name) {
            $this->addition('sourceName', $name);
            return;
        }
        return $this->addition('sourceName');
    }
    
    /**
     * 
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public function addition($key, $value = null) {
        if ($value !== null) {
            $this->additionalData->add($value, $key);
            return;
        }
        return $this->additionalData->offsetGet($key);
    }
    
}
