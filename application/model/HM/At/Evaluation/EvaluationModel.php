<?php

class HM_At_Evaluation_EvaluationModel extends HM_Model_Abstract {

    // предмет оценки - что оценивают
    // не путать с учебными курсами els 
    // на всякий случай совпадает по значениям с константами в HM_At_Criterion_CriterionModel
    const SUBJECT_OTHER = 0;
    const SUBJECT_COMPETENCE = 1;
    const SUBJECT_QUALIFICATION = 2;
    const SUBJECT_PERSONAL_CHARACTERISTIC = 3;
    const SUBJECT_KPI = 4;
    // метод оценки - как оценивают
    const TYPE_COMPETENCE = 'competence';
    const TYPE_KPI = 'kpi';
    const TYPE_RATING = 'rating'; // парные сравнения
    const TYPE_TEST = 'test';
    const TYPE_PSYCHO = 'psycho';
    const TYPE_FORM = 'form';
    const TYPE_AUDIT = 'audit';
    const TYPE_FIELD = 'field';
    const TYPE_FINALIZE = 'finalize';
    
    const RELATION_TYPE_SELF = 90; // самооценка
    const RELATION_TYPE_PARENT = 180; // руководитель
    const RELATION_TYPE_SIBLINGS = 270; // коллеги
    const RELATION_TYPE_CHILDREN = 360; // подчиненные
    const RELATION_TYPE_RECRUITER = 450; // рекрутер
    const RELATION_TYPE_HR = 540; // HR
    const RELATION_TYPE_PARENT_RESERVE = 630; // руководитель подразделения кадрового резерва
    const RELATION_TYPE_PARENT_FUNCTIONAL = 720; // функциональный руководитель
    const RELATION_TYPE_CLIENTS = 810; // клиент
    const RELATION_TYPE_OTHERS = -90; // все, кроме самооценки
    const RELATION_TYPE_ALL = -180; // все, включая самооценку
//    const RELATION_TYPE_PARENT_LINEAR = 45; // линейный руководитель // DEPRECATED!

    const CRITERION_VALUE_PROFILE = 0; // план
    const CRITERION_VALUE_SESSION_USER = 1; // факт 

    protected $_primaryName = 'evaluation_type_id';

    public function getServiceName() 
    {
        return 'AtEvaluation';
    }

    static public function getSubjectTitle($subjectId) 
    {
        $subjects = self::getSubjects();
        return isset($subjects[$subjectId]) ? $subjects[$subjectId] : false;
    }

    static public function getMethodTitle($methodId, $relationTypeId = false) 
    {
        $methods = self::getMethods(false);
        $title = isset($methods[$methodId]) ? $methods[$methodId] : false;
        if (($methodId == self::TYPE_COMPETENCE) && $relationTypeId) {
            $title .= ' - ' . HM_At_Evaluation_Method_CompetenceModel::getRelationTypeTitleShort($relationTypeId);
        }
        return $title;
    }

    static public function getRelationTitle($relationTypeId) 
    {
        $methods = self::getMethods();
        $title = isset($methods[$methodId]) ? $methods[$methodId] : false;
        if (($methodId == self::TYPE_COMPETENCE) && $relationTypeId) {
            $title .= ' - ' . HM_At_Evaluation_Method_CompetenceModel::getRelationTypeTitleShort($relationTypeId);
        }
        return $title;
    }

    /**
     * Что в принципе можно оценивать
     */
    static public function getSubjects() 
    {
        return array(
            self::SUBJECT_COMPETENCE => _('Оценка компетенций'),
            self::SUBJECT_QUALIFICATION => _('Оценка квалификаций'),
            self::SUBJECT_PERSONAL_CHARACTERISTIC => _('Оценка личностных характеристик'),
            self::SUBJECT_KPI => _('Оценка эффективности (KPI)'),
            self::SUBJECT_OTHER => _('Произвольные опросы'),
        );
    }

