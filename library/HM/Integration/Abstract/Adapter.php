<?php

abstract class HM_Integration_Abstract_Adapter
{
    protected $_model;

    protected $_keyExternal;

    protected $_mapping = array();
    protected $_defaults = array();
    protected $_externals = array();

    public function init(HM_Integration_Abstract_Model $model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeyExternal()
    {
        return $this->_keyExternal;
    }

    /**
     * @param mixed $keyExternal
     */
    public function setKeyExternal($keyExternal)
    {
        $this->_keyExternal = $keyExternal;
        return $this;
    }

    public function convert(Array $item)
    {
        $this->setDefaultAttributes()
            ->setExternalAttributes($item)
            ->mapAttributes($item);

        return $this;
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function setDefaultAttributes($extra = array())
    {
        if (is_array($this->_defaults)) {
            $defaults = array_merge($this->_defaults, $extra);
            foreach ($defaults as $key => $value) {
                $this->_model->setAttribute($key, $value);
            }
        }
        return $this;
    }

    public function setExternalAttributes(Array $item)
    {
        foreach ($this->_externals as $key => $keyExternal) {
            if (isset($item[$keyExternal])) {

                $callback = sprintf('_convert%s', ucfirst($keyExternal));
                if (method_exists($this, $callback)) {
                    $value = $this->$callback($item[$keyExternal], $key);
                } else {
                    $value = $item[$keyExternal];
                }

                $this->_model->setExternalAttribute($key, $value);
            }
        }
        return $this;
    }

    public function mapAttributes(Array $item)
    {
        $this->_model->setSource($item);
        if (is_array($this->_mapping)) {
            foreach ($this->_mapping as $keyExternal => $keys) {
                if (!is_array($keys)) $keys = array($keys);
                foreach ($keys as $key) {
                    if (isset($item[$keyExternal])) {
                        // @todo-int другие возможные обработки здесь
                        $value = trim($item[$keyExternal]);

                        $callback = sprintf('_convert%s', ucfirst($keyExternal));
                        if (method_exists($this, $callback)) {

                            $extraKeys = array_keys($this->_mapping, $key);
                            if (count($extraKeys) > 1) {
                                foreach ($extraKeys as $extraKey) {
                                    $extraValues[$extraKey] = trim($item[$extraKey]);
                                    unset($item[$extraKey]);
                                }
                                $value = $this->$callback($value, $extraValues);
                            } else {
                                $value = $this->$callback($value);
                            }
                        }

                        $this->_model->setAttribute($key, $value);
                    }
                }
            }
        }
        return $this;
    }

    static protected function _convertDate($value)
    {
        $parts = explode('.', $value);
        return implode('-', array_reverse($parts));
    }
}