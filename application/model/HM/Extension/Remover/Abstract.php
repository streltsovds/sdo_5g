<?php
abstract class HM_Extension_Remover_Abstract
    extends HM_Service_Standalone_Abstract
    implements HM_Service_Extension_Remover_Interface
{
    // эти параметры кэшируются в data/cache
    protected $_itemsToHide = [
        'columns'        => [],
        'domains'        => [],
        'elements'       => [],
        'infoblocks'     => [],
        'actions'        => [],
        'massActions'    => [],
        'menu'           => [],
        'methods'        => [],
        'noticeClusters' => [],
        'roles'          => [],
    ];

    public function setItemsToHide(array $items = [])
    {
        foreach ($items as $key => $values) {
            if (isset($this->_itemsToHide[$key])) {
                foreach ($values as $k => $value) {
                    $this->_itemsToHide[$key][$k] = $value;
                }
            }
        }
    }

    public function init()
    {
        $this->setItemsToHide();
    }

    public function callAfterInitExtensions($event)
    {
        return true;
    }

    public function callFilterBasicRoles($event, $roles)
    {
        foreach($this->_getItemsToHide('roles') as $roleToRemove) {
            unset($roles[$roleToRemove]);
        }
        return $roles;
    }

    public function callFilterGridColumns($events, $columns)
    {
        foreach($this->_getItemsToHide('columns') as $col) {
            if (isset($columns[$col])) {
                $columns[$col] = array('hidden' => true);
            }
        }
        return $columns;
    }

    public function callFilterClassifierLinkTypes($event, $types)
    {
        foreach ($this->_getItemsToHide('classifierTypes') as $type) {
            unset($types[$type]);
        }
        return $types;
    }

    public function callFilterForm($event, HM_Form $form)
    {
        foreach($this->_getItemsToHide('elements') as $element) {
            $form->removeElement($element);
        }

        foreach ($form->getDisplayGroups() as $group) {
            $elements = $group->getElements();
            if (!is_array($elements) || !count($elements)) {
                $form->removeDisplayGroup($group->getName());
            }
        }

        return $form;
    }

    public function callFilterGridSwitcher($event, $options)
    {
        unset($options['global']);
        return $options;
    }

    public function callFilterReportDomains($event, $domains)
    {
        foreach($this->_getItemsToHide('domains') as $domainToRemove) {
            unset($domains[$domainToRemove]);
        }
        return $domains;
    }

    public function callFilterSubjectEvaluationMethods($event, $subjectsMethods)
    {
        $return = [];

        foreach($subjectsMethods as $subject => $methods) {

            $return[$subject] = [];
            $methodsToHide = $this->_getItemsToHide('methods');

            foreach($methods as $method) {
                if (!in_array($method, $methodsToHide)) $return[$subject][] = $method;
            }
        }
        return $return;
    }

    public function callFilterEvaluationMethods($event, $methods)
    {
        foreach($this->_getItemsToHide('methods') as $methodToRemove) {
            unset($methods[$methodToRemove]);
        }
        return $methods;
    }

    public function callFilterNoticeClusters($event, $noticeClusters)
    {
        foreach($this->_getItemsToHide('noticeClusters') as $noticeCluster) {
            unset($noticeClusters[$noticeCluster]);
        }
        return $noticeClusters;
    }

    public function callFilterMenu($event, $page)
    {
        $application = isset($page['application']) ? $page['application'] : '';
        $module = isset($page['module']) ? str_replace("{$application}/", '', $page['module']) : '';

        $pageId = isset($page['id']) ? $page['id'] : '';
        $menu = $this->_getItemsToHide('menu');

        if (
            (!isset($menu['module']) && isset($menu['application']) && ($menu['application'] === $application)) ||
            (isset($menu['module']) && !isset($menu['application']) && in_array($module, $menu['module'])) ||
            (isset($menu['module']) && isset($menu['application']) && in_array($module, $menu['module']) && ($menu['application'] === $application)) ||
            (isset($menu['id']) && is_array($menu['id']) && in_array($pageId, $menu['id']))
        ) {
            $page['hidden'] = true;
        }

        if (isset($page['controller']) && isset($page['action'])) {
            $pageId = 'mca:' . $module . ':' . $page['controller'] . ':' . $page['action'];
            if (isset($menu['contextMenu']['id']) && in_array($pageId, $menu['contextMenu']['id'])) {
                $page['hidden'] = true;
            }
        }
        return $page;
    }

    public function callFilterContextMenu($event, $page)
    {
        if (isset($page['actions']) && count($this->_getItemsToHide('actions'))) {
            $actions = $this->_getItemsToHide('actions');
            $toHide = array();
            foreach ($actions as $key => $action) {
                if (isset($action['hide'])) {
                    $hides = is_array($action['hide']) ? $action['hide'] : array($action['hide']);
                    foreach ($hides as $hide) {
                        if (false !== strpos($_SERVER['REQUEST_URI'], '/' . $hide['module'] . '/' . $hide['controller'] . '/' . $hide['action'])) {
                            foreach ($page['actions'] as $key => $value) {
                                if ($action['module']     == $value['module'] &&
                                    $action['controller'] == $value['controller'] &&
                                    $action['action']     == $value['action'])
                                    unset($page['actions'][$key]);
                            }
                        }
                    }
                }
            }
        }

        return $page;
    }

    public function callFilterInfoblocks($event, $blocks)
    {
        $blocksToRemove = $this->_getItemsToHide('infoblocks');
        foreach($blocks as $i => &$block) {
            if (isset($block['name'])) {
                if (in_array($block['name'], $blocksToRemove)) {
                    unset($blocks[$i]);
                } elseif (isset($block['block']) && is_array($block['block'])) {
                    foreach ($block['block'] as $j => $childBlock) {
                        if (isset($childBlock['name']) && in_array($childBlock['name'], $blocksToRemove)) {
                            unset($block['block'][$j]);
                        }
                    }
                }
            }
        }
        return !empty($blocks) ? $blocks : array();
    }

    public function callFilterActions($event, $actions)
    {
        $actionsToRemove = $this->_getItemsToHide('actions');
        foreach($actionsToRemove as $action) {
            $urlToRemove = Zend_Registry::get('view')->url($action);
            foreach ($actions as $i => $action) {
                if (strpos($action['url'], $urlToRemove) !== false) {
                    unset($actions[$i]);
                }
            }
        }
        return $actions;
    }

    public function callFilterMassActions($event, $massActions)
    {
        $actionsToRemove = $this->_getItemsToHide('massActions');
        foreach($actionsToRemove as $action) {
            $urlToRemove = Zend_Registry::get('view')->url($action);
            foreach ($massActions as $i => $massAction) {
                if (strpos($massAction['url'], $urlToRemove) !== false) {
                    unset($massActions[$i]);
                }
            }
        }
        return $massActions;
    }

    public function callFilterUserCardUnitInfo($event, $unitInfo)
    {
        return null;
    }

    public function registerEventsCallbacks()
    {
        $formEvents = [
            HM_Extension_ExtensionService::EVENT_FILTER_FORM_OPTIONS,
            HM_Extension_ExtensionService::EVENT_FILTER_FORM_AT_OPTIONS,
            HM_Extension_ExtensionService::EVENT_FILTER_FORM_CONTRACT,
            HM_Extension_ExtensionService::EVENT_FILTER_FORM_SUBJECT_FEEDBACK,
            HM_Extension_ExtensionService::EVENT_FILTER_FORM_USER,
        ];

        foreach ($formEvents as $formEvent) {
            $this->getService('EventDispatcher')->connect(
                $formEvent,
                array($this, 'callFilterForm')
            );
        }

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_AFTER_INIT_EXTENSIONS,
            array($this, 'callAfterInitExtensions')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_BASIC_ROLES,
            array($this, 'callFilterBasicRoles')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_GRID_COLUMNS,
            array($this, 'callFilterGridColumns')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_CLASSIFIER_LINK_TYPES,
            array($this, 'callFilterClassifierLinkTypes')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_REPORT_DOMAINS,
            array($this, 'callFilterReportDomains')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_EVALUATION_METHODS,
            array($this, 'callFilterEvaluationMethods')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_SUBJECT_EVALUATION_METHODS,
            array($this, 'callFilterSubjectEvaluationMethods')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_MENU,
            array($this, 'callFilterMenu')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_CONTEXT_MENU,
            array($this, 'callFilterContextMenu')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_INFOBLOCKS,
            array($this, 'callFilterInfoblocks')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_GRID_ACTIONS,
            array($this, 'callFilterActions')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_GRID_MASS_ACTIONS,
            array($this, 'callFilterMassActions')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER,
            array($this, 'callFilterGridSwitcher')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_NOTICE_CLUSTERS,
            array($this, 'callFilterNoticeClusters')
        );
    }

    protected function _getItemsToHide($type)
    {
        if (isset($this->_itemsToHide[$type]) && is_array($this->_itemsToHide[$type])) {
            return $this->_itemsToHide[$type];
        } else {
            return array();
        }
    }
}