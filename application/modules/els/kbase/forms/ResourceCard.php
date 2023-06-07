<?php
class HM_Form_ResourceCard extends HM_Form
{
    public function init()
    {
        $subjectId = $this->getParam('subject_id', 0);
        $resourceId = HM_Controller_Action_Resource::_getLastId($this->getParam('resource_id', 0));

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement($this->getDefaultStepperElementName(), 'stepper', [
            "steps" => array(
                _('Общие свойства') => ['resourceGroup'],
                _('Классификация') => ['classifiers'],
            ),
            "form" => $this
        ]);

        $this->addElement('hidden', 'resource_id', array('Value' => $resourceId));

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

        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Краткое описание'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTagsElementName(), 'related_resources', array(
            'Label' => _('Связанные ресурсы'),
//            'Description' => _('Используйте знак # для указания ID ресурса'),
            'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'resources-list')),
            'value' => array(),
            'newel' => false,
            'height' => 3,
            'maxitimes' => 10,
            'returnIdsNotText' => true,
            'showIdPrefix' => true,
            'Filters' => array()
        ));

        $this->addElement($this->getDefaultTagsElementName(), 'tags', array(
            'Label' => _('Метки'),
            'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать «Enter»'),
            'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'tags')),
            'value' => '',
            'Filters' => array()
        ));

        if (in_array($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN
        ))) {
            $resourceStatuses = HM_Resource_ResourceModel::getStatuses();
            unset($resourceStatuses[HM_Resource_ResourceModel::STATUS_STUDYONLY]);

            $this->addElement($this->getDefaultSelectElementName(), 'status', array(
                    'label' => _('Статус ресурса БЗ'),
                    'description' => _('Опубликованные ресурсы доступны всем авторизованным пользователям через Портал Базы знаний; ограниченное использование ресурсов предполагает возможность включения их в состав учебных курсов преподавателями, но они не доступны через Портал; неопубликованные ресурсы доступны только менеджерам Базы знаний.'),
                    'required' => true,
                    'filters' => array(array('int')),
                    'multiOptions' => $resourceStatuses
                )
            );
        } else {
            $this->addElement('hidden', 'status',
                array(
                    'required' => true,
                    'filters' => array(array('int')),
                    'value' => HM_Resource_ResourceModel::STATUS_PUBLISHED,
                )
            );
        }


        $icon = '';

        /** @var HM_Resource_ResourceModel $resource */
        $resource = $this->getService('Resource')->findOne($resourceId);
        if (!empty($resource)) {
            $icon = $resource->getIcon();
        }

        $this->addElement($this->getDefaultFileElementName(), 'icon', [
            'Label' => _('Изображение'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'Required' => false,
            'Description' => _('Для использования в виджете "Рекомендуемые материалы"'),
            'Filters' => array('StripTags'),
            'file_size_limit' => 10485760,
            'file_types' => '*.jpg;*.png;*.gif;*.jpeg',
            'file_upload_limit' => 1,
            'crop' => [
                'ratio' => 2
            ],
            'preview_url' => $icon,
        ]);

        $this->addDisplayGroup(
            array(
                'title',
                'description',
                'status',
                'related_resources',
                'tags',
                'icon',
            ),
            'resourceGroup',
            array('legend' => _('Общие свойства'))
        );

        $classifierElements = $this->addClassifierElements(
            HM_Classifier_Link_LinkModel::TYPE_RESOURCE,
            $this->getParam('resource_id', 0)
        );

        $this->addClassifierDisplayGroup($classifierElements);

        /** Что развивает */

        $this->addElement($this->getDefaultMultiSelectElementName(), 'criteria', array(
            'Label' => _('Компетенции'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'class' => 'multiselect',
            'idName' => 'criterion_id',
            'remoteUrl' => $this->getView()->url([
                'module' => 'kbase',
                'controller' => 'criteria',
                'action' => 'corporate',
                'material_type' => HM_Event_EventModel::TYPE_RESOURCE,
                'material_id' => $resourceId,
            ]))
        );

        $this->addDisplayGroup(
            array(
                'criteria',
            ),
            'criteriaGroup',
            array('legend' => _('Что развивает'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к редактированию содержимого'),
                    'url' => $this->getView()->url([
                        'module' => 'kbase',
                        'controller' => 'resource',
                        'action' => 'edit',
                        'subject_id' => $subjectId,
                    ]),
                ],
            ]
        ]);

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url($resourceId ? array('action' => 'index', 'resource_id' => $resourceId) : array('action' => 'index'))
        ));

        parent::init(); // required!
    }

}
