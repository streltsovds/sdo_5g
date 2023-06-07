<?php
class HM_Currency_CurrencyModel
{
    private static  $_currencyList = array(
        'RUB' => 'Российский рубль',
        'EUR' => 'Euro',
        'USD' => 'US Dollar',
    );
    
    /**
     * Возвращает массив валют
     * @return multitype 
     */
    public static function getList()
    {
        return self::$_currencyList;
    }
    
    /**
     * Возвращает массив валют, 
     * где в наименовании каждой валюты присутствует ее код
     * @return multitype
     */
    public static function getFullNameList()
    {
        $result = array();
        foreach (self::$_currencyList as $key=>$name) {
            $result[$key] = $key . "\t" . $name;
        }
        return $result;
    }
    
    /**
     * Функция возвращает название валюты по ее коду
     * @param string $shotName код валюты
     * @return boolean|string 
     */
    public static function getName( $shotName )
    {
        if( !self::isCurrency($shotName) ) return FALSE;
        return self::$_currencyList[strtoupper($shotName)];
    }
    
    /**
     * Проверяет есть ли код валюты в списке
     * @param string $shotName код валюты
     * @return boolean
     */
    public static function isCurrency( $shotName ) 
    {
        return array_key_exists(strtoupper($shotName), self::$_currencyList);
    }
    
    /**
     * Возвращает код валюты, установленной как валюта по умолчанию
     */
    public static function getDefaultCurrency()
    {
        return Zend_Registry::get('serviceContainer')->getService('Option')->getDefaultCurrency();        
    }
}
?>