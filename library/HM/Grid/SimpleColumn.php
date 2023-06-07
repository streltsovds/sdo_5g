<?php

class HM_Grid_SimpleColumn extends HM_Grid_ConfigurableClass
{
    const TYPE = 'simple';

    protected static function _getDefaultOptions()
    {
        return array(
            'grid' => null,
            'columns' => null,
            'field' => '',
            'hidden' => false,
            'title' => _(''),
            'order' => true,
            'callback' => false,
            'decorator' => false,
            'filter' => true,
        );
    }

    private static function getClassName($type)
    {
        static $filter = null;

        if (strtoupper($type[0]) !== $type[0]) {

            if ($type === 'simple') {
                return __CLASS__;
            }

            if ($filter === null) {
                $filter = new Zend_Filter_Word_DashToCamelCase();
            }

            $typeParts = explode('.', $type);

            $className = 'HM_Grid_Column';

            foreach ($typeParts as $typePart) {
                $className .= '_'.$filter->filter($typePart);
            }

            if ($className::TYPE === $type) {
                return $className;
            } else {
                throw new Exception('Найденный класс имеет другой тип столбца грида');
            }
        }

        return $type;
    }

    public static function factory($type, $options)
    {
        $className = self::getClassName($type);

        return new $className($options);

    }

    public function getColumnConfig()
    {
        $result = array(
            'hidden' => $this->isHidden(),
            'title' => $this->getTitle(),
            'order' => $this->isSorted(),
        );

        $decorator = $this->getDecorator();

        if ($decorator) {
            $result['decorator'] = $decorator;
        } else {
            $callBack = $this->getCallBack();

            if ($callBack) {
                $result['callback'] = $callBack;
            }
        }

        return $result;

    }

    public function getTitle()
    {
        return $this->_options['title'];
    }

    public function isHidden()
    {
        return $this->_options['hidden'];
    }

    public function isSorted()
    {
        return $this->_options['order'];
    }

    public function getCallBack()
    {
        return $this->_options['callback'];
    }

    public function getDecorator()
    {
        return $this->_options['decorator'];
    }

    public function getFieldName()
    {
        return $this->_options['field'];
    }

    public function hasFilter()
    {
        return !empty($this->_options['filter']);
    }

    public function getFilter()
    {
        if ($this->_options['filter'] === true) {
            return null;
        }

        return $this->_options['filter'];
    }

    /**
     * @return HM_Grid
     */
    public function getGrid()
    {
        return $this->_options['grid'];
    }
}