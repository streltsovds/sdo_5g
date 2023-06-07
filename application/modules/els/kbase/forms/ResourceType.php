<?php
class HM_Form_ResourceType extends HM_Form
{
    protected $_resource;

	public function init()
    {
        $resourceId = $this->getParam('resource_id', 0);
        $this->_resource = $this->getService('Resource')->findOne($resourceId);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement('hidden', 'resource_id', array(
            'Required' => false,
        ));
    }

    public function addSubmitBlock()
    {
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к редактированию карточки ресурса'),
                    'url' => $this->getView()->url([
                        'module' => 'kbase',
                        'controller' => 'resource',
                        'action' => 'edit-card',
                        'resource_id' => $this->_resource->resource_id,
                        'idType' => null,
                    ]),
                ],
            ]
        ]);

        if ($this->_resource->subject_id) {
            if ($this->getView()->idType) {
                $backUrl = $this->getView()->url([
                    'module' => 'subject',
                    'controller' => 'materials',
                    'action' => 'index',
                    'subject_id' => $this->_resource->subject_id,
                    'idType' => null,
                ]);
            } else {
                $backUrl = $this->getView()->url([
                    'module' => 'subject',
                    'controller' => 'lessons',
                    'action' => 'edit',
                    'subject_id' => $this->_resource->subject_id,
                    'idType' => null,
                ]);
            }
        } else {
            $backUrl = $this->getView()->url([
                'module' => 'kbase',
                'controller' => 'resources',
                'action' => 'index',
            ]);
        }

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $backUrl,
        ));

        parent::init();
	}
}
