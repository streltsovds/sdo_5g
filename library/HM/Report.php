<?php
class HM_Report
{
    private $_config = null;
    private $_domain = null;
    private $_fields = [];
    private $_tables = [];
    private $_joined = [];

    private $_gridFields = [];
    private $_gridFilters = [];

    private $_values = [];

    public function setConfig(HM_Report_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @return HM_Report_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    public function setDomain($domain)
    {
        $this->_domain = $domain;
    }

    public function getDomain()
    {
        return $this->_domain;
    }

    public function setValues($values)
    {
        $this->_values = $values;
    }

    public function getValue($name)
    {
        if (isset($this->_values[$name]) && $this->_values[$name]) {
            return $this->_values[$name];
        }
        return null;
    }

    private function _setupPrimaryKeys(HM_Report_Table $table)
    {
        $primaryKeys = $table->getPrimaryKeys();
        if (count($primaryKeys)) {
            foreach($primaryKeys as $primaryKey) {
                if (!isset($this->_fields[$table->name.'.'.$primaryKey])) {
                    $field  = new HM_Report_Table_Field(array('table' => $table->name, 'field' => $primaryKey, 'name' => $table->name.$primaryKey));
                    $field->setOptions(array('hidden' => true));
                    $field->setTable($table);

                    if (null !== $this->getValue($field->getTable()->name.'_'.$field->name)) {
                        $field->setOption('filter', $this->getValue($field->getTable()->name.'_'.$field->name));
                    }

                    $this->_fields[$table->name.'.'.$primaryKey] = $field;
                }
            }
        }
    }

    protected $logs = array();

    public function log($msg) {
        $this->logs[] = $msg;
    }

    public function getInputFields(HM_Controller_Action $controller)
    {
        $inputFields = array();

         if (is_array($this->_fields) && count($this->_fields)) {
             $preFilters = $this->_getPreFilters();
             $select = $this->_processTables($controller->getService('User')->getSelect());
             foreach($this->_fields as $field) {
                 $options = $field->getOptions();
                 if (isset($options['input']) && strlen($options['input']) && $options['input']) {
                     if ($field->type == 'string') {
                         $selectInputField = clone $select;

                         $primaryKey = $field->name;
                         $primaryKeys = $field->getTable()->getPrimaryKeys();
                         if (count($primaryKeys) == 1) {
                             foreach($primaryKeys as $pKey) {
                                 if (isset($this->_fields[$field->getTable()->name.'.'.$pKey])) {
                                     $this->_fields[$field->getTable()->name.'.'.$pKey]->name = 'inputId';
                                     $selectInputField = $this->_fields[$field->getTable()->name.'.'.$pKey]->getQuery($selectInputField);
                                 }
                             }
                         }

                         $field->name = 'inputTitle';
                         $selectInputField = $field->getQuery($selectInputField);
                         $selectInputField->distinct();
                         $selectInputField->order($field->name);

                  	     foreach($preFilters['where'] as $filter) {
                            $selectInputField->where($filter);
                         }
                  	     foreach($preFilters['having'] as $filter) {
                            $selectInputField->having($filter);
                         }

                         $values = array(0 => _('Все'));
                         $rows = $selectInputField->query()->fetchAll();
                         if (count($rows)) {
                             foreach($rows as $row) {
                                 if(isset($field->callback))
                                     $row['inputTitle'] = call_user_func_array(array($controller, $field->callback), array($primaryKey, $row['inputTitle']));
                                  //$values[$row['inputId']] = $row['inputTitle'];
                                 $values[$row['inputTitle']] = $row['inputTitle'];
                             }
                         }

                         $inputField = array('name' => $field->getTable()->name.'_'.$primaryKey, 'title' => $field->title, 'type' => 'select', 'values' => $values);

                     } else {
                         $filter = false;
                         if (isset($options['filter'])) {
                             $filter = $options['filter'];
                         }
                         $inputField = array('name' => $field->getTable()->name.'_'.$field->name, 'title' => $field->title, 'type' => $field->type, 'filter' => $filter);
                     }


                     $inputFields[] = $inputField;
                 }
             }
             return $inputFields;
        }
        return array();

    }

    // Выдает ограничения для запроса, указанные в конструкторе отчетов
    private function _getPreFilters()
    {
        $conditions = array();
        foreach($this->_fields as $name=>$field)
        {
            $options = $field->getOptions();
            if(!isset($options['filter'])) continue;

            foreach($field->getColumn() as $expression) {};
            $expression = "{$expression}";

            switch($field->type){
                case 'string': $conditions[] = array($expression, "LIKE '%{$options['filter']}%'"); break;
                case 'double': 
                case 'integer': $conditions[] = array($expression, "= {$options['filter']}"); break;
                case 'date': 
                case 'datetimestamp': 
                    if($options['filter']['from']) {
                        $date = new HM_Date ($options['filter']['from']);
                        $date = $field->type=='date' ? $options['filter']['from'] : $date->getTimestamp();
                        $conditions[] = array($expression, ">= '{$date}'"); 
                    }
                    if($options['filter']['to']) {
                        $date = new HM_Date ($options['filter']['to']);
                        $date = $field->type=='date' ? $options['filter']['to'] : $date->getTimestamp();
                        $conditions[] = array($expression, "<= '{$date}'"); 
                    }
                break;
                default: continue;
            }
        }	
        //определяем возможность сунуть в where или в having (в зависимости от наличия аггрегатных ф-й)
        $having = $where = array();
        foreach($conditions as $condition) {
            $expression = $condition[0];
            $condition = $condition[1];

            $aggregateFunctions = array('count','sum', 'avg', 'max', 'min');
            $trimedExpression = str_replace(array('     ', '    ', '   ', '  ', ' '), '', $expression);
            $bIsAggrecateExpression = false;
            foreach($aggregateFunctions as $keyword) {
                if(stripos($trimedExpression, "{$keyword}(")!==false) { $bIsAggrecateExpression = true; break;
                }
            }
            $where2Sql  = "{$expression} {$condition}";
            if($bIsAggrecateExpression) {
                $having[] = $where2Sql;
            }else{
                $where[] = $where2Sql;
            }
        }
        return array('where'=>$where, 'having'=>$having);
    }

    public function setFields(array $fields)
    {
        foreach($fields as $i => $item) {
            $key = $item['field'];
            list($domainName, $categoryName, $fieldName) = explode('.', $key);
            if (strlen($domainName) && strlen($categoryName) && strlen($fieldName)) {
                $this->setDomain($domainName);
                $field = $this->getConfig()->getField($this->getDomain(), $categoryName, $fieldName);

                if ($field) {
                    $table = $this->getConfig()->getTable($this->getDomain(), $field->table);

                    $fullFieldName = $table->name . '.' . $fieldName;

                    if ($i = $this->_checkDuplicate($fullFieldName)) {
                        $field = clone $field;
                        $fullFieldName .= $i;
                        $field->name .= $i;
//                        Zend_Registry::get('session_namespace_default')->report['generator']['fields'][$i]['field'] .= $i;
                    }

                    $field->setTable($table);
                    if (isset($item['options'])) {
                        $field->setOptions($item['options']);
                    }

                    if (!$field->isAggregation()) {
                        $this->_setupPrimaryKeys($table);
                    }

                    if (null !== $this->getValue($field->getTable()->name.'_'.$field->name)) {
                        $field->setOption('filter', $this->getValue($field->getTable()->name.'_'.$field->name));
                    } else {
                        $filter = array();

                        if (null !== $this->getValue($field->getTable()->name.'_'.$field->name.'_from')) {
                            $filter['from'] = $this->getValue($field->getTable()->name.'_'.$field->name.'_from');
                        }

                        if (null !== $this->getValue($field->getTable()->name.'_'.$field->name.'_to')) {
                            $filter['to'] = $this->getValue($field->getTable()->name.'_'.$field->name.'_to');
                        }

                        if (count($filter)) {
                            $field->setOption('filter', $filter);
                        }
                    }

                    $this->_tables[$field->table] = $table;
                    $this->_fields[$fullFieldName] = $field;

                }
            }
        }


    }

    private function _checkDuplicate($fullFieldName)
    {
        $i = 0;
        if (!isset($this->_fields[$fullFieldName])) return false;
        do {
        	$uniqueFieldName = $fullFieldName . ++$i;
        } while (isset($this->_fields[$uniqueFieldName]));
        return $i;
    }

    private function _processTables(Zend_Db_Select $select)
    {
        $debug = (bool) (int) Zend_Registry::get('config')->debug;
/*
        $tables = $multiJoinTables = array();
        foreach($this->_tables as $tableName => $table) {
            if ($table->multiJoin && $table->hasRelations()) {
                foreach($table->getRelations() as $relationName => $relation) {
                    if (isset($this->_tables[$relationName])) {
                        if (!isset($table->multiJoinNames)) {
                            $table->multiJoinNames = array();
                        }
                        $table->multiJoinNames = array_merge($table->multiJoinNames, array($relationName));
                    }
                }
            }

            if ($table->multiJoin && $table->hasRelations() && $table->getMultiJoinNames()) {
                $multiJoinTables = array_merge($multiJoinTables, array($tableName => $table));
                continue;
            }

            $tables[$tableName] = $table;

        }

        if (count($multiJoinTables)) {
            $tables = array_merge($tables, $multiJoinTables);
        }

        $this->_tables = $tables;
*/
        $calculator = new HM_Report_Table_Distance_Calculator($this->getConfig()->getTables($this->getDomain()));

        $count = 0; $from = '';
        foreach($this->_tables as $table) {
            if ($count == 0) {
                $from = $table->name;
                $select->from(array($table->name => $table->table), array());
                $this->_joined[$table->name] = $table;
            } else {
                if (isset($this->_joined[$table->name])) continue;
                $path = $this->getConfig()->getPath($this->getDomain(), $from, $table->name);
                if (!$path) {
                    $calculator->calculate($from);
                    $path = $calculator->getPath($table->name);
                }
                if (count($path) >= 2) {
                    $localTable = array_shift($path);
                    foreach($path as $foreignTable)
                    {
                        if (!isset($this->_joined[$foreignTable])) {

                            $fTable = $this->getConfig()->getTable($this->getDomain(), $foreignTable);

                            $joines = array($localTable);

                            foreach(array_keys($fTable->getRelations()) as $fTableRelationName) {
                                if (isset($this->_joined[$fTableRelationName]) && !in_array($fTableRelationName, $joines)) {
                                    $joines[] = $fTableRelationName;
                                }
                            }

                            $select = $fTable->join($select, $joines);

                            $this->_joined[$foreignTable] = $fTable;
                        }

                        $localTable = $foreignTable;
                    }
                }
            }

            if ($debug) {
                // когда включен дебаг, сохраняем полезную инфу
                $this->log("Для связи между таблицами $from и {$table->name} использовался путь: $from".(!empty($path) ? '/'.implode('/', $path) : ''));
            }

            if (!empty($table->condition)) {
                $select->where($table->condition);
            }
            
            switch ($table->name) {
            	case 'Person':
            		$this->_personsResponsibility($select);
            	break;
            	case 'Subject':
            		$this->_subjectsResponsibility($select);
            	break;
            	default:
            	break;
            }

            /**
             * если у нас в отчете выборка только по классификатору,
             * то нам всёравно надо связать таблицы classifiers и classifiers_links
             * для фильтрации по типу
             */
            if (count($this->_tables) == 1) {
                switch ($table->name) {
                    case 'ClassifierSubject':
                        $select->joinInner(
                            array('cl' => 'classifiers_links'),
                            'cl.classifier_id = ClassifierSubject.classifier_id
                            AND cl.type = 0',
                            array()
                        );
                        break;
                    case 'ClassifierPerson':
                        $select->joinInner(
                            array('cl' => 'classifiers_links'),
                            'cl.classifier_id = ClassifierPerson.classifier_id
                            AND cl.type = 3',
                            array()
                        );
                        break;
                    default:
                        break;
                }
            }

            $count++;
        }

        return $select;
    }

    public function getQuery(Zend_Db_Select $select, HM_Controller_Action $controller)
    {
        if (is_array($this->_fields) && count($this->_fields)) {
        	
            $select = $this->_processTables($select);

            /** @var HM_Report_Table_Field $field */
            foreach($this->_fields as $field) {
                $select = $field->getQuery($select);
                $this->_gridFields = array_merge($this->_gridFields, $field->getGridField($controller));
                $this->_gridFilters = array_merge($this->_gridFilters, $field->getGridFilter());
            }

            if (APPLICATION_ENV == 'development') {
                $controller->getService('FireBug')->log('Report query: '.$select->__toString(), Zend_Log::INFO);
            }

            if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
                Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
                array(
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
                ))) {
                // все по области ответственности, даже не назначенные
                $select->joinLeft(array('pos' => 'structure_of_organ'), 'pos.mid = Person.MID', array());
                $soid = Zend_Registry::get('serviceContainer')->getService('Responsibility')->get();
                $responsibilityPosition = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getOne(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->find($soid));
                if ($responsibilityPosition) {
                    $subSelect = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getSelect()
                        ->from('structure_of_organ', array('soid'))
                        ->where('lft > ?', $responsibilityPosition->lft)
                        ->where('rgt < ?', $responsibilityPosition->rgt);
                    $select->where("pos.soid IN (?)", $subSelect);
                } else {
                    $select->where('1 = 0');
                }
            }

            return $select;
        }
        return false;
    }

    public function getGridFields()
    {
        return $this->_gridFields;
    }

    public function getGridFilters()
    {
        return $this->_gridFilters;
    }

    public function getGrid(HM_Controller_Action $controller)
    {
        $grid = $controller->getGrid(
            $this->getQuery($controller->getService('User')->getSelect(), $controller),
            $this->getGridFields(),
            $this->getGridFilters()
        );

        $gridFilters = new Bvb_Grid_Filters();
        foreach ($this->_fields as $field) {
            if ($field->sortable === false) {
                $grid->updateColumn($field->name, array(
                    'order' => false,
                ));
            }
            if ($field->isAggregation() && in_array('group_concat', $field->getAggregation())) {
                $grid->updateColumn($field->name, array(
                    'callback' => array(
                        'function' => array('HM_Controller_Action_Report', 'updateGroupConcat'),
                        'params' => array(sprintf('{{%s}}', $field->name)),
                    )
                ));
            }
        }
        return $grid;
    }

    protected function _subjectsResponsibility($select)
    {
        $container = Zend_Registry::get('serviceContainer');
        $currentUserId = $container->getService('User')->getCurrentUserId();
        $currentUserRole = $container->getService('User')->getCurrentUserRole();
        $options = $container->getService('Dean')->getResponsibilityOptions($currentUserId);

        if($options['unlimited_subjects'] != 1 && $container->getService('Acl')->inheritsRole($currentUserRole, HM_Role_Abstract_RoleModel::ROLE_DEAN)){
            $select = $container->getService('Responsibility')->checkSubjects($select, 'Subject.subid');
//            $select->joinInner(array('d' => 'deans'), 'd.subject_id = Subject.subid', array())
//                ->where('d.MID = ?', $currentUserId);
        }
    }
    
    protected function _personsResponsibility($select)
    {
        $container = Zend_Registry::get('serviceContainer');
        $currentUserId = $container->getService('User')->getCurrentUserId();
        $currentUserRole = $container->getService('User')->getCurrentUserRole();

        $roles = HM_Responsibility_ResponsibilityModel::getResponsibilityRoles();
        if ($container->getService('Acl')->inheritsRole($currentUserRole, $roles)) {
            $select->joinLeft(
                array('d2' => 'structure_of_organ'),
                'd2.mid = Person.MID',
                array()
            );
            $select = $container->getService('Responsibility')->checkUsers($select, 'd2');
        }
        return true;
    }
    public function getProviderTypeName($name, $providerType)
    {
        $types = array(
            HM_Tc_Provider_ProviderModel::TYPE_PROVIDER => _('Курс провайдера'),
            HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER => _('Курс учебного центра'),
            HM_Tc_Provider_ProviderModel::TYPE_NONE => _('Дистанционный'));
        if($types[$providerType]) {
            return $types[$providerType];
        }
    }
}