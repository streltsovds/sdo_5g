<?php
class Resource_ListController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->resources;
    }
    
    /**
     * Создание инфоресурса в курсе
     * 
     * @param stdObj $resource
     * @param stdObj $subject
     * @return int ID созданного курса
     */
    public function createResource($resource, $subject, $openExtendedPage = true) 
    {
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page, $openExtendedPage);
        
        $data = [
            'steps' => [
                [
                    'title' => $resource->title,
                    'type' => [
                        'type' => 'select',
                        'value' => $resource->type,
                    ],
                ],
        ]];
        
        switch ($resource->type) {
            case HM_Resource_ResourceModel::TYPE_HTML:
                $data['steps'][] = [
                    'content' => [
                        'type' => 'wysiwyg',
                        'value' => $resource->html,
                    ],            
                ];
                break;
            case HM_Resource_ResourceModel::TYPE_EXTERNAL:
                $data['steps'][] = [
                    'file' => [
                        'type' => 'file',
                        'value' => $resource->file,
                    ],            
                ];
                break;
        }
        
        $this->createEntity($data, false);
        
        $resourceId = $this->grabEntityId('title', $resource->title);
        
        $this->rollback('delete from resources where resource_id = %d', $resourceId);
        
        return $resourceId;        
    }
}