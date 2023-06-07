<?php
class Profile_ReportController extends HM_Controller_Action_Profile
{
    use HM_Controller_Action_Trait_Report;

    public function init()
    {
        parent::init();
        $this->initReport();
    }

    public function indexAction()
    {
        $percentProgress = array();

        $select = $this->getService('Classifier')->getSelect();
        $select->from(
            array('classifiers'),
            array('classifiers.name', 'classifiers.type', 'classifiers_links.item_id')
        )->joinLeft(
            array('classifiers_links'),
            'classifiers.classifier_id=classifiers_links.classifier_id'
        )->where('classifiers_links.item_id=?', $this->_profile->profile_id);

        $specialities = $select->query()->fetchAll();
        foreach ($specialities as $spec) {
            if ($spec['type'] == HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_SPECIALITIES) {
                $specs[] = $spec['name'];
            }
        }

        $univercities = $select->query()->fetchAll();
        foreach ($univercities as $univer) {
            if ($univer['type'] == HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_UNIVERSITIES) {
                $univers[] = $univer['name'];
            }
        }

        if (isset($this->_profile->category) && count($this->_profile->category)) {
            $category = $this->_profile->category->current();
        }
        
		// ВНИМАНИЕ! их может быть очень много и тогда всё зависнет
        $specialities = $this->getService('AtProfile')->getProfileSpecialities($this->_profile->profile_id, HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_SPECIALITIES);
        $univercities = $this->getService('AtProfile')->getProfileSpecialities($this->_profile->profile_id, HM_Classifier_Link_LinkModel::TYPE_PROFILE_EDUCATION_UNIVERSITIES);

//print_r($specialities);exit();
    
        $requirements = $this->getService('AtProfile')->getRequirements4Report($this->_profile->profile_id);

        $criteria = $criteriaTypes = $criteriaSuccessValues = $criteriaClusters = $quests = array();
        $subjects = array();
        
        if (count($collection = $this->getService('Quest')->fetchAll())) {
            $quests = $collection->getList('quest_id', 'name');
        }
        
        if (count($collection = $this->getService('Subject')->fetchAll())) {
            $subjects = $collection->getList('subid', 'name');
        }
        
        if (count($this->_profile->criteriaValues)) {
            foreach ($this->_profile->criteriaValues as $criteriaValue) {
                $criteriaTypes[$criteriaValue->criterion_type][] = $criteriaValue->criterion_id;
            }

//#17387 - склеивались значения компетенций разных типов, оставили только корпоративные
            $corpCriteriaValues = array();
            foreach($this->_profile->criteriaValues as $i=>$c)
                if($c->criterion_type==HM_At_Criterion_CriterionModel::TYPE_CORPORATE)
                {
                    $criteriaSuccessValues[$c->criterion_id] = $c->value_id;
                }
//
        }
        
        if (count($criteriaTypes)) {
            foreach ($criteriaTypes as $type => $criterionIds) {
                switch ($type) {
                    case HM_At_Criterion_CriterionModel::TYPE_PERSONAL:
                        $service = 'AtCriterionPersonal';
                        break;
                    case HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL:
                        $service = 'AtCriterionTest';
                        break;
                    case HM_At_Criterion_CriterionModel::TYPE_CORPORATE:
                        $service = 'AtCriterion';
                        break;
                }
                if ($service && count($collection = $this->getService($service)->fetchAllDependence('Quest',array('criterion_id IN (?)' => $criterionIds), 'name'))) {
                    $criteria[$type] = $collection;
                    if ($type == HM_At_Criterion_CriterionModel::TYPE_CORPORATE) {
                        $clusterIds = $collection->getList('cluster_id');
                        $collection = $this->getService('AtCriterionCluster')->fetchAll(array(
                            'cluster_id IN (?)' => $clusterIds,
                        ));
                        if (count($collection)) {
                            $criteriaClusters = $collection->getList('cluster_id', 'name');
                        }                        
                    }
                }
            }
        }

        if ($this->_profile->base_id) {
            $baseProfile = $this->getService('AtProfile')->findOne($this->_profile->base_id);
        }

        //************************************
        
        $this->view->lists['general'] = array(
            _('Профиль должности') => $this->_profile->name,
//            _('Базовый профиль должности') => $baseProfile
//                ? sprintf('%s <a href="%s">Отменить</a>', $baseProfile->name, $this->view->url(array('controller' => 'list', 'action' => 'unlink', 'profile_id' => $this->_profile->profile_id)))
//                : '',
//             _('Структурное подразделение') => $this->_profile->name,
            _('Категория должности') => $category ? $category->name : '',
            _('Руководящая должность') => $this->_profile->is_manager ? _('Да') : _('Нет'),
        );

        //************************************

        $this->view->lists['requirements-1'] = array(
            _('Пол') => ($val = HM_At_Profile_ProfileModel::getVariant($this->_profile->gender, 'getGenderVariants')) ? $val : $this->view->reportNoValue(),
            _('Возраст') => $this->_getAge($this->_profile->age_min, $this->_profile->age_max),
            _('Наличие ученой степени, звания') => ($val = HM_At_Profile_ProfileModel::getVariant($this->_profile->academic_degree, 'getAcademicDegreeVariants')) ? $val : $this->view->reportNoValue(),
            _('Опыт работы в данной позиции') => strlen(trim($this->_profile->experience)) ? $this->_profile->experience : $this->view->reportNoValue(),
        );
        
        $this->view->lists['requirements-2'] = array(
            _('Основное образование') => ($val = HM_At_Profile_ProfileModel::getVariant($this->_profile->education, 'getMainEducationVariants')) ? $val : $this->view->reportNoValue(),
            _('Дополнительное образование, сертификаты, лицензии') => strlen(trim($this->_profile->additional_education)) ? $this->_profile->additional_education : $this->view->reportNoValue(),
            _('Другое') => strlen(trim($this->_profile->comments)) ? $this->_profile->comments : $this->view->reportNoValue(),
        );

        $percentProgress[_('Формальные требования')] = $this->_getRequirementsPercent($this->view->lists['requirements-1'], $this->view->lists['requirements-2']);

        //************************************
                
/*
        $percentProgress[_('Требования по профстнадартам')] = $this->_getSkillsPercent($skillsInner, $skillsOuter); // вызываем функцию до array_shift
        while (count($skillsInner) || count($skillsOuter)) {
            $table[] = array(
                array($skillsOuter),        
                array_shift($skillsInner),        
            );
        }
*/
        $table = array(array_merge(array(_('Профстандарт'),_('Обобщенная трудовая функция')), HM_At_Standard_Function_FunctionModel::getTypes()));
        foreach($requirements as $requirement) {
            $table[] = $requirement;
        }
        $this->view->tables['skills'] = $table;

        $this->view->footnote(_('Используются при публикации вакансии на HeadHunter'), 1);
        //************************************
        
        $table = array($head = array(
            _('Квалификация'),
            _('Оценивается посредством теста'),
//            _('Развивается посредством курса'),
        ));
        if (isset($criteria[HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL]) && count($criteria[HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL])) {
            foreach ($criteria[HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL] as $criterion) {
                $table[] = array(
                    $criterion->name,
                    isset($quests[$criterion->quest_id]) ? $quests[$criterion->quest_id] : '',
//                    isset($subjects[$criterion->subject_id]) ? $subjects[$criterion->subject_id] : '',
                );
            }
        }

        $percentProgress[_('Дополнительные знания и навыки')] = $this->_getCriteriaPercent($criteria[HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL]);
        $this->view->tables['criteria-test'] = $table;
        
        //************************************
                
        $this->view->lists['requirements-misc'] = array(
            _('Необходимость командировок') => ($val = HM_At_Profile_ProfileModel::getVariant($this->_profile->trips, 'getTripsVariants')) ? $val : $this->view->reportNoValue(),
            _('Длительность  командировок, дн.') => strlen(trim($this->_profile->trips_duration)) ? $this->_profile->trips_duration : $this->view->reportNoValue(),            
            _('Статус мобильности') => ($val = HM_At_Profile_ProfileModel::getVariant($this->_profile->mobility, 'getMobilityVariants')) ? $val : $this->view->reportNoValue(),
//             _('Подчинение') => '',
        );

        //************************************

        $isAtCompetenceRemoved = (bool)$this->getService('Extension')->getRemover("HM_Extension_Remover_AtCompetenceRemover");

        $table = array(array(
            _('Кластер'),        
            _('Компетенция'),        
            _('Описание'),
            $isAtCompetenceRemoved ? null : _('Уровень успешности'),
        ));
        
        if (count($criteria[HM_At_Criterion_CriterionModel::TYPE_CORPORATE])) {
            $scaleId = $this->getService('Option')->getOption('competenceScaleId'); // шкала оценки компетенций; в подборе может использоваться другая шкала
            foreach($criteria[HM_At_Criterion_CriterionModel::TYPE_CORPORATE] as $criterion) {
                $table[] = array(
                    isset($criteriaClusters[$criterion->cluster_id]) ? $criteriaClusters[$criterion->cluster_id] : '',
                    $criterion->name,
                    $criterion->description,
                    $isAtCompetenceRemoved ? null : isset($criteriaSuccessValues[$criterion->criterion_id]) ? HM_Scale_Converter::getInstance()->id2value($criteriaSuccessValues[$criterion->criterion_id], $scaleId) : '',
                );
            }
        }
        $percentProgress[_('Компетенции')] = $this->_getCorporateCriteriasPercent($criteria, $criteriaSuccessValues);
        $this->view->tables['criteria'] = $table;
        
        //************************************
                
        $table = array(array(
            _('Характеристика'),        
            _('Описание'),        
//            _('Оценивается посредством психологического опроса'),
        ));
        if (isset($criteria[HM_At_Criterion_CriterionModel::TYPE_PERSONAL]) && count($criteria[HM_At_Criterion_CriterionModel::TYPE_PERSONAL])) {
            foreach ($criteria[HM_At_Criterion_CriterionModel::TYPE_PERSONAL] as $criterion) {
                $table[] = array(
                    $criterion->name,
                    $criterion->description,
//                    isset($quests[$criterion->quest_id]) ? $quests[$criterion->quest_id] : '',
                );
            }
        }
        $percentProgress[_('Личностные характеристики')] = $this->_getCriteriaPercent($criteria[HM_At_Criterion_CriterionModel::TYPE_PERSONAL]);
        $this->view->tables['criteria-personal'] = $table;

        //************************************

        $profileId = $this->_getParam('profile_id', 0);
        $percentProgress[_('Программы подбора, начального обучения и регулярной оценки')] = $this->_getMethodsPercent($profileId);

        $this->view->percentProgress = $percentProgress;
        $this->view->totalProgressStatus = $this->_countTotalProgressStatus($percentProgress);
        $this->getService('AtProfile')->update(array('profile_id'=>$profileId, 'progress'=>$this->view->totalProgressStatus)); //#17407

        $this->view->print = $print = $this->_getParam('print', 0);
        $this->view->editable = !$print && $this->getService('Acl')->isCurrentAllowed('mca:profile:list:edit');

        $this->view->doubleTime = $this->_profile->double_time;
        $this->view->isDean = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN);

