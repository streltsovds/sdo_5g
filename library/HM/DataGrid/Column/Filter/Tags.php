<?php

/**
 *
 */
class HM_DataGrid_Column_Filter_Tags
{
    private $value;

    public function __construct(HM_DataGrid $dataGrid)
    {
        $this->value = array(
            'callback' => array(
                'function' => array($this, 'callback'),
                'params' => array($dataGrid)
            )
        );
    }

    static public function create($dataGrid)
    {
        $self = new self($dataGrid);
        return $self->getValue();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function callback($data)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $request = $frontController->getRequest();
        $serviceContainer = $data[0]->getServiceContainer();
        $module = strtolower($request->getModuleName());
        $data['value'] = trim($data['value']);
        $service = false;

        switch ( $module ) {
            case 'blog':
                $service = $serviceContainer->getService('TagRefBlog');
                break;
            case 'resource':
            case 'activity':
                $service = $serviceContainer->getService('TagRefResource');
                break;
            case 'course':
            case 'subject':
                $service = $serviceContainer->getService('TagRefCourse');
                break;
            case 'quest':
                $service = $serviceContainer->getService('TagRefTest');
                break;
            case 'exercises':
                $service = $serviceContainer->getService('TagRefExercises');
                break;
            case 'poll':
                $service = $serviceContainer->getService('TagRefPoll');
                break;
            case 'task':
                $service = $serviceContainer->getService('TagRefTask');
                break;
            case 'study-groups':
            case 'user':
            case 'assign':
                $service = $serviceContainer->getService('TagRefUser');
                break;
            case 'session':
                $service = $serviceContainer->getService('TagRefAtSession');
                break;
        }

        if ($service) {
            $data['select'] = $service->getFilterSelect($data['value'], $data['select']);
        }
    }
}