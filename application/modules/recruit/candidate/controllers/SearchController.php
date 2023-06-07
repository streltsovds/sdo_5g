<?php
/**
 * Candidates search controller
 *
 * @author tutrinov
 */
class Candidate_SearchController extends HM_Controller_Action_Vacancy
{
    const ITEMS_PER_PAGE = 20;

    private $_form;

    public function indexAction()
    {
        $this->view->setHeader(_('Автопоиск кандидатов'));
        
        /*@var $searchresult HM_Recruit_Candidate_Search_Result_AbstractItemsCollection */
        $searchResult = $this->getService('RecruitCandidate')->searchByVacancy($this->_vacancy);

        $this->view->vacancy = $this->_vacancy;
        $this->_setPaginator(self::_prepareForPaginator($searchResult));
    }

    public function formAction()
    {
        $this->view->setHeader(_('Поиск резюме'));

        $query = $this->_getParam('search_query', '');
        $this->view->error = false;
        
//        if ($this->getRequest()->isPost()) {
            
            if ($query == '') {
                $this->view->error = _('Пустой запрос');
            } else {
                $this->view->query = $query;
    
                $sphinx = HM_Search_Sphinx::factory();
                $sphinx->SetLimits(0,1000,1000);
                $sphinx->SetMatchMode( SPH_MATCH_BOOLEAN );
    //            $res = $sphinx->Query(iconv('Windows-1251','UTF-8',$query),'*');
                if ($results = $sphinx->Query($query, 'candidates')) {
                    $words = array_flip(explode(' ', $query));
                    $words = $words + (is_array($results['words']) ? $results['words'] : array());
                }
                        
                if (!$results || !isset($results['matches']) || count($results['matches']) == 0){
                    $this->view->error = _('Не найдено результатов, удовлетворяющих поисковому запросу');
                }
    
                $this->view->words = $words;
                $this->_setPaginator($results['matches']);


            }
//        }

        if ($this->_format = $this->_getParam('export')) {
            $this->_export($this->_format);
        }
    }  

