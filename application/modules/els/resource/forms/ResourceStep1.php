<?php
class HM_Form_ResourceStep1 extends HM_Form_SubForm
{
	public function init()
	{
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        if ($resourceId = $this->getParam('resource_id', 0)) {
            $resource = Zend_Registry::get('serviceContainer')->getService('Resource')->find($resourceId)->current();
        }
        $subjectId = $this->getParam('subject_id', 0);
            
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        //$this->setAttrib('onSubmit', "select_list_select_all('list2');");
        $this->setName('resourceStep1');

        $url = array(
            'module'     => 'resource',
            'controller' => 'list',
            'action'     => 'index',
        );
        if($subjectId){
            $url['subject_id'] = $subjectId;
        }
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url($url, null, true)
        ));

        $this->addElement('hidden', 'resource_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        if($resourceId && $resource && ($resource->type != HM_Resource_ResourceModel::TYPE_CARD)) {
            $this->addElement($this->getDefaultSelectElementName(), 'type', array(
                'label' => _('Тип ресурса'),
                'filters' => array(array('digits')),
                'id' => 'type_id',
                'multiOptions' => HM_Resource_ResourceModel::getTypes(),
                'disabled' => true
            ));
        }
        else{
            $this->addElement($this->getDefaultSelectElementName(), 'type', array(
                'label' => _('Тип ресурса'),
                'filters' => array(array('digits')),
                'required' => true,
                'id' => 'type_id',
                'multiOptions' => HM_Resource_ResourceModel::getEditableTypes()
            ));
        }

	    if (in_array($userService->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN
        ))) {
            $this->addElement($this->getDefaultSelectElementName(), 'status', array(
                    'label' => _('Статус ресурса БЗ'),
                    'description' => _('Опубликованные ресурсы доступны всем авторизованным пользователям через Портал Базы знаний; ограниченное использование ресурсов предполагает возможность включения их в состав учебных курсов преподавателями, но они не доступны через Портал; неопубликованные ресурсы доступны только менеджерам Базы знаний.'),
                    'required' => true,
                    'filters' => array(array('int')),
                    'multiOptions' => HM_Resource_ResourceModel::getStatuses()
                )
            );
        } else {
            $this->addElement('hidden', 'status',
                array(
                    'required' => true,
                    'filters' => array(array('int')),
                    'value' => HM_Resource_ResourceModel::STATUS_STUDYONLY,
                )
            );
        }


        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Краткое описание'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $tags = $resourceId ? $this->getService('Tag')->getTags($resourceId, $this->getService('TagRef')->getResourceType() ) : '';

        $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
            'Label' => _('Метки'),
            'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
            'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'tags')),
            'value' => $tags,
            'Filters' => array()
        ));

//        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
//                'Label' => _('Метки'),
//				'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
//                'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'tags')),
//                'value' => $tags,
//                'Filters' => array()
//            )
//        ));

        if (!$this->getParam('subject_id', 0)) {

            $this->addElement($this->getDefaultTagsElementName(), 'related_resources', array(
                'Label' => _('Связанные ресурсы'),
                'Description' => _('Используйте знак # для указания ID ресурса'),
                'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'resources-list')),
                'value' => array(),
                'newel' => false,
                'height' => 3,
                'maxitimes' => 10,
                'Filters' => array()
            ));

//            $this->addElement(new HM_Form_Element_FcbkComplete('related_resources', array(
//                'Label' => _('Связанные ресурсы'),
//                'Description' => _('Используйте знак # для указания ID ресурса'),
//                'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'resources-list')),
//                'value' => array(),
//                'newel' => false,
//                'height' => 3,
//                'maxitimes' => 10,
//                'Filters' => array()
//            )));
	    } else {
            $this->addElement('hidden', 'related_resources', array('value' => ''));
	    }


        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'resource_id',
                'title',
                'type',
                'status',
                /*'file',*/
                'description',
                'tags',
                'related_resources',
                'submit'
            ),
            'resourceGroup',
            array('legend' => _('Общие свойства'))
        );

        if (!$this->getParam('subject_id', 0)) {
            $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $this->getParam('resource_id', 0));
            $this->addClassifierDisplayGroup($classifierElements);
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => ($resourceId) ? _('Сохранить') : _('Далее')));

        parent::init(); // required!
	}

/*    public function getFileElementDecorators($alias, $first = 'File') {
        $decorators = parent::getFileElementDecorators($alias, $first);

        $resourceId = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('resource_id', 0);

        if ($resourceId) {
            $resource = Zend_Registry::get('serviceContainer')->getService('Resource')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Resource')->find($resourceId)
            );
            array_shift($decorators);
            array_unshift($decorators, array('FileInfo', array(
                 'file' => Zend_Registry::get('config')->path->upload->resource.'/'.$resourceId,
                 'name' => $resource->filename,
                 'download' => $this->getView()->url(array('module' => 'file', 'controller' => 'get', 'action' => 'resource', 'resource_id' => $resourceId))
            )));
            array_unshift($decorators, 'File');
        }

        return $decorators;
    }*/

}