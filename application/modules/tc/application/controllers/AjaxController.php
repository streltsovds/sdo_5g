<?php

class Application_AjaxController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();

        $this->_helper->ContextSwitch()->addActionContext('departments', 'xml')->initContext('xml');
    }

    public function departmentsAction()
    {
        $owner  = 0;
        $itemId = (int) $this->_getParam('item_id', 0);

        $currentUserId = $this->getService('User')->getCurrentUserId();

        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $cutedList = false;

        if ($itemId) {
            $item = $this->getOne($this->getService('Orgstructure')->find($itemId));
            if ($item) {
                $owner = $item->owner_soid;
            }
        } elseif ($this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {

            // подразделение специалиста по обучению
            if (count($responsibility = $this->getService('Responsibility')->get($currentUserId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE))) {
                $itemId = array_shift($responsibility);
                $cutedList = true;
            }
        }


        $items = array();
        $collection = $this->getService('Orgstructure')->fetchAllDependence(
            'Descendant',
            $this->quoteInto(
                array($cutedList ? 'soid = ?' : 'owner_soid = ?', ' AND type=?', ' AND blocked!=?'),
                array($itemId, HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT, 1)
            ),
            false,
            null,
            array('type', 'name')
        );

        if (count($collection)) {
            foreach($collection as $unit) {
                $leaf = true;
                if (/*($unit->owner_soid == 0) && */isset($unit->descendants) && count($unit->descendants)) {
                    foreach ($unit->descendants as $descendant) {
                        if ($descendant->type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                            $leaf = false;
                            break;
                        }
                    }
                }

                $items[] = '<item id="'. $unit->soid .'" value="'.htmlspecialchars($unit->name).'" '.($leaf ? 'leaf="yes"' : '').'/>';
            }
        }

        $xml = "<?xml version=\"1.0\" encoding=\"".Zend_Registry::get('config')->charset."\"?><tree owner=\"".$owner."\">".join('', $items)."</tree>";
        $this->view->xml = $xml;

    }

    public function getInitialCoursesListAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);

        $type = $this->_request->getParam('type');
        $tcApplicationService = $this->getService('TcApplication');
        $courses = $tcApplicationService->getSubjectsForForm($type);
        foreach ($courses as $provider => $coursesSet) {
            if ($provider) {
                echo '<optgroup label=\''.$provider.'\'>';
            } else {
                echo '<option value=\'0\' title label></option>';
            }
            foreach ($coursesSet as $id => $course) {
                echo '<option value=\''.$id.'\' title=\''.$course.'\' label=\''.$course.'\' >'.$course.'</option>';
            }
            echo '</optgroup>';
        }
    }
}