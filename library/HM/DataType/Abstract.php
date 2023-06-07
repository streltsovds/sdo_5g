<?php


abstract class HM_DataType_Abstract
{
    public function setProps(array $props)
    {
        foreach ($props as $propName => $propValue) {
            if(property_exists($this, $propName)) {
                $this->{$propName} = $propValue;
            }
        }

        return $this;
    }

    public function asArray()
    {
        return get_object_vars($this);
    }
}