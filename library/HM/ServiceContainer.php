<?php

class HM_ServiceContainer extends sfServiceContainerBuilder {

    public function getService($id = false)
    {
        if ($id) {
            $service = parent::getService($id);
            if ($service instanceof Es_Entity_Trigger) {
                /*@var $dispatcher sfEventDispatcher */
                $dispatcher = parent::getService('EventDispatcher');
                if (!$dispatcher->hasListeners(get_class($service).'::esPushTrigger')) {
                    $dispatcher->connect(get_class($service).'::esPushTrigger', $service->triggerPushCallback());
                }
            }
            return $service;
        }
    }
}