        $removers = [
            'At',
            'AtCompetence',
            'AtKpi',
            'AtTest',
            'Recruit',
        ];

        $this->view->isRemoved = [];
        foreach ($removers as $remover) {
            $this->view->isRemoved[$remover] = (bool)$this->getService('Extension')->getRemover("HM_Extension_Remover_{$remover}Remover");
        }
    }
    
    public function competenceAction()
    {
        // навигация вручную
        $this->view->setCurrentPage('mca:profile:criterion:corporate');
        $this->view->setSubHeader(_('Компетенции: профиль успешности'));

        $methods = $relation_types = array();
        $scaleId = $this->getService('Option')->getOption('competenceScaleId'); // шкала оценки компетенций; в подборе может использоваться другая шкала

        $this->view->scaleMaxValue = Zend_Registry::get('serviceContainer')->getService('Scale')->getMaxValue($scaleId);
        $this->view->scaleMinValue = 0;

        $criteria = $this->getService('AtCriterion')->fetchAll(null, array('cluster_id', 'name'));
        $criteriaCache = $criteria->getList('criterion_id', 'name');

        $this->view->texts['general'] = $this->_profile->requirements;


        $this->view->charts['competence']['head'] = array('title' => _('Компетенция'));
        $this->view->charts['competence']['values'] = array('title' => _('Плановое значение'));

        $data = array();
        foreach ($this->_profile->criteriaValues as $criterionValue) {
            
            if ($criterionValue->criterion_type != HM_At_Criterion_CriterionModel::TYPE_CORPORATE) continue;

            $value = HM_Scale_Converter::getInstance()->id2value($criterionValue->value_id, $scaleId);
            $value = ((int) $value > 0) ? $value : 0;

            $data[] = array('title' => $criteriaCache[$criterionValue->criterion_id], 'profile' => $value);

            $this->view->charts['competence']['head'][$criterionValue->criterion_id] = $criteriaCache[$criterionValue->criterion_id];
            $this->view->charts['competence']['values'][$criterionValue->criterion_id] = $value;

            if ($this->view->scaleMinValue > $value) $this->view->scaleMinValue = $value;
        }

        $graphs = array('profile' => array('legend' => 'LEGEND', 'color' => "#DD3377"));

        $this->view->charts['competence']['data'] = $data;
        $this->view->charts['competence']['graphs'] = $graphs;
        $this->view->methods = $methods;
    }
    
    public function _getAge($min, $max)
    {
        if ($min && $max) {
            return sprintf('не моложе %s, не старше %s', $min, $max);
        } elseif ($min) {
            return sprintf('не моложе %s', $min);
        } elseif ($max) {
            return sprintf('не старше %s', $max);
        }
        return $this->view->reportNoValue();
    }

    public function _getGender($gender)
    {
        if ($gender) {
            return ($gender == 1) ? _('Мужской') : _('Женский');
        }
        return $this->view->reportNoValue();
    }

    protected function _getRequirementsPercent($rightBlock, $leftBlock){

        $filledValues = 0;
        $totalValues = 0;
        foreach($rightBlock as $value){
            if($value != $this->view->reportNoValue()) { $filledValues++; }
            $totalValues++;
        }
        foreach($leftBlock as $value){
            if($value != $this->view->reportNoValue()) { $filledValues++; }
            $totalValues++;
        }

        if ($filledValues) {
            return round($filledValues / $totalValues * 100);
        } else {
            return 0;
        }
    }

    protected function _getSkillsPercent($skillsInner, $skillsOuter){
        $result = 0;
        if(count($skillsInner)) $result += 50;
        if(count($skillsOuter)) $result += 50;
        return $result;
    }

    protected function _getCriteriaPercent($criteria)
    {
        $result = 0;
        $criteriaCounter = 0;
        $criteriaQuestions = 0;
        
        if ($criteria && count($criteria)) {
            $result += 50;
            $questIds = $criteria->getList('quest_id');
            $quests = $this->getService('Quest')->fetchAllDependence('QuestionQuest', array('quest_id IN (?)' => $questIds));
            
            if (count($quests)) {
                $quests = $quests->asArrayOfObjects();
                foreach($criteria as $criteria){
                    $criteriaCounter++;
                    if (count($quests[$criteria->quest_id]->questionQuest)) $criteriaQuestions++;
}
            }
        
            if ($criteriaQuestions){
                $result += round($criteriaQuestions / $criteriaCounter * 50);
            }
        }
        return $result;
    }

    protected function _getCorporateCriteriasPercent($criteria, $criteriaSuccessValues){
        $result = 0;
        $criteriaCounter = 0;
        $criteriaSuccessValueCounter = 0;
        if (count($criteria[HM_At_Criterion_CriterionModel::TYPE_CORPORATE])) {
            $result += 50;
            foreach($criteria[HM_At_Criterion_CriterionModel::TYPE_CORPORATE] as $criterion) {
                    $criteriaCounter++;
                    if(isset($criteriaSuccessValues[$criterion->criterion_id])) $criteriaSuccessValueCounter++;
            }
            if($criteriaSuccessValueCounter){
                $result += round($criteriaSuccessValueCounter / $criteriaCounter * 50);
            }
        }
        return $result;
    }

    protected function _getMethodsPercent($profileId)
    {
        $divider = 4; // всего 3 типа программ могут быть + нач. обучение
        $collection = $this->getService('AtEvaluation')->fetchAll(array(
            'profile_id = ?' => $profileId,
        ));
        $result = array();
        if (count($collection)) {
            foreach ($collection as $evaluation) {
                $result[$evaluation->programm_type] = 100/$divider;
            }
        }
        
        $program = $this->getService('Programm')->fetchAllDependence('Event', array(
            'item_id = ?' => $profileId,
            'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ELEARNING
        ))->current();

        if ($program && count($program->events)) {
            $result['prim'] = 100/$divider;
        }

        return ceil(array_sum($result));
    }

    protected function _countTotalProgressStatus($percentProgress){
        $result = 0;
        $progressBarPercentsSum = 0;
        if(is_array($percentProgress)){
            foreach($percentProgress as $progressBarPercent){
                $progressBarPercentsSum += $progressBarPercent;
            }
            if ($progressBarPercentsSum) {
                $result = round($progressBarPercentsSum / count($percentProgress));
            }
        }
        return $result;
    }
}
