<?php
/**
 * Методика оценки по компетенциям (360 град.)
 *
 */
abstract class HM_At_Evaluation_Method_CompetenceModel extends HM_At_Evaluation_EvaluationModel
{
    const RELATION_WEIGHT_SELF = 0.2; // в сумме должно быть 1
    const RELATION_WEIGHT_PARENT = 0.2;
    const RELATION_WEIGHT_PARENT_FUNCTIONAL = 0.2;
    const RELATION_WEIGHT_SIBLINGS = 0.2;
    const RELATION_WEIGHT_CHILDREN = 0.2;

    const THRESHOLD_TOP_COMPETENCES = 2; // порог попадания в top пользователей в общем отчете
    const THRESHOLD_BOTTOM_COMPETENCES = 1; // в bottom
    const THRESHOLD_HIDDEN_DELTA = 0.5;

    const THRESHOLD_HIDE_CHILDREN = 2; // если подчиненных меньше, чем указано здесь - то в отчете они попадают в категорию "коллеги" - для конспирации

    const ANALYTICS_GRAPH_USER = 'user';
    const ANALYTICS_GRAPH_PROFILE = 'profile';
    const ANALYTICS_GRAPH_SESSIONS = 'sessions';
    const ANALYTICS_GRAPH_POSITION = 'position';
	
	const MATRIX_TOP_ROW = 3;
    const MATRIX_MIDDLE_ROW = 2;
    const MATRIX_BOTTOM_ROW = 1;

    const MATRIX_LEFT_COLUMN = 0;
    const MATRIX_RIGHT_COLUMN = 1;

    const MATRIX_BLOCK_LEADERS = 6; // Лидеры
    const MATRIX_BLOCK_EXPERTS = 5; // Эксперты
    const MATRIX_BLOCK_PERSPECTIVE = 4; // Перспективные
    const MATRIX_BLOCK_DILIGENTS = 3; // Старательные
    const MATRIX_BLOCK_ANALYSIS = 2; // Группа разбора
    const MATRIX_BLOCK_RISK = 1; // Группа риска

    static public function getMethodId()
    {
        return HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE;
    }

    static public function getMethodName()
    {
        return _('Оценка по компетенциям');
    }

    static public function getCriterionTypes()
    {
        return array(
            HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
            HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
            HM_At_Criterion_CriterionModel::TYPE_PERSONAL,
        );
    }

    static public function getRelationTypeTitle($relationTypeId, $short = false)
    {
        $types = self::getRelationTypes();
        return isset($types[$relationTypeId]) ? $types[$relationTypeId] : false;
    }

    static public function getRelationWeight($relationTypeId)
    {
        $weights = self::getRelationWeights();
        return isset($weights[$relationTypeId]) ? $weights[$relationTypeId] : false;
    }

    static public function getRelationWeights()
    {
        return array(
            self::RELATION_TYPE_SELF => self::RELATION_WEIGHT_SELF,
            self::RELATION_TYPE_PARENT => self::RELATION_WEIGHT_PARENT,
            self::RELATION_TYPE_PARENT_FUNCTIONAL => self::RELATION_WEIGHT_PARENT_FUNCTIONAL,
            self::RELATION_TYPE_SIBLINGS => self::RELATION_WEIGHT_SIBLINGS,
            self::RELATION_TYPE_CHILDREN => self::RELATION_WEIGHT_CHILDREN,
        );
    }

    static public function getRelationTypeTitleShort($relationTypeId)
    {
        $types = self::getRelationTypesShort();
        return isset($types[$relationTypeId]) ? $types[$relationTypeId] : false;
    }

    static public function getRelationTypes()
    {
        return array(
            self::RELATION_TYPE_SELF => _('Компетенции - самооценка'),
            self::RELATION_TYPE_PARENT => _('Компетенции - оценка руководителем'),
//            self::RELATION_TYPE_PARENT_FUNCTIONAL => _('Компетенции - оценка функциональным руководителем'),
            self::RELATION_TYPE_PARENT_RESERVE => _('Компетенции - оценка куратором'),
            self::RELATION_TYPE_CHILDREN => _('Компетенции - оценка подчиненными'), // порядок не стоит менять
            self::RELATION_TYPE_SIBLINGS => _('Компетенции - оценка коллегами'),
            self::RELATION_TYPE_RECRUITER => _('Компетенции - оценка менеджером'),
//            self::RELATION_TYPE_CLIENTS => _('Компетенции - оценка клиентами'),
        );
    }
    
    static function isCustomRespondentsEnabled($relationType)
    {
        return in_array($relationType, array(
            self::RELATION_TYPE_PARENT,
            self::RELATION_TYPE_PARENT_FUNCTIONAL,
            self::RELATION_TYPE_CHILDREN,
            self::RELATION_TYPE_SIBLINGS,
            self::RELATION_TYPE_CLIENTS,
        ));
    }

    static public function getRelationTypeColors()
    {
        return array(
            self::RELATION_TYPE_SELF => '#C24E5F',
            self::RELATION_TYPE_PARENT => '#CF7725',
            self::RELATION_TYPE_SIBLINGS => '#D4B922',
            self::RELATION_TYPE_CHILDREN => '#949E08',
            self::RELATION_TYPE_RECRUITER => '#949E08',
            self::RELATION_TYPE_OTHERS => '#4B8C3E',
            self::RELATION_TYPE_ALL => '#003F7E',
            self::RELATION_TYPE_PARENT_FUNCTIONAL => '#4E69C2',
            self::RELATION_TYPE_PARENT_RESERVE => '#4EC2A8',
            self::RELATION_TYPE_CLIENTS => '#AE4EC2',
        );
    }

    // @todo: подобрать уникальные цвета
    static public function getAnalyticsColors()
    {
        $relationTypeColors = self::getRelationTypeColors();
        return array(
            self::ANALYTICS_GRAPH_USER => $relationTypeColors[self::RELATION_TYPE_ALL],
            self::ANALYTICS_GRAPH_PROFILE => '#C759D2',
            self::ANALYTICS_GRAPH_SESSIONS => '#4B8C3E',
            self::ANALYTICS_GRAPH_POSITION => '#C24E5F',
        );
    }

    public function getDefaults($user)
    {
        if (!is_a($user, 'HM_user_UserModel')) return false;
        return array(
            'name' => sprintf($msg = _('Оценка компетенций пользователя %s'), $user->getName()),
        );
    }
    
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
            case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }
}