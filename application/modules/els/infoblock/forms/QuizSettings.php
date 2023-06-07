<?php
class HM_Form_QuizSettings extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('quiz-settings');
        $this->setAction($this->getView()->url());

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));

        $this->addElement('hidden', 'getQuestionsUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'infoblock', 'controller' => 'quizzes', 'action' => 'get-questions'))
        ));

		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $questWhereCols = array('type = ? ', ' AND status = ? AND (subject_id IS NULL OR subject_id = 0)');
        $questWhereVals = array(HM_Quest_QuestModel::TYPE_POLL, HM_Quest_QuestModel::STATUS_RESTRICTED);

        $currentUserRole = $this->getService('User')->getCurrentUserRole();

        if(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER != $currentUserRole) {
            array_push($questWhereCols, ' AND creator_role <> ?');
            array_push($questWhereVals, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER);
        }

        $questService = $this->getService('Quest');
        $collection = $questService->fetchAll($questService->quoteInto(
            $questWhereCols, $questWhereVals
        ));
        $tests = $collection->getList('quest_id', 'name', _('Выберите опрос'));

        $this->addElement($this->getDefaultSelectElementName(), 'quest_id', array(
            'Label' => _('Опрос'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать значение из списка")))
            ),
            'filters' => array('int'),
            'multiOptions' => $tests
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'question_id', array(
            'Label' => _('Вопрос'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать значение из списка")))
            ),
            'filters' => array('int'),
            'multiOptions' => array(_('Выберите вопрос'))
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'getQuestionsUrl',
                'quest_id',
                'question_id',
                'submit',
            ),
            'QuizGroup',
            array('legend' => _('Настройки блока опросов'))
        );

        parent::init(); // required!
	}
}