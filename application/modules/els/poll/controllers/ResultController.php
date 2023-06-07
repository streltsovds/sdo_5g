<?php
class Poll_ResultController extends HM_Controller_Action {

    public function pageRankAction()
    {
        $pollId = $this->_getParam('quiz_id', 0);

        $poll   = $this->getService('Poll')->getPollObject($pollId);

        if ( !$poll ) { return; }


        // объекты и итоговые оценки
        $pollLinks = $this->getService('PollLink')->getLinksByQuizId($pollId);

        $processedLinks = array();

        foreach( $pollLinks as $linkItem ) {
            $processedLinks[$linkItem->link_id] = array(
                'link_id' => $linkItem->link_id,
                'title' => $linkItem->getItemTitle(),
                'rank'  => $linkItem->getNormalizePageRank()
            );
        }

        usort($processedLinks, array($this, '_linkSort'));


        // список вопросов
        $questionIDs = explode(HM_Question_QuestionModel::SEPARATOR, $poll->data);

        if ( count($questionIDs) ) {
            $where = $this->quoteInto('question_id IN (?)', $questionIDs);
            $questionList = $this->getService('PollAnswer')->fetchAll($where)->getList('question_id', "question_title");
        } else {
            $questionList = array();
        }

        // средние результаты по вопросам
        $answersWeight = $this->getService('Question')->getWeight($questionIDs);
        $results       = $this->getService('PollResult')->getPollResults($pollId);

        $linkQuestionBall  = array();
        $linkQuestionUsers = array();
        foreach ( $results as $resultItem ) {
            $linkQuestionBall[$resultItem->link_id][$resultItem->question_id] += (isset($answersWeight[$resultItem->question_id][$resultItem->answer_id]))? $answersWeight[$resultItem->question_id][$resultItem->answer_id] : 0;
            $linkQuestionUsers[$resultItem->link_id][$resultItem->question_id][$resultItem->user_id] = 1;
        }

        $this->view->canViewDetail = $this->getService('Poll')->canViewDetailResultPageRate($poll);
        $this->view->questionBall  = $linkQuestionBall;
        $this->view->questionUsers = $linkQuestionUsers;
        $this->view->answerWeight  = $answersWeight;
        $this->view->links         = $processedLinks;
        $this->view->questionList  = $questionList;
    }

    public function detailRankAction(){
        $linkId = $this->_getParam('link_id', 0);
        $pollId = $this->getService('PollLink')->getOne($this->getService('PollLink')->find($linkId));
        if ( !$pollId ) { return; }
        else{
            $pollId=$pollId->quiz_id;
        }
        $poll   = $this->getService('Poll')->getPollObject($pollId);
        if ( !$poll ) { return; }
        $processedLinks = array();

        usort($processedLinks, array($this, '_linkSort'));


        // список вопросов
        $questionIDs = explode(HM_Question_QuestionModel::SEPARATOR, $poll->data);

        if ( count($questionIDs) ) {
            $where = $this->quoteInto('question_id IN (?)', $questionIDs);
            $questionList = $this->getService('PollAnswer')->fetchAll($where);
        } else {
            $questionList = array();
        }
        $questions=array();
        foreach ($questionList as $question){
            if (!array_key_exists($question->question_id,$questions)){
                $questions += array($question->question_id=>array(
                    'Title' => $question->question_title,
                    'answers'=> array($question->answer_id => $question->answer_title)
                ));
            }
            else{
                $questions[$question->question_id]['answers']=array_merge(
                    $questions[$question->question_id]['answers'],array($question->answer_id => $question->answer_title));
            }
        }
        $where  = $this->getService('PollResult')->quoteInto(array('quiz_id = ?','AND link_id = ?'), array($pollId,$linkId));
        $results = $this->getService('PollResult')->fetchAllDependence('User', $where);
        $res=array();
        foreach ($results as $resultsItem){
            $user=$resultsItem->user->offsetGet(0)->getData();
            $user=implode(' ',array($user['LastName'],$user['FirstName'],$user['Patronymic']));
            $res=array_merge_recursive($res, array(
                    $user => array($resultsItem->question_id => $resultsItem->answer_id)
                )
            );
        }
        $this->view->questions = $questions;
        $this->view->results = $res;
    }

    public function _linkSort($l1, $l2)
    {
        if ($l1['rank'] == $l2['rank']) return 0;
        return ($l1['rank'] < $l2['rank'])? 1 : -1;
    }
}