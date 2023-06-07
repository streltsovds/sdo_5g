<?php

class Subject_SliderController extends HM_Controller_Action
{
    
    public function indexAction()
    {
        $this->view->setSubHeader(_('Настройка витрины'));
        $form = new HM_Form_Slider();

        if ( $this->_request->isPost() ) {
            
            if ( $form->isValid($this->_request->getParams()) ) {
                
                $featuredSubjects = $form->getValue('in_slider');
                
                if (count($featuredSubjects)) {
                    $this->getService('Subject')->updateWhere(array('in_slider' => 0), array());
                    $this->getService('Subject')->updateWhere(array('in_slider' => 1), array('subid IN (?)' => $featuredSubjects));
                }

                $this->_flashMessenger->addMessage(_('Настройки успешно сохранены'));
                $this->_redirector->gotoSimple('index', 'slider', 'subject');
            } else {
                $form->populate($this->_request->getParams());
            }
            
        } else {
            $collection = $this->getService('Subject')->fetchAll(array('in_slider = ?' => 1));
            if (count($collection)) {
                $subjects = $collection->getList('subid', 'subid');
                $form->populate(array('in_slider' => $subjects));
            }
        }
        
        $this->view->form = $form;
    }

    public function subjectsListAction()
    {
        $select = $this->getService('Subject')->getSelect();
        $select->from(array('s' => 'subjects'),
            array(
                'subid' => 's.subid',
                'name' => 's.name',
                'base_id' => 's.base_id',
                'base_color' => 's.base_color',
                'type' => 's.type',
                'reg_type' => 's.reg_type',
                'claimant_process_id' => 's.claimant_process_id',
                'in_slider' => 's.in_slider',
                'classes' => '', // всё валится, оч.большой классификатор
                //'classes' => new Zend_Db_Expr('GROUP_CONCAT(class.name)'),
            )
        )
        ->where($this->getService('Subject')->quoteInto(
            array(
                's.period IN (?) OR ',
                's.period_restriction_type = ? OR ',
                '(s.period_restriction_type = ?',' AND (s.state = ? ',' OR s.state = ? OR s.state is null) ) OR ',
                '(s.period = ? AND ',
                's.end > ?)',
            ),
            array(
                array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED),
                HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                HM_Subject_SubjectModel::STATE_ACTUAL,
                HM_Subject_SubjectModel::STATE_PENDING,
                HM_Subject_SubjectModel::PERIOD_DATES,
                $this->getService('Subject')->getDateTime()
            )
        ))
        ->where('s.reg_type = ?', HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN)
        ->order(array('s.name'))
        ->group(array('s.subid', 's.name', 's.reg_type', 's.type', 's.claimant_process_id', 's.base_id', 's.base_color', 's.in_slider'));

        $subjects = array();
        if (count($rows = $select->query()->fetchAll())) {
            $position = 0;
            foreach ($rows as $row) {
                $subjects[] = [
                    'id' => $row['subid'],
                    'name' => $row['name'],
                    'selected' => $row['in_slider'],
                    'level' => 1,
                    'lft' => ++$position,
                    'rgt' => ++$position,
                ];
            }
        }

        return $this->responseJson($subjects);
    }
}