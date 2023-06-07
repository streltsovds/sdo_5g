<?php

class HM_Integration_Abstract_Model_Nested extends HM_Integration_Abstract_Model
{
    protected $_parentId;
    protected $_parentExternalId;

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->_parentId;
    }

    /**
     * @param mixed $parentId
     */
    public function setParentId($parentId)
    {
        $this->_parentId = $parentId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParentExternalId()
    {
        return $this->_parentExternalId;
    }

    /**
     * @param mixed $parentExternalId
     */
    public function setParentExternalId($parentExternalId)
    {
        $this->_parentExternalId = $parentExternalId;
        return $this;
    }

}