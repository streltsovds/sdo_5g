<?php
class Programm_EvaluationController extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;

    private $_programmId = 0;
    private $_programm = null;
    private $_mode = 0;

    private $_profile = null;
    private $_vacancy = null;

    public function init()
    {
        $this->_programmId = (int) $this->_getParam('programm_id' , 0);
        $this->_programm = $this->getOne($this->getService('Programm')->findDependence('Event', $this->_programmId));
        $this->_mode = (int) $this->_getParam('mode', 0);

        $profileId = $this->_getParam('profile_id', 0);
        $vacancyId = $this->_getParam('vacancy_id', 0);

        if ($profileId && count($profiles = $this->getService('AtProfile')->find($profileId))) {

            $this->_profile = $profiles->current();
            $this->initContext($this->_profile);
            $this->view->addSidebar('profile', [
                'model' => $this->_profile,
            ]);

            $this->view->setBackUrl($this->view->url([
                'baseUrl' => 'at',
                'module' => 'profile',
                'controller' => 'list',
            ], null, true));

            // navigation вручную .)
            switch ($this->_programm->programm_type) {
                case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                    $currentPageId = 'mca:profile:index:programm-recruit';
                    $subHeader = _('Программа подбора');
                    break;
                case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                    $currentPageId = 'mca:profile:index:programm-assessment';
                    $subHeader = _('Программа регулярной оценки');
                    break;
                case HM_Programm_ProgrammModel::TYPE_RESERVE:
                    $currentPageId = 'mca:profile:index:programm-reserve';
                    $subHeader = _('Программа оценки кадрового резерва');
                    break;
            }

            $contextNavigation = $this->view->getContextNavigation();
            $currentPage = $contextNavigation->findOneBy('id', $currentPageId);
            if ($currentPage) $currentPage->setActive(true);

            $this->view->setSubSubHeader($subHeader);


        } elseif ($vacancyId && count($vacancies = $this->getService('RecruitVacancy')->findDependence('Profile', $vacancyId))) {

            // @todo: вообще, неплохо бы наследовать от HM_Controller_Action_Vacancy
            // а не копировать этот код здесь
            $this->_vacancy = $vacancies->current();
            
            if (!$this->isAjaxRequest()) {

                $this->getService('Process')->initProcess($this->_vacancy);
                $this->initContext($this->_vacancy);
                $this->view->addSidebar('vacancy', [
                    'model' => $this->_vacancy,
                    
                   
                ]);

                if (count($this->_vacancy->profile)) {
                    $this->_profile = $this->_vacancy->profile->current();
                }
            }
        } else {

            $this->view->setHeader($this->_programm->name);
            $this->view->setSubHeader(_('Редактирование программы'));

            if (isset($_SERVER['HTTP_REFERER'])) {
                $redirectUrl = parse_url($_SERVER['HTTP_REFERER']);
                $path = isset($redirectUrl['path']) ? $redirectUrl['path'] : '';
                $query = isset($redirectUrl['query']) ? '?' . $redirectUrl['query'] : '';
                $redirectUrl = $path . $query;

                // $params = [
                //     'redirect_url' => urlencode($redirectUrl),
                // ];

                $this->view->setBackUrl($redirectUrl);
            } else {
                $this->view->setBackUrl($this->view->url([
                    'module' => 'programm',
                    'controller' => 'list',
                    'programm_id' => null,
                ]));
            }
        }

        if (!$this->_getParam('start',0) && !$this->_getParam('end',0)) {
            $this->view->showCopyButton = $this->_programm->item_type == HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY;
        }

        parent::init();
    }

    public function indexAction()
    {
        $processes = $this->getService('Programm')->getActiveProcesses($this->_programm);

        $this->view->processes = $processes;
        $this->view->editable = !count($processes);

        // существующие элементы программы
        $events = $eventsHidden = array();
        $collection = $this->getService('ProgrammEvent')->fetchAllDependence('Evaluation',
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?'),
                array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_AT)
            ), 
            'ordr'
        );
        foreach ($collection as $event) {
            if (count($event->evaluation)) {
                $evaluation = $event->evaluation->current();
                if ($evaluation->method == HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE) continue; // итговоые формы добавляются по-другому
                $events[$evaluation->submethod] = $event; //$this->getService('AtEvaluation')->getProgrammTitle($evaluation);
                if ($event->hidden) $eventsHidden[] = $evaluation->submethod;
            }
        }        
        
        // потенциально возможные элементы
        $items = array();
        $subjects = HM_At_Evaluation_EvaluationModel::getSubjectsMethods();

        foreach ($subjects as $subjectId => $methodIds) {

            $item = array(
                'name' => HM_At_Evaluation_EvaluationModel::getSubjectTitle($subjectId),        
                'subitems' => array(),        
            );

            foreach ($methodIds as $methodId) {
                $methodClass = 'HM_At_Evaluation_Method_' . ucfirst($methodId) . 'Model';
                if (call_user_func(array($methodClass, 'isAvailableForProgramm'), $this->_programm->programm_type)) {
                    $subMethods = call_user_func(array($methodClass, 'getSubMethods'), $methodClass);
                    foreach ($subMethods as $key => $title) {
                        if ($relationType = HM_At_Evaluation_EvaluationModel::getRelationTypeAliasFromSubmethod($key)) {
                            $relationTypeClass = 'HM_At_Evaluation_Method_' . ucfirst($methodId) . '_' . ucfirst($relationType) . 'Model';
                            if (!call_user_func(array($relationTypeClass, 'isAvailableForProgramm'), $this->_programm->programm_type)) {
                                continue;
                            }
                        }
//                        $item['subitems'][$key] = $title;
                        $item['subitems'][$key] = array('name' => $title);
                    }
                }
            }
            
            if (count($item['subitems'])) {
                $items[$subjectId] = $item;
            }
        }
