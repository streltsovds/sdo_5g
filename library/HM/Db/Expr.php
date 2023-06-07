<?php

/**
 * Это расширениие Zend_Db_Expr позволяет нам переиспользовать часто повторяющиеся
 * конструкции при набивке аргументов в метод from().
 */
class HM_Db_Expr extends Zend_Db_Expr
{
    /**
     * @var array Сюда можно закидывать шаблоны выражений,
     * которые мы хотим переиспользовать в других селектах.
     * На место плейсходдеров "t*" будет подставлено
     * имя таблицы, переданное в конструктор.
     */
    protected $expressions = [
        'notempty' =>
            "CASE 
                WHEN 
                    (t1.LastName   IS NULL AND 
                     t1.FirstName  IS NULL AND  
                     t1.Patronymic IS NULL) 
                    OR 
                    (t1.LastName   = '' AND 
                     t1.FirstName  = '' AND 
                     t1.Patronymic = '') 
                THEN 0 
                ELSE 1 
            END",
        'fio' =>
            "CONCAT(
                CONCAT(
                    CONCAT(
                        CONCAT(
                            t1.LastName   , ' '), 
                            t1.FirstName ), ' '), 
                            t1.Patronymic)
            ",
        'department' =>
            "CASE 
                WHEN 
                    t1.is_manager > 0 
                THEN 
                    t3.name 
                ELSE 
                    t2.name 
            END",
    ];

    /**
     * HM_Db_Expr constructor.
     * @param string $fieldName
     * @param array $tableNames
     * @param array $expressions
     *
     * Есть два варианта передачи имён таблицы и поля:
     *     1. отдельными аргументами через запятую;
     *     2. единственным аргументом $fieldName в виде "имяТаблицы.имяПоля",
     *        в этом случае даже если вторым аргументом передавать имя таблицы,
     *        оно будет перезаписано тем, что пришло из первого аргумента.
     */
    public function __construct($fieldName, $tableNames = ['t1'], $expressions = [])
    {
        $result     = '';
        $expression = $fieldName;
        $this->expressions = array_merge($this->expressions, $expressions);

        if (false === strpos(".", $fieldName)) {
            $array = explode(".", $fieldName);
            if (isset($array[1])) {
                if (array_key_exists($array[1], $this->expressions)) {
                    list($tableNames, $fieldName) = $array;
                    $expression = $this->expressions[$fieldName];
                }
            }
        }

        if (is_array($tableNames)) {
            foreach ($tableNames as $key => $tableName) {
                $result = str_replace('t'.($key + 1), $tableName, $expression);
            }
        } else {
            $result = str_replace('t1', $tableNames, $expression);
        }

        parent::__construct($result);
    }
}