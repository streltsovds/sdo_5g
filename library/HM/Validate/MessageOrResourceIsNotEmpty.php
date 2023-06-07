<?php
/**
 * Custom validator.
 *
 */

class HM_Validate_MessageOrResourceIsNotEmpty extends Zend_Validate_NotEmpty
{
    const INVALID = 'invalid';

    protected $_messageTemplates = array(
        self::INVALID => 'Нужно заполнить поле с содержанием или ресурсом'
    );

    public function isValid($value, $context = array())
    {
         // You need to use your element names, consider making these dynamic
        $checkFields = array('message','resource_id');
        // Check if all are empty
        foreach ( $checkFields as $field ) {
            if (isset($context[$field]) && !empty($context[$field])) {
                // Only one value needs to return true..skip the rest
                return true;
            }
        }

        // All were empty, set your own error message
        $this->_error(self::INVALID);
        return false;
    }
}
