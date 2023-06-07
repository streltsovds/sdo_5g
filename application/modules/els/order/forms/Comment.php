<?php
class HM_Form_Comment extends HM_Form
{
	public function init()
	{
        $subjectId = (int) $this->getParam('subject_id', 0);
        $postMassParamName = 'postMassIds_grid' . ($subjectId ?: '');

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('comment');
        $this->setAction($this->getView()->url(array('module' => 'order', 'controller' => 'list', 'action' => 'reject-by', 'subject_id' => $subjectId)));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'order', 'controller' => 'list', 'action' => 'index', 'subject_id' => $subjectId))
        ));

        $this->addElement('hidden', $postMassParamName, array(
            'required' => true,
            'filters' => array(
            )
        ));

        $this->addElement('hidden', 'subject_id', array(
            'required' => false,
            'filters' => array(
                'Int'
            )
        ));


        $this->addElement($this->getDefaultTextAreaElementName(), 'comments_all', array(
            'Label' => _('Комментарий'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',255,0)
            )
        ));

        //$this->getElement('message')->addFilter(new HM_Filter_Utf8());

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'subject_id',
                'comments_all'
            ),
            'rejectGroup1',
            array('legend' => _('Всем'))
        );

        $elements = array();
        $ids = explode(',', $this->getParam($postMassParamName, array()));
        if (count($ids)) {
            $orders = $this->getService('Claimant')->findDependence(array('User', 'Subject'), $ids);
            foreach($orders as $order) {
                if ($order->status == HM_Role_ClaimantModel::STATUS_REJECTED) continue;
                $name = 'comments_'.$order->SID;
                $elements[] = $name;
                $this->addElement($this->getDefaultTextAreaElementName(), $name, array(
                    'Label' => sprintf('%s, %s', $order->getUser($order)->getName(), $order->getSubject($order)->name),
                    'Required' => false,
                    'Validators' => array(
                        array('StringLength',255,3)
                    )
                ));
            }
        }

        if ($elements) {
            $this->addDisplayGroup(
                $elements,
                'rejectGroup2',
                array('legend' => _('Персонально'))
            );
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}