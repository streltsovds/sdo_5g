<?php
class Profile_MethodController extends HM_Controller_Action_Profile
{
    private $_profile;
    private $_forms = array();

    public function init()
    {
        if ($userId = $this->_getParam('user_id', 0)) {
            // переопределяем дефолтный профиль индивидуальным
            if (count($profiles = $this->getService('AtProfile')->fetchAllDependence(array('Evaluation', 'User'), array('user_id = ?' => $userId)))) {
                $this->_profile = $this->getService('AtProfile')->getOne($profiles);
                $this->view->setSubHeader($this->_profile->user->current()->getName());
            }
        }

        if (!isset($this->_profile) && ($profile_id = $this->_getParam('profile_id'))) {
            if (count($profiles = $this->getService('AtProfile')->fetchAllDependence(array('Evaluation'), array('profile_id = ?' => $profile_id)))) {
                $this->_profile = $this->getService('AtProfile')->getOne($profiles);
            }
        }
        parent::init();
    }

    // @todo: вынести в  libarary/HM, сделать типовой элемент
    public function indexAction()
    {
        $this->_initMethodsForms(HM_Programm_ProgrammModel::TYPE_ASSESSMENT);
        $this->_viewParamInit();
    }

    public function recruitAction()
    {
        $this->_initMethodsForms(HM_Programm_ProgrammModel::TYPE_RECRUIT);
        $this->_viewParamInit();
        $this->_helper->viewRenderer->setRender('index');
    }

