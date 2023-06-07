<?php
class Poll_PageController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();

        $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('save-answer', 'json')->initContext('json');
    }
    public function getQuestionAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $linksList = $this->_getParam('links', '');
        $userId    = $this->getService('User')->getCurrentUserId();

        $linksIds  = explode(',', $linksList);

        array_walk($linksIds, 'intval');

        if ( !count($linksIds) ) {
            echo _('Ошибка получения данных.');
            return;
        }

        $pollWhere = $this->quoteInto('link_id IN (?)', $linksIds);
        $pollIDs   = $this->getService('PollLink')->fetchAll($pollWhere)->getList('link_id', 'quiz_id');

        $pollWhere = $this->quoteInto('quiz_id IN (?)', $pollIDs);
        $polls     = $this->getService('Poll')->fetchAll($pollWhere);

        if ( !count($polls) ) {
            echo _('Опросы не найдены.');
            return;
        }

        //$currentPoll = $this->getOne($polls);


        // Получить список ИД вопросов для указанных опросов
        $pollsQuestionIds = array();
        $questionPollLink = array();
        foreach ($polls as $poll) {
            $qTemp = explode(HM_Poll_PollModel::QUESTION_SEPARATOR, $poll->data);
            $pollsQuestionIds = array_merge($pollsQuestionIds, $qTemp);
            foreach ($qTemp as $q) {
                $questionPollLink[$q] = $poll;
            }
        }
        $pollsQuestionIds = array_unique($pollsQuestionIds);

        // Получить список вопросов из quizzes_results на которые получены ответы от текущего пользователя (по link_id и user_id)
        $resultsWhere = $this->quoteInto(
            array('link_id IN (?)', ' AND user_id = ?'),
            array($linksIds, $userId)
        );
        $userResults     = $this->getService('PollResult')->fetchAll($resultsWhere);
        $doneQuestionIDs = array_unique($userResults->getList('question_id'));

        // Из списка ИД вопросов выбрать 1 на который не отвечал, получить список вариантов ответов из quizzes_answers и отобразить с учетом типа вопроса
        $needQuestionIds   = array_diff($pollsQuestionIds, $doneQuestionIDs);

        foreach ($needQuestionIds as $key => $item) {
            if (!$item) {
                unset($needQuestionIds[$key]);
            }
        }

        $currentQuestionId = $needQuestionIds[array_rand($needQuestionIds)];
        $currentQuestion   = $this->getOne($this->getService('Question')->find($currentQuestionId));

        if ( !$currentQuestion ) {
            echo _('Благодарим вас за прохождение опроса. Ответы на все вопросы получены.');
            return;
        }

        if ( isset($questionPollLink[$currentQuestionId]) ) {
            $currentPoll           = $questionPollLink[$currentQuestionId];
            $this->view->pollTitle = $currentPoll->title;
            $this->view->pollID    = $currentPoll->quiz_id;
            $this->view->linkID    = array_search($currentPoll->quiz_id, $pollIDs);
        }

        $answerWhere     = $this->quoteInto(array('quiz_id = ?', ' AND question_id = ?'), array($currentPoll->quiz_id, $currentQuestionId));
        $questionAnswers = $this->getService('PollAnswer')->fetchAll($answerWhere);
        $firstAnswer     = $this->getOne($questionAnswers);
        if ( $firstAnswer ) {
            $this->view->questionText = $firstAnswer->question_title;
        }
        $this->view->question = $currentQuestion;
        $this->view->answers  = $questionAnswers;
        echo $this->view->render('page/get-question.tpl');
    }

    public function saveAnswerAction()
    {
        $pollId     = $this->_getParam('quiz_id', 0);
        $questionId = $this->_getParam('question_id', 0);
        $linkID     = $this->_getParam('link_id', 0);
        $answers    = $this->_getParam('answers', $this->_getParam('answer',0));

        if (!$pollId || !$questionId || !$answers || !$linkID) {
            $this->view->assign(array('result' => 'fail'));
            return;
        }

        if ( is_array($answers) ) {
            foreach ($answers as $answerID) {
                $resultData = array(
                    'user_id'     => $this->getService('User')->getCurrentUserId(),
                    'lesson_id'   => 0,
                    'question_id' => $questionId,
                    'answer_id'   => $answerID,
                    'quiz_id'     => $pollId,
                    'link_id'     => $linkID
                );

                $this->getService('PollResult')->insert($resultData);
            }
        } else {
            $answers = trim($answers);
            $resultData = array(
                'user_id'         => $this->getService('User')->getCurrentUserId(),
                'lesson_id'       => 0,
                'question_id'     => $questionId,
                'answer_id'       => 0,
                'freeanswer_data' => $answers,
                'quiz_id'         => $pollId,
                'link_id'         => $linkID
            );

            $this->getService('PollResult')->insert($resultData);
        }

        $this->view->assign(array('result' => 'ok'));
    }
}