    /**
     * Используемые виды оценки
     */
    static public function getMethods($filter = true)
    {
        $methods = array(
            self::TYPE_COMPETENCE => _('Экспертная оценка компетенций'),
            self::TYPE_KPI => _('Оценка выполнения задач'),
            self::TYPE_RATING => _('Парные сравнения'),
            self::TYPE_TEST => _('Профессиональное тестирование'),
            self::TYPE_PSYCHO => _('Психологическое тестирование'),
            self::TYPE_FORM => _('Произвольная оценочная форма'),
            self::TYPE_FINALIZE => _('Итоговая оценочная форма'),
        );

        if ($filter) {
            $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_EVALUATION_METHODS);
            Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $methods);

            return $event->getReturnValue();
        }

        return $methods;
    }

    static function getSubjectMethod($subject) 
    {
        $subjectMethods = self::getSubjectsMethods();
        if (isset($subjectMethods[$subject])) {
            return $subjectMethods[$subject];
        }
        return false;
    }

    static function getMethodSubject($method) 
    {
        $subjectMethods = self::getSubjectsMethods();
        foreach ($subjectMethods as $subject => $methods) {
            if (in_array($method, $methods)) {
                return $subject;
            }
        }
        return false;
    }

    /**
     * Какие критерии какими методами оцениваются  
     */
    static function getSubjectsMethods() 
    {
        $subjectsMethods = array(
            self::SUBJECT_COMPETENCE => array(
                self::TYPE_COMPETENCE,
                self::TYPE_RATING
            ),
            self::SUBJECT_KPI => array(
                self::TYPE_KPI,
            ),
            self::SUBJECT_QUALIFICATION => array(
                self::TYPE_TEST,
            ),
            self::SUBJECT_PERSONAL_CHARACTERISTIC => array(
                self::TYPE_PSYCHO,
            ),
            self::SUBJECT_OTHER => array(
                self::TYPE_FORM,
            ),
        );

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_SUBJECT_EVALUATION_METHODS);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $subjectsMethods);

        return $event->getReturnValue();

    }

    static public function factory($data, $default = 'HM_At_Evaluation_EvaluationModel') 
    {

        if (isset($data['method'])) {
            switch ($data['method']) {
                case self::TYPE_COMPETENCE:

                    switch ($data['relation_type']) {
                        case HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Competence_SelfModel');
                            break;
                        case HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Competence_ParentModel');
                            break;
                        case HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Competence_SiblingsModel');
                            break;
                        case HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Competence_ChildrenModel');
                            break;
                        case HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_RECRUITER:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Competence_RecruiterModel');
                            break;
                        case HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_RESERVE:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Competence_ParentreserveModel');
                            break;
                        case HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_FUNCTIONAL:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Competence_ParentfunctionalModel');
                            break;
                        case HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CLIENTS:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Competence_ClientModel');
                            break;
                        default:
                            // вообще такого быть не должно; 
                            //return parent::factory($data, 'HM_At_Evaluation_Method_CompetenceModel');
                            return false;
                    }
                    break;
                case self::TYPE_KPI:
                    switch ($data['relation_type']) {
                        case HM_At_Evaluation_Method_KpiModel::RELATION_TYPE_SELF:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Kpi_SelfModel');
                            break;
                        case HM_At_Evaluation_Method_KpiModel::RELATION_TYPE_PARENT:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Kpi_ParentModel');
                            break;
                        case HM_At_Evaluation_Method_KpiModel::RELATION_TYPE_PARENT_RESERVE:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Kpi_ParentreserveModel');
                            break;
                        default:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Kpi_ParentModel');
                    }
                    break;
                case self::TYPE_RATING:
                    return parent::factory($data, 'HM_At_Evaluation_Method_Rating_ParentModel');
                    break;
//                 case self::TYPE_AUDIT:
//                     return parent::factory($data, 'HM_At_Evaluation_Method_AuditModel');
//                     break;
//                 case self::TYPE_FIELD:
//                     return parent::factory($data, 'HM_At_Evaluation_Method_FieldModel');
//                     break;
                case self::TYPE_TEST:
                    return parent::factory($data, 'HM_At_Evaluation_Method_Test_SelfModel');
                    break;
                case self::TYPE_PSYCHO:
                    return parent::factory($data, 'HM_At_Evaluation_Method_Psycho_SelfModel');
                    break;
                case self::TYPE_FORM:
                    switch ($data['relation_type']) {
                        case HM_At_Evaluation_Method_KpiModel::RELATION_TYPE_SELF:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Form_SelfModel');
                            break;
                        default:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Form_RecruiterModel');
                    }
                    break;
                case self::TYPE_FINALIZE:
                    switch ($data['programm_type']) {
                        case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Form_Finalize_RecruitModel');
                            break;
                        case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Form_Finalize_AdaptingModel');
                            break;
                        case HM_Programm_ProgrammModel::TYPE_RESERVE:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Form_Finalize_ReserveModel');
                            break;
                        default:
                            return parent::factory($data, 'HM_At_Evaluation_Method_Form_FinalizeModel');
                    }                    
                    break;
            }
            return parent::factory($data, $default);
        }
    }

    static public function getRelationTypesShort() 
    {
        return array(
            self::RELATION_TYPE_OTHERS => _('Все без самооценки'),
            self::RELATION_TYPE_SELF => _('Самооценка'),
            self::RELATION_TYPE_PARENT => _('Руководитель'),
            self::RELATION_TYPE_SIBLINGS => _('Коллеги'),
            self::RELATION_TYPE_CHILDREN => _('Подчиненные'),
            self::RELATION_TYPE_RECRUITER => _('Менеджер по персоналу'),
            self::RELATION_TYPE_PARENT_RESERVE => _('Куратор кадрового резерва'),
//            self::RELATION_TYPE_PARENT_LINEAR => _('Линейный руководитель'),
            self::RELATION_TYPE_PARENT_FUNCTIONAL => _('Функц.руководитель'),
            self::RELATION_TYPE_CLIENTS => _('Клиенты'),
        );
    }

    static public function getRelationTypeTitle($relationTypeId, $short = false)
    {
        $types = $short ? self::getRelationTypesShort() : self::getRelationTypes();
        return isset($types[$relationTypeId]) ? $types[$relationTypeId] : false;
    }

    // более подробные названия определны в моделях методик
    static public function getRelationTypes() 
    {
        return array(
            self::RELATION_TYPE_SELF => _('Самооценка'),
            self::RELATION_TYPE_PARENT => _('Оценка руководителем'),
            self::RELATION_TYPE_CHILDREN => _('Оценка подчиненными'),
            self::RELATION_TYPE_SIBLINGS => _('Оценка коллегами'),
            self::RELATION_TYPE_RECRUITER => _('Оценка менеджером по подбору'),
            self::RELATION_TYPE_PARENT_RESERVE => _('Оценка куратором кадрового резерва'),
//            self::RELATION_TYPE_PARENT_FUNCTIONAL => _('Оценка функциональным руководителем'),
//            self::RELATION_TYPE_CLIENTS => _('Оценка клиентами'),
        );
    }

    static public function getRelationTypeAlias($relationTypeId) 
    {
        $aliases = array(
            self::RELATION_TYPE_SELF => 'self',
            self::RELATION_TYPE_PARENT => 'parent',
            self::RELATION_TYPE_SIBLINGS => 'siblings',
            self::RELATION_TYPE_CHILDREN => 'children',
            self::RELATION_TYPE_RECRUITER => 'recruiter',
            self::RELATION_TYPE_PARENT_RESERVE => 'parentreserve',
            self::RELATION_TYPE_PARENT_FUNCTIONAL => 'parentfunctional',
            self::RELATION_TYPE_CLIENTS => 'client',
        );
        return $aliases[$relationTypeId];
    }

    static public function getRelationTypeAliasFromSubmethod($key) 
    {
        list(, $relationTypeId) = explode('_', $key);
        return self::getRelationTypeAlias($relationTypeId);
    }

    public function isAllowCustomRespondent() {
        false;
    }

    public function getRespondentsCustom($position)
    {
        $return = array();
        $serialized = '';
        if ($position->mid && count($userRelation = Zend_Registry::get('serviceContainer')->getService('AtRelation')->fetchAll(array(
                'user_id = ?' => $position->mid,
                'relation_type = ?' => ($this->method == HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE) ? $this->relation_type : HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT,
            )))) {
            // считаем что для всех остальных методик кроме 360 оценщиком является руководитель
            // если это не так - надо выносить из абстрактного класса
            $serialized = $userRelation->current()->respondents;
        }

        $service = Zend_Registry::get('serviceContainer')->getService('Orgstructure');
        $respondentIds = unserialize($serialized);
        if (is_array($respondentIds) && count($respondentIds)) {
            foreach ($respondentIds as $respondentId) {
                $position = $service->getOne($service->fetchAll(array('mid = ?' => $respondentId)));
                $return[] = $position ?  : $service->getDummyPosition($respondentId);
            }
        }

        return $return;
    }

    // проверка при создании оц.сессии
    // нужно переопределить в классе методики, если в каких-то случаях её нельзя назначить при создании оц.сессии
    public function isValid() 
    {
        return true;
    }

    public function getDefaults($user) 
    {
        return array(
            'name' => sprintf(_('Оценка пользователя %s'), $user->getName())
        );
    }

    public function isMultiEventEvaluation() 
    {
        return false;
    }

    public function isMultiRespondentEvents() 
    {
        return false;
    }

    public function isMultiUserEvents() 
    {
        return false;
    }

    public function isAvailableForProgramm($programmType) 
    {
        return false;
    }

    // метод нужен для универсального построения programm
    // в общем случае это всевозможные relation_type'ы
    // исключение пока составляет только TYPE_FORM
    static public function getSubMethods($methodClass = false)
    {
        $return = array();
        if ($methodClass) {
            $relationTypes = call_user_func(array($methodClass, 'getRelationTypes'));
            foreach ($relationTypes as $key => $value) {
                $key = sprintf('%s_%s', call_user_func(array($methodClass, 'getMethodId')), $key);
                $return[$key] = $value;
            }
        }
        return $return;
    }

    static public function getMethodColors() 
    {
        return array(
            self::TYPE_COMPETENCE => 'red',
            self::TYPE_KPI => 'orange',
            self::TYPE_RATING => 'yellow',
            self::TYPE_TEST => 'green',
            self::TYPE_PSYCHO => 'lightblue',
            self::TYPE_FORM => 'blue',
                // фазан,
        );
    }

    static public function getRelationTypeColors() 
    {
        return array(
            self::RELATION_TYPE_SELF => '#C24E5F',
            self::RELATION_TYPE_PARENT => '#CF7725',
            self::RELATION_TYPE_SIBLINGS => '#D4B922',
            self::RELATION_TYPE_CHILDREN => '#949E08',
            self::RELATION_TYPE_RECRUITER => '#C759D2',
            self::RELATION_TYPE_PARENT_RESERVE => '#C7FFD2',
            self::RELATION_TYPE_OTHERS => '#4B8C3E',
            self::RELATION_TYPE_ALL => '#003F7E',
//            self::RELATION_TYPE_PARENT_LINEAR => '#00FF00',
            self::RELATION_TYPE_PARENT_FUNCTIONAL => '#4E69C2',
        );
    }

    static public function getPlanFactColors() 
    {
        return array(
            self::CRITERION_VALUE_PROFILE => '#C24E5F',
            self::CRITERION_VALUE_SESSION_USER => '#4B8C3E',
        );
    }
    
    static public function parseSubmethod($submethod, $part = 'method') 
    {
        list($method, $relationType) = explode('_', $submethod);
        return $$part;
    }
    
    // переводим или нет процесс сразу после заполнения первой формы
    // true - переводим только после заполнения _всех_ форм
    public function isFullCompletionRequired()
    {
        return true;
    }     
    
    // переводим или нет процесс на след.этап сразу после прохождения
    // false - вручную переведёт специалист
    public function isAutoPassing()
    {
        return true;
    }

    // показываем или нет все формы, относящиеся к участнику 
    // false - показываем только те формы, в которых он респондент
    public function isOtherRespondentsEventsVisible()
    {
        return false;
    }

    // есть ли дефолтный relation_type, зависящий только от типа программы
    // если нет - то при настройке программы можно выбрать разные realtion_type (90. 180,..)
    public function getDefaultRelationType($programmType = false)
    {
        return false;
    }
}