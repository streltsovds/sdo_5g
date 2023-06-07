<?php
class HM_Kbase_KbaseService extends HM_Service_Abstract
{
    public function checkResourceType($type)
    {
        return ($type = 'scorm') || in_array($type, array_keys(HM_Resource_ResourceModel::getTypes()));
    }

    public function getResourceTypes()
    {
        return array(
            HM_Kbase_KbaseModel::TYPE_RESOURCE,
            HM_Kbase_KbaseModel::TYPE_COURSE,
        );
    }
}