    private function _prepareHhParams(&$params) {
        if(is_array($params['hh_area'])){
            $params['hh_area'] = current($params['hh_area']);
        }
        if(is_array($params['hh_citizenship'])){
            $params['hh_citizenship'] = current($params['hh_citizenship']);
        }
        
        if($params['hh_gender'] == '0'){
            unset($params['hh_gender']);
        }
        
        if($params['hh_education'] == '0'){
            unset($params['hh_education']);
        }
        
        if($params['hh_total_experience']['min'] > 0){
            $params['hh_total_experience']['min'] *= 12;//перевели годы в месяцы 
        }
        
        if($params['hh_total_experience']['max'] > 0){
            $params['hh_total_experience']['max'] *= 12;//перевели годы в месяцы 
        }
    }
    
    
    private function _prepareRangeParams(&$params) {
        foreach($params as $name => $value) {
            $nameParts = explode('_', $name);
            $lastPart  = end($nameParts);
            $lastIndex = key($nameParts);

            if($lastPart == 'min'){
                $min = $value;
                $nameParts[$lastIndex] = 'max';
                $paramMaxName = implode('_', $nameParts);
                $max = $params[$paramMaxName];
                unset($params[$name]);
                unset($params[$paramMaxName]);
                unset($nameParts[$lastIndex]);
                $newName = implode('_', $nameParts);
                $params[$newName] = array(
                    'min' => $min,
                    'max' => $max,
                );
            }
        }
    }
    
    
    public function advancedSearchAction()
    {
        $this->view->setHeader(_('Расширенный поиск'));

        $params = $this->_getAllParams();
        $this->_form = new HM_Form_SearchAdvanced();
        
        if ($collection = $this->getService('AtProfile')->findDependence(array('Position', 'CriterionValue'), $this->_vacancy->profile_id)) {
            $profile = $collection->current();        
        }
        
         if ($this->getRequest()->isPost() || !empty($params['page'])) {
            
            $sphinxFilters = $sphinxSubQueries = $searchCriteriaValues = $userIds = array();
    
            // если поиск не работает - возможно что-то забили добавить в NonSearchParams? 
            $nonSearchParams = self::getNonSearchParams();
            $fullTextParams = self::getFullTextParams();
            $rangeParams = self::getRangeParams();
            
            array_walk($params, array('Candidate_SearchController', '_unescapeDots'));
            
            $this->_prepareRangeParams($params);
            
            $this->_prepareHhParams($params);
            
            foreach ($params as $key => $value) {
    
                if (in_array($key, $nonSearchParams)) {
                    unset($params[$key]);
                    continue;
                }
    
                
                if (array_key_exists($key, $rangeParams)) {
                    if (is_array($value) && ($value['min'] != '' || $value['max'] != '')) {
                        if(!$value['min']){
                            $value['min'] = $rangeParams[$key]['min'];
                        }
                        if(!$value['max']){
                            $value['max'] = $rangeParams[$key]['max'];
                        }
                        $sphinxFilters[$key] = $value;
                    } else {
//                        unset($params[$key]);
                    }
                } elseif (in_array($key, $fullTextParams)) {
                    if (strlen($value = trim($value))) {
                        $sphinxSubQueries[] = sprintf('@%s %s', $key, $value);
                    } else {
//                        unset($params[$key]);
                    }
                } else { // attributes
                    
                    if ((strpos($key, 'criterion_') !== false)) { 
                        if ($value) {// нулевые уровни развития не учитываем
                            $searchCriteriaValues[$key] = $value;
                        } 
                    } elseif ($value != -1 && $value != '') {
                        $sphinxFilters[$key] = $value;
                    } else {
//                        unset($params[$key]);
                    }
                }
            }
            
            if (count($searchCriteriaValues)) {
                $sphinxFilters['user_id'] = 0;
                $requiredCriteriaCount = count($searchCriteriaValues);
                $select = $this->getService('AtSessionUserCriterionValue')->getSelect()
                    ->from(array('asucv' => 'at_session_user_criterion_values'), array('criterion_id', 'criterion_type', 'maxValue' => new Zend_Db_Expr("MAX(value)")))
                    ->join(array('asu' => 'at_session_users'), 'asucv.session_user_id = asu.session_user_id', array('user_id'))
                    ->group(array('asu.user_id', 'asucv.criterion_id', 'asucv.criterion_type'));
                
                $stmt = $select->query();
                $stmt->execute();
                $rows = $stmt->fetchAll();
                
                foreach ($rows as $row) {
                    
                    if (!isset($userIds[$row['user_id']])) $userIds[$row['user_id']] = $searchCriteriaValues;
                    $key = "criterion_{$row['criterion_type']}_{$row['criterion_id']}";

                    // если результат есть и он выше чем надо
                    if (isset($searchCriteriaValues[$key]) && ($row['maxValue'] >= $searchCriteriaValues[$key])) {
                        unset($userIds[$row['user_id']][$key]);
                    }
                }
                $userIds = array_filter($userIds, array('Candidate_SearchController', '_filterNotArray'));  
                if (count($userIds)) { 
                    $sphinxFilters['user_id'] = implode('|', array_keys($userIds));
                }
            }
    
            $sphinxQuery = implode(' ', $sphinxSubQueries);
    
            if (count($params)) {
    
                $sphinx = HM_Search_Sphinx::factory();
                $sphinx->SetLimits(0, 1000, 1000);
                $sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
                
                foreach ($sphinxFilters as $key => $value) {
                    if (!is_array($value)) {
                        $sphinx->SetFilter($key, explode('|', $value));
                    } else {
                        $sphinx->SetFilterRange($key, $value['min'], $value['max']);
                    }
                }

//$sphinxQuery = '@fio Алексеев';
//$sphinx->SetFilter('user_id', array(258208, 258468));

                $this->getService('FireBug')->log($sphinxQuery, Zend_Log::INFO);
                $results = $sphinx->Query($sphinxQuery, 'candidates');
    
                if (count($results['matches']) == 0){
                   $this->view->error = _('Резюме не найдены');
                }            
    
                $this->view->query = $params['resume'];
                $this->_setPaginator($results['matches']);
    
                if ($this->_format = $this->_getParam('export')) {
                    $this->_export($this->_format);
                }            
                
                $this->_form->setDefaults($params);
    
                array_walk($params, array('Candidate_SearchController', '_escapeDots'));
                $this->view->params = $params;
            }
    
            if (empty($results)){

                // fallback на случай когда нет сфинкса
                if (!empty($params['fio'])) {
                    $collection = $this->getService('User')->fetchAllDependenceJoinInner('Candidate', $this->getService('User')->quoteInto(array(
                        new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(self.LastName, ' ') , self.FirstName), ' '), self.Patronymic) LIKE ?"),
                    ), array(
                        '%' . $params['fio'] . '%'
                    )));

                    if (count($collection)) {

                        $results = $positions = array();
                        $this->view->error = false;
                        $userIds = $collection->getList('MID');
                        if (count($userIds)) {
                            $positions = $this->getService('Orgstructure')->fetchAll(array('mid in (?)' => $userIds));
                            $positions = $positions->getList('mid', 'soid');
                        }

                        foreach ($collection as $user) {
                            $results[$user->MID] = array(
                                'attrs' => array(
                                    'user_id' => $user->MID,
                                    'current_position_id' => $positions[$user->MID] ? : 0,
                                ),
                            );
                        }

                        $this->_setPaginator($results);
                    }
                }
            }

            if (empty($results)){
               $this->view->error = _('Не найдено результатов, удовлетворяющих поисковому запросу');
            }
        } else {
            
            $defaults = array();
            if ($profile) {
// эти дефолтные параметры больше мешают; приходится их убирать, чтобы найти хоть кого-нибудь
//                 $defaults['profile'] = $profile->name;
//                 if (count($profile->positions)) {
//                     $position = $profile->positions->current();
//                     $defaults['position'] = $position->name;
//                     if ($department = $this->getService('Orgstructure')->find($position->owner_soid)) {
//                         $defaults['department'] = $department->current()->name;
//                     } 
//                 }
// а эти дефолтные параметры убирать нельзя, т.к. Slider имеет нехороший баг - при нулевых значениях не инициализируется и не работает 
                if (count($profile->criteriaValues)) {
                    foreach ($profile->criteriaValues as $criterionValue) {
                        $defaults["criterion_{$criterionValue->criterion_type}_{$criterionValue->criterion_id}"] = (int)$criterionValue->value;
                    }
                }
            }
            $this->_form->setDefaults($defaults);
        }
        
        $this->view->form = $this->_form;
    }
    
    // ВНИМАНИЕ! Все варианты поиска используют этот метод
    // Рефакторить осторожно!
    private function _setPaginator($results)
    {
        if ($this->view->error == false) {

            if($this->_vacancy->position){
                $currentPositionUserId = (int)$this->_vacancy->position->current()->mid;
            } else {
                $currentPositionUserId = 0;
            }
            
            // выделяем в результатах тех, кто уже включен
            $userIds = array();
            if (count($this->_vacancy->candidates)) {
                $userIds = $this->_vacancy->candidates->getList('user_id');
            }
            $this->view->existingUserIds = $userIds;

            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)) {
                $filteredResults = array();
                $accountableUserIds = $this->getService('Responsibility')->getAccountableUsers();
                foreach ($results as $key => $result) {
                    if (in_array($result['attrs']['user_id'], $accountableUserIds)) {
                        $filteredResults[$key] = $result;
                    }
                }
                $results = $filteredResults;
            }

            $page = $this->_getParam('page', 0);
            $paginator = Zend_Paginator::factory ($results);
            $paginator->setCurrentPageNumber((int)$page);
            $paginator->setItemCountPerPage($page === 'all' ? $paginator->getTotalItemCount() : self::ITEMS_PER_PAGE);

            $users = $candidateUserIds = $positionUserIds = array();
            $currentItems = $paginator->getCurrentItems();
            foreach($currentItems as $key => $value){
                if (empty($value['attrs']['current_position_id'])) {
                    $candidateUserIds[] = $value['attrs']['user_id'];
                } else {
                    $positionUserIds[] = $value['attrs']['user_id'];
                }
            }
            
            if (count($positionUserIds) > 0) {
                $users = $this->getService('User')->fetchAllHybrid('Position', 'Profile', 'Position', array('MID IN (?)' => $positionUserIds))->asArrayOfObjects();
            }

            if (count($candidateUserIds) > 0) {
                $users = $users + $this->getService('User')->fetchAllHybrid('Candidate', 'Vacancy', 'VacancyCandidate', array('MID IN (?)' => $candidateUserIds))->asArrayOfObjects();
            }
            
            // @todo: правильнее включать это условие до пагинатора, чтоб не сбивать количество элементов
            if (isset($users[$currentPositionUserId])) unset($users[$currentPositionUserId]);

            // @todo: реализовать отдельные searchItem для пользователей и кандидатов
            foreach ($currentItems as $key => &$value) {
                // $value['obj'] - для показа в результатах поиска; только текущая страница
                // $this->_data - для экспорта; все страницы
                $this->_data[] = $value['obj'] = $users[$value['attrs']['user_id']]; 
            }

            $this->view->paginator = $paginator;
            
            $actions = array();

            $state = $this->getService('Process')->getCurrentState($this->_vacancy);
            if ($state && !is_a($state, 'HM_Recruit_Vacancy_State_Hire')) {
                $actions = array(
                    $this->view->url(array('module' => 'candidate', 'controller' => 'list', 'action' => 'assign-new', 'switcher' => null)) => array(
                        'label' => _('Включить в сессию подбора'),         
                        'confirm' => _('Вы уверены, что хотите включить выбранных кандидатов в данную сессию подбора? Если сессия подбора уже идёт, им сразу будут назначены оценочные мероприятия.'),
                    ),
//                    $this->view->url(array('module' => 'candidate', 'controller' => 'list', 'action' => 'assign-new-hold-on', 'switcher' => null)) => array(
//                        'label' => _('Включить в сессию подбора в качестве потенциального кандидата'),
//                        'confirm' => _('Вы уверены, что хотите включить выбранных кандидатов или пользователей в данную сессию подбора в качестве потенциальных кандидатов? При этом оценочные мероприятия назначены не будут. Внешние кандидаты не могут проходить одновременно несколько сессий подбора, они будут автоматически исключены.'),
//                    )
                );
            }  
            $this->view->actions = $actions;            
        }
    }
    
    static public function getRangeParams()
    {
        return array(
//            'age' => array('from' => 15, 'to' => 65), // key => defaultRange
            'hh_age'              => array('min' => 1, 'max' => 100),
            'hh_salary'           => array('min' => 1, 'max' => 1000000),
            'hh_total_experience' => array('min' => 0, 'max' => 1200),
        );
    }

    static public function getFullTextParams()
    {
        return array(
            'fio',
            'profile',
            'position',
            'department',
            'resume',
            
//            'hh_area',
            'hh_metro',
            'hh_education',
            'hh_citizenship',
            'hh_gender',
        );
    }

    static public function getNonSearchParams()
    {
        return array(
            'module',
            'controller',
            'action',
            'submit',
            'cancelUrl',
            'page',
            'export',
            'vacancy_id',
            'switcher',
            'list-switcher',
            
            'hh_area_fcbkComplete_input',
            'hh_citizenship_fcbkComplete_input',
        );
    }
    
    static public function _escapeDots(&$value)
    {
        $value = str_replace('.', '~', $value);
    }

    static public function _unescapeDots(&$value)
    {
        $value = str_replace('~', '.', $value);
    }    

    protected function _filterNotArray($arr)
    {
        return !count($arr);
    }
    
    static public function _prepareForPaginator($collection)
    {
        $return = array();
        foreach ($collection as $item) {
            $userId = $item->getCandidateId(); // здесь candidateId == People.MID
            $data = $item->getAdditionalData(); 
            $return[$userId]['attrs']['user_id'] = $userId;
            if (isset($data['currentPositionId'])) $return[$userId]['attrs']['current_position_id'] = $data['currentPositionId'];
        }
        return $return;
    }
}