//
//        $this->view->modeCheckbox = new Zend_Form_Element_Checkbox('mode_strict', array(
//            'Label' => _('Последовательный режим прохождения'),
//            'Description' => _('Последовательный режим прохождения'), // @todo: tooltip не отображается
//            'Value' => $this->_programm->mode_strict,
//            'Decorators' => array(
//                array('ViewHelper'),
//                //array('Description', array('tag' => 'p', 'class' => 'description')),
//                array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
//                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
//            )
//        ));
//
//        if (in_array($this->_programm->programm_type, array(
//                HM_Programm_ProgrammModel::TYPE_ADAPTING,
//                HM_Programm_ProgrammModel::TYPE_RECRUIT,
//                HM_Programm_ProgrammModel::TYPE_RESERVE
//        ))) {
//            $this->view->finalizeCheckbox = new Zend_Form_Element_Checkbox('mode_finalize', array(
//                'Label' => _('Завершить программу итоговой формой'),
//                'Description' => _('Завершить программу итоговой формой'), // @todo: tooltip не отображается
//                'Value' => $this->_programm->mode_finalize,
//                'Decorators' => array(
//                    array('ViewHelper'),
//                    //array('Description', array('tag' => 'p', 'class' => 'description')),
//                    array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
//                    array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
//                )
//            ));
//        }
        $options =  array(
            'mode_strict' => array(
                'label' =>  _('Последовательный режим прохождения'),
                'value' => $this->_programm->mode_strict
            )
        );

        if (in_array($this->_programm->programm_type, array(
            HM_Programm_ProgrammModel::TYPE_ADAPTING,
            HM_Programm_ProgrammModel::TYPE_RECRUIT,
            HM_Programm_ProgrammModel::TYPE_RESERVE
        ))) {
            $options['mode_finalize'] = array(
                'label' => _('Завершить программу итоговой формой'),
                'value' => $this->_programm->mode_finalize
            );
        }

        $this->view->options = $options;
        $this->view->page = 0;
        $this->view->items = $items;
        $this->view->events = $events;
        $this->view->eventsHidden = $eventsHidden;
        $this->view->programm = $this->_programm;
        $this->view->vacancy = $this->_vacancy;
        $this->view->finalizesubmethod = implode('_', array(HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE, $this->_programm->programm_type));
    }
    
    public function assignAction()
    {
        if ($this->isAjaxRequest()) {
            $this->getHelper('viewRenderer')->setNoRender();

            $keys = $this->_getParam('item_id', array());
            
            $attribute = HM_Programm_ProgrammModel::getItemAttribute($this->_programm->item_type);
            $collection = $this->getService('AtEvaluation')->fetchAll(array(
                "{$attribute} = ?" => $this->_programm->item_id,
                'programm_type = ?' => $this->_programm->programm_type
            ));
            $evaluations = $collection->asArrayOfObjects(); 
            $evaluationKeys = $evaluationsIdsToDel = count($collection) ? $collection->getList('submethod', 'evaluation_type_id') : array();

            // это чтобы удалить дубликаты
            foreach ($evaluations as $evaluationId => $evaluation) {
                if (!in_array($evaluationId, $evaluationKeys)) $evaluationsIdsToDel[] = $evaluationId;
            }

            if (count($evaluationKeys)) {
                // на всякий случай проверяем чтобы не было лишних programmEvents
                if (count($collection = $this->getService('ProgrammEvent')->fetchAllDependence('ProgrammEventUser', $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?', ' AND item_id NOT IN (?)'),
                        array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_AT, $evaluationKeys)
                    )
                ))) {
                    foreach ($collection as $programmEvent) {
                        $this->getService('ProgrammEvent')->deleteEvent($programmEvent);
                    }
                }
            }
            
            if (count($keys)) {
                foreach($keys as $ordr => $key) {

                    // возможно следует всегда всё удалять и заново вставлять
                    // сейчас если в evaluation_types как-то затесался неправильный evaluation с такой же submethod - он никак не удаляется  
                    if (!array_key_exists($key, $evaluationKeys)) {

                        list($methodId, $relationTypeId) = explode('_', $key);
                        $methodClass = 'HM_At_Evaluation_Method_' . ucfirst($methodId) . 'Model';
                        $defaultRelationType = call_user_func(array($methodClass, 'getDefaultRelationType'), $this->_programm->programm_type);

                        $scaleId = 0;
                        if ($methodId == HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE) {
                            $optionsScope = $this->_programm->programm_type == HM_Programm_ProgrammModel::TYPE_RECRUIT ? HM_Option_OptionModel::MODIFIER_RECRUIT : HM_Option_OptionModel::MODIFIER_AT;
                            $scaleId = $this->getService('Option')->getOption('competenceScaleId', $optionsScope);
                        }

                        $evaluation = $this->getService('AtEvaluation')->insert(array(
                            $attribute => $this->_programm->item_id,
                            'name' => HM_At_Evaluation_EvaluationModel::getMethodTitle($methodId),
                            'method' => $methodId,
                            'submethod' => $key,
                                
                            // у этих типов формат submethod отличается, в нём не relation_type 
                            // TYPE_FORM - id формы
                            // TYPE_FINALIZE - тип программы (подбор, адаптация,..)
                            'relation_type' => $defaultRelationType ? : $relationTypeId,
                            'scale_id' => $scaleId,
                            'programm_type' => $this->_programm->programm_type,
                        ));
                        $this->getService('AtEvaluation')->updateCriteria($evaluation);
                    } else {
                        unset($evaluationsIdsToDel[$key]);
                        $evaluation = $evaluations[$evaluationKeys[$key]];
                    }                    
                    $this->getService('Programm')->assignItem(array(
                        'programm_id' => $this->_programmId, 
                        'item_id' => $evaluation->evaluation_type_id, 
                        'type' => HM_Programm_Event_EventModel::EVENT_TYPE_AT, 
                        'name' => $this->getService('AtEvaluation')->getProgrammTitle($evaluation),
                        'ordr' => $ordr,
                    ));
                }
            }

            // @todo: реализовать удаление на уровне модели
            // сейчас продублировано в HM_At_Profile_ProfileService
            if (count($evaluationsIdsToDel)) {
                $this->getService('AtEvaluation')->deleteBy(
                    $this->quoteInto(
                        array('evaluation_type_id IN (?)'),
                        array($evaluationsIdsToDel)
                    )
                );
                
                $this->getService('AtEvaluationCriterion')->deleteBy(
                    $this->quoteInto(
                        array('evaluation_type_id IN (?)'),
                        array($evaluationsIdsToDel)
                    )
                );
                
                if (count($collection = $this->getService('ProgrammEvent')->fetchAllDependence('ProgrammEventUser', $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?', ' AND item_id IN (?)'),
                        array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_AT, $evaluationsIdsToDel)
                    )
                ))) {
                    foreach ($collection as $programmEvent) {
                        $this->getService('ProgrammEvent')->deleteEvent($programmEvent);
                    }                    
                }
            }
            
            $needUpdate = false;
            $modeStrict = $this->_getParam('mode_strict');
            if ($this->_programm->mode_strict != $modeStrict) {
                $this->_programm->mode_strict = $modeStrict;
                $needUpdate = true;
            }

            $modeFinalize = $this->_getParam('mode_finalize');
            if ($this->_programm->mode_finalize != $modeFinalize) {
                $this->_programm->mode_finalize = $modeFinalize;
                $needUpdate = true;
            }

            $programm = $this->getService('Programm')->update($this->_programm->getValues());

            if ($this->_mode == 1 && $programm->item_type == 0) {

                try {
                    // Если необходимо скопировать программу в профили
                    $categoryId = $programm->item_id;
                    $profiles = $this->getService('AtProfile')->fetchAllDependence('Position',
                        $this->quoteInto("base_id IS NULL AND category_id = ?", $categoryId)
                    );
                    if (count($profiles)) {
                        foreach ($profiles as $profile) {
                            $this->getService('AtProfile')->assignProgrammFromCategory($profile, $programm);
                        }
                    }

                    echo HM_Json::encodeErrorSkip(array('result' => 1));
                } catch (Exception $e) {
                    echo HM_Json::encodeErrorSkip(array('result' => 0));
                }
                exit();
            }

        } else {
            $this->_redirector->gotoSimple('index');
        }
    }
    
    public function calendarAction()
    {
        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {

            $begin = HM_Date::getAbstractDay(intval($this->_getParam('start')));
            $end   = HM_Date::getAbstractDay(intval($this->_getParam('end')));

            if (count($this->_programm->events)) {
                $itemIds = $this->_programm->events->getList('item_id');
                $evaluations = $this->getService('AtEvaluation')->fetchAll(array('evaluation_type_id IN (?)' => $itemIds))->asArrayOfObjects();
            }
            
            $eventsSources = array();
            $methodColors = HM_At_Evaluation_EvaluationModel::getMethodColors();
            $relationTypeColors = HM_At_Evaluation_EvaluationModel::getRelationTypeColors();
            foreach ($this->_programm->events as $event) {
                if (($event->day_begin + 1 <= $end) && ($event->day_end + 1 >= $begin) && ! $event->hidden) {
                    
                    list($methodId, $relationTypeId) = explode('_', trim($evaluations[$event->item_id]->submethod));
                    $day_begin = (int)$event->day_begin ? (int)$event->day_begin : 1;
                    $day_end = (int)$event->day_end ? (int)$event->day_end : 1;
                    $eventsSources[] = array(
                        'id'    => $event->programm_event_id,
                        'title' => $this->getService('AtEvaluation')->getProgrammTitle($evaluations[$event->item_id]),
                        'color' => ($methodId == HM_At_Evaluation_EvaluationModel::TYPE_FORM) ? '#888' : $relationTypeColors[$relationTypeId],
                        //первая секунда дня
                        'start' => ($day_begin - 1) * 86400 + 1,//86400 = 60s*60m*24h
                        'end'   => ($day_end - 1) * 86400 + 1,
                        'editable' => true,
                        'borderColor' => $methodColors[$methodId]
                    );                    
                }
            }
            $tempView = $this->view->assign($eventsSources);
            unset($tempView->lists);

        } else {
            $this->view->source = array('module'=>'programm', 'controller'=>'evaluation', 'action'=>'calendar', 'no_user_events' => 'y');
            $this->view->editable = !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
        }        
    }
    
    public function saveCalendarAction()
    {
        $eventId = $this->_getParam('eventid',0);
        $begin = HM_Date::getAbstractDay(floatval($this->_getParam('start'))/1000); //в миллисекундах
        $end   = HM_Date::getAbstractDay($endSeconds = floatval($this->_getParam('end'))/1000);

        $result    = _('При сохранении данных произошла ошибка');
        $status    = 'fail';

        if ($this->_request->isPost() && $eventId && $begin && $end) {

            $event = $this->getService('ProgrammEvent')->getOne($this->getService('ProgrammEvent')->find($eventId));
            if ($event) {
                $data = $event->getData();
                $data['day_begin'] = $begin;
                $data['day_end'] = $endSeconds ? $end : $begin;
                if ($this->getService('ProgrammEvent')->update($data)) {
                    $result = _('Данные успешно сохранены');
                    $status = 'success';
                }
            }
        }
        $this->view->status = $status;
        $this->view->msg    = $result;
    }
    
    public function editAction()
    {
        if ($this->getRequest()->isPost()) {

            $method = $this->_getParam('method');
            $formClass = 'HM_Form_Methods_' . ucfirst($method);
            $request = $this->getRequest();

            $programmEventId = $this->_getParam('programm_event_id');
            $programmEvent = $this->getService('ProgrammEvent')->getOne($this->getService('ProgrammEvent')->findDependence('Programm', $programmEventId));
            if (count($programmEvent->programm)) $programm = $programmEvent->programm->current();

            if (class_exists($formClass) && $request->isPost()) {
                $form = new $formClass();
                if ($form->isValid($request->getParams())) {
                    $values = $form->getValues();

                    if ($programm && $programm->isEditCriteriaFromProgramm()) {
                        $criteriaIds = is_array($values['criteria']) ? array_filter($values['criteria']) : array(); // filter empty

                        $overrideQuests = array();
                        foreach ($values as $key => $value) {
                            list($name, $criterionId) = explode('_', $key);
                            if ($name == 'criterion') {
                                $overrideQuests[$criterionId] = $value;
                            }
                        }

                        $this->getService('AtEvaluationCriterion')->assignCriteria($values['evaluation_id'], $criteriaIds, $overrideQuests);
                    }

                    $memos = is_array($values['memos']) ? array_filter($values['memos']) : array(); // filter empty
                    $this->getService('AtEvaluationMemo')->assignMemos($values['evaluation_id'], $memos);

                    if (!empty($values['name'])) {
                        $programmEvent->name = $values['name'];
                        $programmEvent->hidden = $values['hidden'];
                        $this->getService('ProgrammEvent')->update($programmEvent->getValues());
                    }
                }

                $this->_flashMessenger->addMessage(_('Элемент успешно обновлён'));
            }
        }

        $this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/fieldset.js'));
        $programmId = $this->_getParam('programm_id');
        $programm = $this->getService('Programm')->getOne($this->getService('Programm')->find($programmId));

        if ($submethod = $this->_getParam('submethod')) {
            $method = HM_At_Evaluation_EvaluationModel::parseSubmethod($submethod);
        }

        if ($programmId && $submethod) {

            if (count($collection = $this->getService('AtEvaluation')->fetchAllDependenceJoinInner('ProgrammEvent', $this->getService('AtEvaluation')->quoteInto(
                ['self.submethod = ? AND ', 'ProgrammEvent.programm_id = ?'],
                [$submethod, $programmId]
            )))) {
                $evaluation = $collection->current();

                // fetchAllDependenceJoinInner не фильтрует по параметрам связанных сущностей, поэтому нельзя сделать
                // ProgrammEvent.type = HM_Programm_ProgrammModel::TYPE_ELEARNING
                $currentEvent = false;
                $events = $evaluation->programmEvent;
                foreach ($events as $event) {
                    if($event->type == HM_Programm_ProgrammModel::TYPE_ELEARNING) {
                        $currentEvent = $event;
                        break;
                    }
                }

                // Если мы нашли нужный event, создаём новую зависимость для $evaluation
                // Если нет - возвращаем $events из оригинальной сущности
                $evaluation->programmEvent = $currentEvent
                    ? new HM_Collection([$currentEvent->getData()], get_class($currentEvent))
                    : $events;
            }
            if ($evaluation) {
                $formClass = 'HM_Form_Methods_' . ucfirst($method);
                if (class_exists($formClass)) {

                    /** @var HM_Form $form */
                    $form = new $formClass();
                    $form->setDefaultsByEvaluation($evaluation);
                    if (!$programm->isEditCriteriaFromProgramm()) {
                        $form->removeElement('criteria');
                        foreach ($form->getElements() as $name => $element) {
                            if (strpos($name, 'criterion_') !== false) {
                                $form->removeElement($name);
                            }
                        }
                    }
                    $this->view->form = $form;
                }
            }
        }

    }

    // вообще-то ajax тут не нужен совсем
    public function criteriaListAction()
    {
        $this->_ajaxify();
        
        $categoryId = 0;
        if ($this->_profile) {
            $categoryId = $this->_profile->category_id;
        }

        $clusteredCriteria = $this->getService('AtCriterionCluster')->getClustersCriteria($categoryId);
        $nonClusteredCriteria = $this->getService('AtCriterion')->getNonClustered();
        $method = $this->_getParam('method', HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE);

        if ($evaluationId = $this->_getParam('evaluation_id')) {
            $selectedCriteria = $this->getService('AtEvaluationCriterion')->fetchAll(array('evaluation_type_id = ?' => $evaluationId))->getList('criterion_id');
        }

        $count = 0;
        foreach($clusteredCriteria as $cluster) {
            if ($count++ > 0) echo "\n";
            $cluster_id = 'cluster_' . $cluster->cluster_id;
            echo sprintf("%s=%s", $cluster_id, $cluster->name);
            foreach ($cluster->criteria as $criterion) {
                if ($categoryId && ($criterion->category_id != $categoryId)) continue;
                echo "\n";
                $criterion_id = $criterion->criterion_id;
                if (in_array($criterion_id, $selectedCriteria)) {
                    $criterion_id .= '+';
                }
                echo sprintf("%s=- %s", $criterion_id, $criterion->name);
            }
        }
        $count = count($clusteredCriteria);
        if (count($nonClusteredCriteria)) {
		    if ($count++ > 0) echo "\n";
        	echo sprintf("%s= [%s]", -1, _('нет кластера'));
        	foreach($nonClusteredCriteria as $criterion) {
        	    if ($categoryId && ($criterion->category_id != $categoryId)) continue;
            	$criterion_id = $criterion->criterion_id;
                if (in_array($criterion->criterion_id, $selectedCriteria)) {
                    $criterion_id .= '+';
                }
                echo sprintf("\n%s=- %s", $criterion_id, $criterion->name);
        	}
        }
    }

    public function criteriaTestListAction()
    {
        $this->_ajaxify();
        $programmType = $this->_getParam('programm_type', null);
		$selectedCriteria = array();
        if ($evaluationId = $this->_getParam('evaluation_id')) {
            $selectedCriteria = $this->getService('AtEvaluationCriterion')->fetchAll(array('evaluation_type_id = ?' => $evaluationId))->getList('criterion_id');
        }		
//         if (count($this->_vacancy->evaluations)) {
//             foreach ($this->_vacancy->evaluations as $evaluation) {
//                 $programTypeCheckResult = ($programmType !== null) ? ($evaluation->programm_type == $programmType) : true;
//                 if ($evaluation->method == HM_At_Evaluation_EvaluationModel::TYPE_TEST && $programTypeCheckResult) {
//                     $selectedCriteria = Zend_Registry::get('serviceContainer')->getService('AtEvaluationCriterion')->fetchAll(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id))->getList('criterion_id');
//                     break;
//                 }
//             }
//         }

        echo $this->getService('AtCriterionTest')->getTreeContentForMultiselect($selectedCriteria);
    }

    private function _ajaxify()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
    }
    
}