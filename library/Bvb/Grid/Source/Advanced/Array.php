<?php

/**
 * Description of Array
 *
 * @author tutrinov
 */
class Bvb_Grid_Source_Advanced_Array extends Bvb_Grid_Source_Array {
    
    public function buildFields() {
        if (null !== $this->_fields) {
            return parent::buildFields();
        }
        throw new Bvb_Grid_Exception('Invalid data for array grid source (empty list)');
    }
    
}
