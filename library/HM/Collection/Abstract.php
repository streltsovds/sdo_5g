<?php
/**
 * Абстрактная реализация lazy initialization коллекции объектов
 */
abstract class HM_Collection_Abstract extends HM_Collection_Primitive
{

    /**
     * Массив инициализированных объектов
     * @var array
     */
    protected $_data = [];
    protected $_position = 0;
    protected $_count = 0;
    protected $_modelClass = [];
    protected $_sortAttr = null;

    /**
     * Массив зависимостей для одного объекта
     * @var array
     */
    protected $_dependences = [];

    /**
     * Кэш произвольного назначения
     * Чтобы не обращаться к БД из модели в цикле
     * @var array
     */
    protected $_cache = [];

    public function __construct($raw = [], $modelClass = null)
    {
        $this->_raw = $raw;
        $this->_modelClass = $modelClass;
        $this->_position = 0;
        $this->_count = count($raw);
    }

    public function init()
    {
        foreach ($this as $offset => $item) {
            if (method_exists($item, 'init') and !$item->init()) {
                $this->offsetUnset($offset);

            }
            else {
                $item->setCollection($this);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getCache()
    {
        return $this->_cache;
    }

    public function getCachedValue($cacheType, $key)
    {
        if (isset($this->_cache[$cacheType][$key])) {
            return $this->_cache[$cacheType][$key];
        }
        return false;
    }

    /**
     * @param array $cache
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    public function addCache($cacheType, $data)
    {
        $this->_cache[$cacheType] = $data;
        return $this;
    }

    protected function _createDependences($offset)
    {
        if (!isset($this->_data[$offset])) return null;
        if (!is_array($this->_dependences) or !count($this->_raw)) {
            $this->_dependences = [];
        }
        $model = $this->_data[$offset];
        foreach($this->_dependences as $column => $dependence) {
            if (strlen($column) and isset($dependence[$model->$column])) {
                foreach($dependence[$model->$column] as $propertyName => $items) {
                    $models = new static([], 'HM_Model_Abstract');
                    foreach($items as $item) {
                        if (!empty($item['refClass'])) {
                            $item['row']['modelClass'] = $item['refClass'];
                            unset($item['refClass']);
                            $models[] = $item['row'];
                        }
                    }
                    $model->setValue($propertyName, $models);
                }
            }
        }
    }

    /**
     * Инициализируем текущий объект
     * @param  int $offset
     * @return void
     */
    protected function _createObject($offset = 0)
    {
        if (!isset($this->_data[$offset])) {
            $modelClass = !empty($this->_raw[$offset]['modelClass']) ? $this->_raw[$offset]['modelClass']: $this->getModelClass();
            unset($this->_raw[$offset]['modelClass']);

            if (isset($this->_raw[$offset])) $this->_data[$offset] = call_user_func_array([$modelClass, 'factory'], [$this->_raw[$offset], $modelClass]);
            $this->_createDependences($offset);
            $this->_raw[$offset] = null;

            if (null === $this->_raw[count($this->_raw)-1]) {
                unset($this->_dependences);
            }
        }
    }

    /**
     * Делаем reset коллекции
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Возвращаем текущий элемент коллекции
     * @return HM_Model_Abstract
     */
    public function current()
    {
        $this->_createObject($this->_position);

        return isset($this->_data[$this->_position]) ? $this->_data[$this->_position] : null;

    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function valid()
    {
        return (isset($this->_raw[$this->_position]) || isset($this->_data[$this->_position]));
    }

    /**
     * Добавляем в коллекцию новый элемент с индексом $offset
     * @param  int $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $modelClass = $this->getModelClass();
        if ($value instanceof $modelClass) {
            if (is_null($offset)) {
                $this->_data[] = $value;
            } else {
                $this->_data[$offset] = $value;
            }
        } elseif(is_array($value)) {
            if (is_null($offset)) {
                $this->_raw[] = $value;
            } else {
                $this->_raw[$offset] = $value;
            }
        }
        ++$this->_count;
    }

    /**
     * Проверка на существование элемента с индексом $offset
     * @param  int $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return (isset($this->_raw[$offset]) or isset($this->_data[$offset]));
    }

    public function offsetUnset($offset)
    {
        if (isset($this->_data[$offset]) or isset($this->_raw[$offset])) {
            --$this->_count;
        }
        unset($this->_data[$offset]);
        unset($this->_raw[$offset]);

        //something very dangerous, observer this
        sort($this->_raw);
        sort($this->_data);
        $this->rewind();
    }

    public function offsetGet($offset)
    {
        if ((!isset($this->_raw[$offset]) && !isset($this->_data[$offset]))) return null;
        $this->_createObject($offset);
        return $this->_data[$offset];
    }

    /**
     * Устанавливаем название модели по умолчанию для элементов коллекции
     * @param  string $modelClass
     * @return void
     */
    public function setModelClass($modelClass)
    {
        $this->_modelClass = $modelClass;
    }

    public function getModelClass()
    {
        return $this->_modelClass;
    }

    /**
     * Устанавливаем зависимости для текущей коллекции объектов
     * @param array $dependences
     * @return void
     */
    public function setDependences($dependences = [])
    {
        $this->_dependences = $dependences;
    }

    public function getDependences()
    {
        return $this->_dependences;
    }

    public function count()
    {
        return $this->_count;
    }

    public function isEmpty()
    {
        return empty($this->_raw) && empty($this->_data);
    }

    /**
     * Возвращает список из $key и $value свойств элементов коллекции
     * @param  string $key
     * @param  string $value
     * @param bool $default
     * @return array
     */
    public function getList($key, $value = null, $default = false)
    {
        $value = $value ?: $key;
        $result = [];

        if ($default) {
            $result[0] = $default;
        }

        foreach($this as $item) {
            if (method_exists($item, $value))
                $result[$item->$key] = $item->$value();
            else
                $result[$item->$key] = $item->$value;
        }

        return $result;
    }

    public function exists($key, $value)
    {
        foreach($this as $item) {
            if (isset($item->$key) && ($item->$key == $value)) {
                return $item;
            }
        }
        return false;
    }

    public function asArray()
    {
        return $this->_raw;
    }

    // должен быть установлен primaryName у таблицы
    public function asArrayOfObjects($sort = null)
    {
        $result = [];
        foreach ($this as $item) {
            if (is_object($item) && ($pk = $item->getPrimaryKey())) {
                $result[$pk] = $item;
            } else {
                $result[] = $item;
            }
        }

        if ($sort && count($result) && isset($item->$sort)) {
            $this->_sortAttr = $sort;
            uasort($result, array('HM_Collection_Abstract', '_sortByAttribute'));
        }

        return $result;
    }

    // должен быть установлен primaryName у таблицы
    public function asArrayOfArrays()
    {
        $result = [];
        foreach ($this as $item) {
            if ($pk = $item->getPrimaryKey()) {
                $result[$pk] = $item->getData();
            } else {
                $result[] = $item->getData();
            }
        }
        return $result;
    }

    public function asArrayOfUnifiedArrays()
    {
        $result = [];
        foreach ($this as $item) {
            $pk = $item->getPrimaryKey();
            if ($pk) {
                $result[$pk] = $item->getUnifiedData();
            } else {
                $result[] = $item->getUnifiedData();
            }
        }
        return $result;
    }

    public function filter($closure)
    {
        do {
            $allItemsChecked = true;
            foreach ($this as $offset => $item) {
                if (empty($item) or (isset($closure) and !$closure($item))) {
                    $this->offsetUnset($offset);
                    $allItemsChecked = false;
                    $this->rewind();
                    break;
                }
            }
        } while (!$allItemsChecked);

        $this->rewind();
        return $this;
    }

    public function sort($closure)
    {
        if (is_callable($closure)) {
            if (is_array($this->_data) && count($this->_data)){
                usort($this->_data, $closure);
            }
        }
        return $this;
    }

    protected function _sortByAttribute(&$item1, &$item2)
    {
        if ($sort = $this->_sortAttr) {
            return ($item1->$sort < $item2->$sort) ? -1 : 1;
        }
        return 0;
    }
}