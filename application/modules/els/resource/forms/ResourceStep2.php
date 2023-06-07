<?php
class HM_Form_ResourceStep2 extends HM_Form_SubForm
{

    public function init()
    {

        $resourceId = $this->getParam('resource_id', 0);
        $subjectId = $this->getParam('subject_id', 0);
        
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('resourceStep2');

        if(!$resourceId)
            $this->addElement('hidden', 'prevSubForm', array(
                                                            'Required' => false,
                                                            'Value' => 'resourceStep1'
                                                       ));

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
        if($resourceId) {
            $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));
            $type = $resource->type;

            if (!$resource->subject_id) {

                $this->addElement($this->getDefaultCheckboxElementName(), 'saveAsRevision', array(
                    'Label' => _('Сохранить текущую версию в истории изменения ресурса'),
                    'Required' => false,
                    'Value' => 1,
                ));
            }
        }
        else{
            $session = $this->getSession();
            $type = $session['resourceStep1']['type'];
        }

        switch($type) {
            case HM_Resource_ResourceModel::TYPE_EXTERNAL:
                $this->initFile();
                break;
            case HM_Resource_ResourceModel::TYPE_HTML:
                $this->initHTML();
                break;
            case HM_Resource_ResourceModel::TYPE_URL:
                $this->initURL();
                break;
            case HM_Resource_ResourceModel::TYPE_FILESET:
                $this->initFileSet();
                break;
        }

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к редактированию карточки информационного ресурса'),
                    'url' => $this->getView()->url([
                        'module' => 'resource',
                        'controller' => 'list',
                        'action' => 'edit-resource'
                    ]),
                ],
            ]
        ]);

        $this->addDisplayGroup(
            array(
                'filezip',
                'file',
                'content',
                'url',
                'saveAsRevision',
                'externalViewer',
                'prevSubForm',
                'cancelUrl',
                'submit',
                'submit_and_redirect'
            ),
            'ContentGroup',
            array('legend' => _('Содержимое ресурса'))
        );

        parent::init(); // required!
    }


    /**
     * Настройка полей для типа ресурса "Набор файлов"
     */
    public function initFileSet()
    {
        $this->addElement($this->getDefaultTextElementName(), 'url', array(
            'Required' => true, 
            'Value' => 'index.htm', 
            'Label' => _('Запускаемый файл')
        ));

        $this->addElement($this->getDefaultFileElementName(), 'filezip', array(
		    'Label'             => _('Zip-архив с файлами'),
            'Destination'       => realpath(Zend_Registry::get('config')->path->upload->resource),
//            'file_size_limit'   => 104857600,
            'file_types'        => '*.zip',
            'file_upload_limit' => 1
		    ));
    }

    public function initFile()
    {
        $this->addElement('hidden', 'content', array('Required' => false, 'Value' => ''));

        $this->addElement($this->getDefaultFileElementName(), 'file', array(
		    'Label' => _('Файл(ы)'),
		    'Description' => _('Можно одновременно выбрать несколько файлов. В таком случае система будет отображать список загруженных файлов, с возможностью открыть каждый из файлов по отдельности либо скачать все вместе одним архивом.'),
		    'Destination' => realpath(Zend_Registry::get('config')->path->upload->resource),
            'file_upload_limit' => 15,
		    'validators' => array(
//		        array('Count', false, 1),
		        //array('Extension', false, 'zip'),
		        //array('IsCompressed', false, 'zip')
		    )
		));
        
        $this->addElement($this->getDefaultCheckboxElementName(), 'externalViewer', array(
            'Label' => _('Использовать сервис Google для просмотра содержимого ресурса'),
            'Required' => false,
            'Value' => 0,
        ));
        
// todo
//        $this->addElement($this->getDefaultFileElementName(), 'file', array(
//		    'Label' => _('Файл ресурса'),
//		    'Destination' => realpath(Zend_Registry::get('config')->path->upload->resource),
//            'file_size_limit' => 10485760,
//            'file_types' => '*.jpg;*.png;*.gif;*.jpeg;*.bmp',
//            'file_upload_limit' => 1,
//		    'validators' => array(
//		        array('Count', false, 1),
//		        //array('Extension', false, 'zip'),
//		        //array('IsCompressed', false, 'zip')
//		    )
//		));
//
    }

    public function initUrl()
    {
        $this->addElement($this->getDefaultTextElementName(), 'url', array('Required' => true, 'Value' => 'http://', 'Label' => _('Ссылка на внешний ресурс')));
    }

    public function initHTML()
    {
        $url = array(
            'module' => 'storage',
            'controller' => 'index',
            'action' => 'elfinder'
        );

        if ($this->getParam('subject_id', 0)) {
            $url['subject'] = 'subject';
            $url['subject_id'] = (int) $this->getParam('subject_id', 0);
        } elseif ($this->getParam('course_id', 0)) {
            $url['subject'] = 'course';
            $url['subject_id'] = (int) $this->getParam('course_id', 0);
        } elseif ($this->getParam('resource_id', 0)) {
            $url['subject'] = 'resource';
            $url['subject_id'] = (int) $this->getParam('resource_id', 0);
        }

        

        $this->addElement($this->getDefaultWysiwygElementName(), 'content', array(
            'Label' => 'Текст',
            'Required' => true,
            'Validators' => array(
            ),
            'Filters' => array('HtmlSanitizeRich'),
            'connectorUrl' => $this->getView()->url($url),
            'fmAllow' => true,
            //'toolbar' => 'hmToolbarMaxi',
            'style' => 'width:100%; height:600px',
        ));
    }

    public function getFileElementDecorators($alias, $first = 'File') {
        $decorators = parent::getFileElementDecorators($alias, $first);

        $resourceId = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('resource_id', 0);

        if ($resourceId) {
            $resource = $this->getService('Resource')->getOne(
                $this->getService('Resource')->find($resourceId)
            );
            if($resource->type != HM_Resource_ResourceModel::TYPE_FILESET){
                array_shift($decorators);
                array_unshift($decorators, array('FileInfo', array(
                     'file' => Zend_Registry::get('config')->path->upload->resource.'/'.$resourceId,
                     'name' => $resource->filename,
                     'download' => $this->getView()->url(array('module' => 'file', 'controller' => 'get', 'action' => 'resource', 'resource_id' => $resourceId))
                )));
                array_unshift($decorators, 'File');
            }
            if(($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) || ($resource->type == HM_Resource_ResourceModel::TYPE_EXTERNAL)){
                $this->addAttribs(array('onsubmit'=>'if(confirm(\''._('Содержимое информационного ресурса будет перезаписано. Продолжить?').'\')) return true; else return false;'));
            }
        }

        return $decorators;
    }
}