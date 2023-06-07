<?php
class HM_At_Session_Event_EventTable extends HM_Db_Table
{
    protected $_name    = "at_session_events";
    protected $_primary = "session_event_id";

    protected $_referenceMap = array(
        'Evaluation' => array(
            'columns'       => 'evaluation_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns'    => 'evaluation_type_id',
            'propertyName'  => 'evaluation'
        ),
        'Session' => array( 
            'columns'       => 'session_id',
            'refTableClass' => 'HM_At_Session_SessionTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'session'
        ),
        'Reserve' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'reserve'
        ),
        'SessionEventUser' => array( 
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ),
        'SessionEventRespondent' => array( // не работает.(
            'columns'       => 'respondent_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'respondent'
        ),
        'SessionUser' => array( 
            'columns'       => 'session_user_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns'    => 'session_user_id',
            'propertyName'  => 'sessionUser'
        ),
        'SessionRespondent' => array(
            'columns'       => 'session_respondent_id',
            'refTableClass' => 'HM_At_Session_Respondent_RespondentTable',
            'refColumns'    => 'session_respondent_id',
            'propertyName'  => 'sessionRespondent'
        ),
        'Position' => array(
            'columns'       => 'position_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'soid',
            'propertyName'  => 'position'
        ),
        'EvaluationResult' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Evaluation_Results_ResultsTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'evaluationResults'
        ),
        'EvaluationIndicator' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Evaluation_Results_IndicatorTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'evaluationIndicators'
        ),
        'EvaluationMemoResult' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Evaluation_MemoResult_MemoResultTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'evaluationMemoResults'
        ),
        'Attempt' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Event_Attempt_AttemptTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'attempts'
        ),
        'SessionEventLesson' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Event_Lesson_LessonTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'sessionEventLesson'
        ),
        'SessionPair' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Pair_PairTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'pairs'
        ),
        'SessionPairResult' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Pair_Result_ResultTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'pairResults'
        ),
        'ProgrammEventUser' => array(
            'columns'       => 'programm_event_user_id',
            'refTableClass' => 'HM_Programm_Event_User_UserTable',
            'refColumns'    => 'programm_event_user_id', // ВНИМАНИЕ! нужно еще отфильтровать по type
            'propertyName'  => 'programmEventUser'
        ),
        'CriterionTest' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'criterion_id', // ВНИМАНИЕ! нужно еще отфильтровать по type
            'propertyName'  => 'criterionTest'
        ),
        'CriterionPersonal' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Personal_PersonalTable',
            'refColumns'    => 'criterion_id', // ВНИМАНИЕ! нужно еще отфильтровать по type
            'propertyName'  => 'criterionPersonal'
        ),
        'Quest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id', 
            'propertyName'  => 'quest'
        ),
        'QuestAttempt' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_Quest_Attempt_AttemptTable',
            'refColumns'    => 'context_event_id', // нужно еще отфильтровать по context_type 
            'propertyName'  => 'questAttempts'
        ),
        'StateData' => array(
            'columns'      => 'programm_event_user_id',
            'refTableClass' => 'HM_State_Data_StateDataTable',
            'refColumns'    => 'programm_event_user_id',
            'propertyName'  => 'stateData'
        ),
    );

}