<?php
trait HM_Controller_Action_Trait_Empty
{
    protected $_empty;

    /**
     * @return mixed
     */
    public function isEmpty()
    {
        return (bool)$this->_empty;
    }

    /**
     * @param bool $empty
     * @return HM_Controller_Action_Trait_Empty
     */
    public function setEmpty($empty = true)
    {
        $this->_empty = $empty;
        return $this;
    }

}
