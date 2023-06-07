<?php
class HM_Extension_ExtensionService extends HM_Service_Standalone_Abstract
{
    const EVENT_AFTER_INIT_EXTENSIONS         = 'event.after.init.extensions';

    const EVENT_FILTER_ADDING_ROLES_UNMANAGED = 'event.filter.adding_roles_unmanaged';
    const EVENT_FILTER_BASIC_ROLES            = 'event.filter.basic.roles';
    const EVENT_FILTER_CLASSIFIER_LINK_TYPES  = 'event.filter.classifier.link.types';
    const EVENT_FILTER_CONTEXT_BLOCK          = 'event.filter.context.block';
    const EVENT_FILTER_MENU                   = 'event.filter.menu';
    const EVENT_FILTER_CONTEXT_MENU           = 'event.filter.context.menu';
    const EVENT_FILTER_SUBJECT_EVALUATION_METHODS = 'event.filter.subject.evaluation.methods';
    const EVENT_FILTER_EVALUATION_METHODS     = 'event.filter.evaluation.methods';

    const EVENT_FILTER_FORM_OPTIONS           = 'event.filter.form.options';
    const EVENT_FILTER_FORM_AT_OPTIONS        = 'event.filter.form.at_options';
    const EVENT_FILTER_FORM_CONTRACT          = 'event.filter.form.contract';
    const EVENT_FILTER_FORM_SUBJECT_FEEDBACK  = 'event.filter.form.subject_feedback';
    const EVENT_FILTER_FORM_USER              = 'event.filter.form.user';

    const EVENT_FILTER_GRID_COLUMNS           = 'event.filter.grid.columns';
    const EVENT_FILTER_GRID_ACTIONS           = 'event.filter.grid.actions';
    const EVENT_FILTER_GRID_MASS_ACTIONS      = 'event.filter.grid.mass_actions';
    const EVENT_FILTER_GRID_SWITCHER          = 'event.filter.grid.switcher';

    const EVENT_FILTER_INFOBLOCKS             = 'event.filter.infoblocks';
    const EVENT_FILTER_LESSON_TYPES           = 'event.filter.lesson_types';
    const EVENT_FILTER_REPORT_DOMAINS         = 'event.filter.report_domains';
    const EVENT_FILTER_SCALE_MODES            = 'event.filter.scale_modes';
    const EVENT_FILTER_USER_CARD_UNIT_INFO    = 'event.filter.user_card_unit_info';
    const EVENT_FILTER_NOTICE_CLUSTERS        = 'event.filter.notice_clusters';

    private $_removers = array();
    private $_removersMustBeRestoredFromCache = true;
    private $_removersRestoredFromCache = false;

    public function init()
    {
        $this->_getRemovers();
        if ($this->_removersRestoredFromCache && count($this->_removers)) {
            foreach($this->_removers as $remover) {
                $remover->init();
                $remover->setServiceContainer($this->getServiceContainer());
                $remover->registerEventsCallbacks();
            }
        }
    }

    private function _getRemovers()
    {
        $modulesXml = APPLICATION_PATH . '/settings/modules.xml';
        if (file_exists($modulesXml) && is_readable($modulesXml)) {
            $modulesCache = 'modules_xml_'.filemtime($modulesXml);
            $removers = Zend_Registry::get('cache')->load($modulesCache);

            if ($this->_removersMustBeRestoredFromCache && ($removers !== false)) {
                $this->_removers = $removers;
                $this->addRemover(new HM_Extension_Remover_LessonsPlanActionsRemover());
                $this->_removersRestoredFromCache = true;
            } else {
                $this->_removers = array();
                if ($xml = simplexml_load_file($modulesXml)) {
                    foreach($xml->module as $module) {
                        $moduleDisabled = strtolower((string) $module['enable']) === 'false';

                        if ($moduleDisabled && isset($module['remover'])) {
                            $removerClass = (string) $module['remover'];
                            $remover = new $removerClass();
                            $remover->setServiceContainer($this->getServiceContainer());
                            $remover->init();
                            $remover->registerEventsCallbacks();

                            $this->addRemover($remover);
                        }
                    }
                    $this->addRemover(new HM_Extension_Remover_LessonsPlanActionsRemover());

                    Zend_Registry::get('cache')->save($this->_removers, $modulesCache);
                }
            }
        }
    }

    public function addRemover(HM_Service_Extension_Remover_Interface $remover)
    {
        $this->_removers[get_class($remover)] = $remover;
    }

    public function getRemover($class)
    {
        return isset($this->_removers[$class]) ? $this->_removers[$class] : false;
    }
}