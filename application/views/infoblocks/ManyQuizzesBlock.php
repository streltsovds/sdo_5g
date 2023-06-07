<?php


class HM_View_Infoblock_ManyQuizzesBlock extends HM_View_Infoblock_Abstract
{
	protected $id = 'many-quizzes';
    protected $session;

	public function manyQuizzesBlock($param = null)
	{
		$this->session = new Zend_Session_Namespace('infoblock_many_quizzes');
		$serviceContainer = Zend_Registry::get('serviceContainer');
        $this->session->questionId = array();
		$userId = $serviceContainer->getService('User')->getCurrentUserId();
        $this->view->isModerator = $serviceContainer->getService('Activity')->isUserActivityPotentialModerator($userId);
		$select = $this->getService('Test')->getSelect();
		$select->from(array('i' => 'interface'), array('param_id'))
	        ->where(new Zend_Db_Expr($serviceContainer->getService('Infoblock')->quoteInto('block = ?', 'manyQuizzesBlock')))
        	->limit(1);
		
        if ($rowset = $select->query()->fetchAll()) {
        	if (!empty($rowset[0]['param_id'])) {
        		$params = unserialize($rowset[0]['param_id']);
        		$questId = $params['quest_id'];

				$condition = array(
					'quest_id = ?' => $questId,
					'user_id = ?' => $userId,
					'context_event_id = ?' => $questId,
					'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_WIDGET,
					'status = ?' => HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED
				);

				if ($userId && $attempt = $this->getService('QuestAttempt')->getOne($this->getService('QuestAttempt')->fetchAll($condition))) {
                    $name = $this->getService('Quest')->getOne($this->getService('Quest')->fetchAll(array('quest_id = ?' => $questId)))->name;
				    $this->view->ajaxUrl = $this->view->url(array(
						'module' => 'quest',
						'controller' => 'report',
						'action' => 'poll-widget',
                        'name' => $name,
					));
				} else {
					$this->view->ajaxUrl = $this->view->url(array(
						'module' => 'quest',
						'controller' => 'poll-widget',
						'action' => 'start',
						'quest_id' => $questId,
						'advance' => 1
					));

					$this->view->saveUrl = $this->view->url(array(
						'module' => 'quest',
						'controller' => 'poll-widget',
						'action' => 'save'
					));
				}
        	} else {
       		    $this->view->enabled = false;
        	    $content = $this->view->render('manyQuizzesBlock.tpl');
        	    return $this->render($content);
        	}
        }
		$this->view->questId = $questId;
		$quest = $this->getService('Quest')->findOne($questId);

		if (!$quest || !$quest->status) $this->view->enabled = false;
		else $this->view->enabled = true;

    	$content = $this->view->render('manyQuizzesBlock.tpl');

		$this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/infoblocks/many-quizzes/style.css'), 'screen,print');
		$this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/forms/at-forms.css'), 'screen,print');
		$this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/forms.css'), 'screen,print');
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css');
		$this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/forms/competencies.css'), 'screen,print');
		$this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/quest.js') );
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.checkbox.js');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.cookie.js');
        //$this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/many-quizzes/script.js');

		
		return $this->render($content);
	}
}