    private function _viewParamInit()
    {
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/fieldset.js'));
        $this->view->forms = $this->_forms;
    }
    private function _initMethodsForms($programmType)
    {
        $forms = array();
        foreach (array_keys(HM_At_Evaluation_EvaluationModel::getMethods()) as $method) {

            $formClass = 'HM_Form_Methods_' . ucfirst($method);
            if (class_exists($formClass)) {
                /**
                 * @var HM_Form $form
                 */
                $form = new $formClass();
                $form->setDefaultsByProfile($this->_profile, $programmType);

                if ($userId = $this->_getParam('user_id', 0)) {
                    $element = $form->getElement('user_id');
                    $element->setValue($userId);
                }

                // добавляется тип программы
                $programmTypeElement = new Zend_Form_Element_Hidden('programm_type', array('value' => $programmType));

                $programmTypeElement->removeDecorator('Label')
                                    ->removeDecorator('HtmlTag');
                $form->addElement($programmTypeElement);

                // тип программы добавляется в параметры некоторых элементов
                $uiSelect = $form->getElement('criteria_test');
                if ( $uiSelect ) {
                    $uiSelectParams =  $uiSelect->getAttribs();
                    $uiSelectParams['jQueryParams']['remoteUrl'] = rtrim($uiSelectParams['jQueryParams']['remoteUrl'], '/') . '/programm_type/' . $programmType;
                    $uiSelect->setAttribs($uiSelectParams);
                }

                $forms[] = $form;
            }
        }
        $this->_forms = $forms;
    }
    public function saveCompetenceAction()
    {
        $this->_ajaxify();

        $form = new HM_Form_Methods_Competence();
        $programmTypeElement = new Zend_Form_Element_Hidden('programm_type', array('value' => 0));

        $programmTypeElement->removeDecorator('Label')
                            ->removeDecorator('HtmlTag');
        $form->addElement($programmTypeElement);
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();
                
                if ($values['user_id'] && empty($this->_profile->user_id)) {
                    $this->getService('AtProfile')->individualize($this->_profile, $values['user_id']);
                }
                
                $values['competences'] = array_filter($values['competences']); // сохраняем только компетенции; кластеры отбрасываем
                if (!empty($this->_profile)) {
                    if ($values['radio_competence']) {
                        if (is_array($values['relation_types']) && count($values['relation_types']) && is_array($values['competences']) && count($values['competences'])) {

                            foreach ($values['relation_types'] as $relationTypeId) {
                                // создаем вид оценки
                                $evaluation = $this->getService('AtEvaluation')->insertEvaluationTypeCompetence($this->_profile->profile_id, $relationTypeId, $values['competences'], $values['programm_type']);
                                // вставляем элементом программы оценки
                                $this->getService('Programm')->assignItem($this->_profile->programm_id, $evaluation->evaluation_type_id, HM_Programm_Event_EventModel::EVENT_TYPE_AT);

                                $this->getService('AtEvaluationMemo')->deleteBy(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id));
                                foreach ($values['memos'] as $memo) {
                                    if (empty($memo)) continue;
                                    $memo = $this->getService('AtEvaluationMemo')->insert(array(
                                        'evaluation_type_id' => $evaluation->evaluation_type_id,
                                        'name' => $memo
                                    ));
                                }

                            }

                            // удаляем те, что не были отмечены
                            if (count($toDel = array_diff(array_keys(HM_At_Evaluation_Method_CompetenceModel::getRelationTypes()), $values['relation_types']))) {
                                foreach ($toDel as $relationTypeId) {
                                    $evaluations = $this->getService('AtEvaluation')->deleteEvaluationTypeCompetence($this->_profile->profile_id, $relationTypeId, $values['programm_type']);
                                    $evaluation = array_shift($evaluations);
                                    $this->getService('Programm')->unassignItem($this->_profile->programm_id, $evaluation, HM_Programm_Event_EventModel::EVENT_TYPE_AT);
                                    $this->getService('AtEvaluationMemo')->deleteBy(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id));
                                }
                            }
                            exit('1');
                        }

                    } elseif (empty($values['radio_competence'])) { // unset the evaluation && program item
                        // удаляем все виды оценки, связанные с 360
                        $evaluations = $this->getService('AtEvaluation')->deleteEvaluationTypeCompetence($this->_profile->profile_id);
                        // удаляем элементы программы
                        if (count($evaluations)) {
                            foreach ($evaluations as $evaluationTypeId) {
                                $this->getService('Programm')->unassignItem($this->_profile->programm_id, $evaluationTypeId, HM_Programm_Event_EventModel::EVENT_TYPE_AT);
                            }
                        }
                        exit('1');
                    }
                }
            }
        }
        exit('0');
    }


    public function saveKpiAction()
    {
        $this->_ajaxify();

        $form = new HM_Form_Methods_Kpi();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();
                
                if ($values['user_id'] && empty($this->_profile->user_id)) {
                    $this->getService('AtProfile')->individualize($this->_profile, $values['user_id']);
                }
                
                $values['criteria_kpi'] = array_filter($values['criteria_kpi']); // сохраняем только компетенции; кластеры отбрасываем
                if (!empty($this->_profile)) {
                    if ($values['radio_kpi']) {
                        if (is_array($values['relation_types']) && count($values['relation_types'])) {

                            foreach ($values['relation_types'] as $relationTypeId) {
                                // создаем вид оценки
                                $evaluation = $this->getService('AtEvaluation')->insertEvaluationTypeKpi($this->_profile->profile_id, $relationTypeId, $values['criteria_kpi']);
                                // вставляем элементом программы оценки
                                $this->getService('Programm')->assignItem($this->_profile->programm_id, $evaluation->evaluation_type_id, HM_Programm_Event_EventModel::EVENT_TYPE_AT);

                                $this->getService('AtEvaluationMemo')->deleteBy(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id));
                                foreach ($values['memos'] as $memo) {
                                    if (empty($memo)) continue;
                                    $memo = $this->getService('AtEvaluationMemo')->insert(array(
                                        'evaluation_type_id' => $evaluation->evaluation_type_id,
                                        'name' => $memo
                                    ));
                                }

                            }

                            // удаляем те, что не были отмечены
                            if (count($toDel = array_diff(array_keys(HM_At_Evaluation_Method_KpiModel::getRelationTypes()), $values['relation_types']))) {
                                foreach ($toDel as $relationTypeId) {
                                    $evaluations = $this->getService('AtEvaluation')->deleteEvaluationTypeKpi($this->_profile->profile_id, $relationTypeId);
                                    $evaluation = array_shift($evaluations);
                                    $this->getService('Programm')->unassignItem($this->_profile->programm_id, $evaluation, HM_Programm_Event_EventModel::EVENT_TYPE_AT);
                                    $this->getService('AtEvaluationMemo')->deleteBy(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id));
                                }
                            }
                            exit('1');
                        }

                    } elseif (empty($values['radio_kpi'])) { // unset the evaluation && program item
                        // удаляем все виды оценки, связанные с 360
                        $evaluations = $this->getService('AtEvaluation')->deleteEvaluationTypeKpi($this->_profile->profile_id);
                        // удаляем элементы программы
                        if (count($evaluations)) {
                            foreach ($evaluations as $evaluationTypeId) {
                                $this->getService('Programm')->unassignItem($this->_profile->programm_id, $evaluationTypeId, HM_Programm_Event_EventModel::EVENT_TYPE_AT);
                            }
                        }
                        exit('1');
                    }
                }
            }
        }
        exit('0');
    }

    public function saveRatingAction()
    {
        $this->_save(HM_At_Evaluation_EvaluationModel::TYPE_RATING);
    }

    public function saveTestAction()
    {
        $this->_save(HM_At_Evaluation_EvaluationModel::TYPE_TEST);
    }

    private function _save($method)
    {
        $this->_ajaxify();

        $formClass = 'HM_Form_Methods_' . ucfirst($method);
        $form = new $formClass();
        $programmTypeElement = new Zend_Form_Element_Hidden('programm_type', array('value' => 0));

        $programmTypeElement->removeDecorator('Label')
                            ->removeDecorator('HtmlTag');
        $form->addElement($programmTypeElement);

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();

                if ($values['user_id'] && empty($this->_profile->user_id)) {
                    $this->getService('AtProfile')->individualize($this->_profile, $values['user_id']);
                }

                $values['criteria_' . $method] = array_filter($values['criteria_' . $method]);
                if (!empty($this->_profile)) {
                    if ($values['radio_' . $method]) {
                        $insertMethod = 'insertEvaluationType' . ucfirst($method);
                        $evaluation = $this->getService('AtEvaluation')->$insertMethod($this->_profile->profile_id, $values['criteria_' . $method], $values['programm_type']);
                        // вставляем элементом программы оценки
                        $this->getService('Programm')->assignItem($this->_profile->programm_id, $evaluation->evaluation_type_id, HM_Programm_Event_EventModel::EVENT_TYPE_AT);
                        exit('1');
                    } elseif (empty($values['radio_' . $method])) { // unset the evaluation && program item
                        $evaluations = $this->getService('AtEvaluation')->deleteEvaluationType($this->_profile->profile_id, $method,  $values['programm_type']);
                        if (count($evaluations)) {
                            foreach ($evaluations as $evaluationTypeId) {
                                $this->getService('Programm')->unassignItem($this->_profile->programm_id, $evaluationTypeId, HM_Programm_Event_EventModel::EVENT_TYPE_AT);
                            }
                        }
                        exit('1');
                    }
                }
            }
        }
        exit('0');
    }

    // вообще-то ajax тут не нужен совсем
    public function criteriaListAction()
    {
        $this->_ajaxify();

        if ($types = $this->_getParam('types', false)) {

            $clusteredCriteria = $this->getService('AtCriterionCluster')->getClustersCriteria(explode('_', $types));
            $nonClusteredCriteria = $this->getService('AtCriterion')->getNonClustered();
            $method = $this->_getParam('method', HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE);

            if (count($this->_profile->evaluations)) {

                // одни и те же компетенции теперь используются в разных методиках
                foreach ($this->_profile->evaluations as $evaluation) {
                	if ($evaluation->method == $method) break;
                }
                $selectedCriteria = $this->getService('AtEvaluationCriterion')->fetchAll(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id))->getList('criterion_id');
            }

            $count = 0;
            foreach($clusteredCriteria as $cluster) {
                if ($count++ > 0) echo "\n";
                $cluster_id = 'cluster_' . $cluster->cluster_id;
                echo sprintf("%s=%s", $cluster_id, $cluster->name);
                foreach ($cluster->criteria as $criterion) {
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
                	$criterion_id = $criterion->criterion_id;
                    if (in_array($criterion->criterion_id, $selectedCriteria)) {
                        $criterion_id .= '+';
                    }
                    echo sprintf("\n%s=- %s", $criterion_id, $criterion->name);
            	}
            }
        }
    }

    public function criteriaTestListAction()
    {
        $this->_ajaxify();
        $programmType = $this->_getParam('programm_type', null);
		$selectedCriteria = array();
        if (count($this->_profile->evaluations)) {
            foreach ($this->_profile->evaluations as $evaluation) {
                $programTypeCheckResult = ($programmType !== null)? ($evaluation->programm_type == $programmType) : true;
                if ($evaluation->method == HM_At_Evaluation_EvaluationModel::TYPE_TEST && $programTypeCheckResult) {
                    $selectedCriteria = Zend_Registry::get('serviceContainer')->getService('AtEvaluationCriterion')->fetchAll(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id))->getList('criterion_id');
                    break;
                }
            }
        }

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
