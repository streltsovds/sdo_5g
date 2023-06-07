<?php
/**
 * Custom validator.
 *
 */

class HM_Validate_Contains extends Zend_Validate_Abstract
{
    const NOT_FOUND = 'notFound';

    protected $_needle;
    protected $_hint;

    protected $_messageTemplates = array(
        self::NOT_FOUND => '%hint%'
    );

    protected $_messageVariables = array(
        'hint' => '_hint'
    );

    public function __construct($options)
    {
        $this->setNeedle($options['needle']);
        $this->setHint($options['hint']);
    }

    /**
     * @return mixed
     */
    public function getNeedle()
    {
        return $this->_needle;
    }

    /**
     * @param mixed $contains
     * @return HM_Validate_Contains
     */
    public function setNeedle($contains)
    {
        $this->_needle = $contains;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHint()
    {
        return $this->_hint;
    }

    /**
     * @param mixed $message
     * @return HM_Validate_Contains
     */
    public function setHint($message)
    {
        $this->_hint = $message;
        return $this;
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;
        $needle = $this->getNeedle();

        if(is_array($needle))
            foreach ($needle as $item)
                $isValid = $isValid && $this->_isValid($value, $item);
        else
            $isValid = $this->_isValid($value, $needle);

        if (!$isValid)
            $this->_error(self::NOT_FOUND, $this->getHint());

        return $isValid;
    }

    /**
     * @param string $value
     * @param string $needle
     * @return bool
     */
    private function _isValid($value, $needle): bool
    {
        return strpos(strip_tags($value), $needle) !== false;
    }
}
