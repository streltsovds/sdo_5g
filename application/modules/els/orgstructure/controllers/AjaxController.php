<?php
class Orgstructure_AjaxController extends HM_Controller_Action
{
    protected $emptyDepartmentsCache;

    public function init()
    {
        $this->_helper->ContextSwitch()->addActionContext('tree', 'xml')->initContext('xml');
    }

    public function treeAction()
    {
        $owner = 0;
        $itemPath = null;
        $itemId = (int)$this->_getParam('item_id', 0);
        $onlyDepartments = (int)$this->_getParam('only-departments', false);
        $showAsParent = false;

        if ($itemId) {

            // drill down
            $item = $this->getOne($this->getService('Orgstructure')->find($itemId));
            if ($item) {
                $owner = $item->owner_soid;
                $itemPath = $this->getTreePath($item);
            }
        } elseif ($this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL])) {

            // смотрят список подразделений верхнего уровня
            $currentUserId = $this->getService('User')->getCurrentUserId();
            if (count($responsibility = $this->getService('Responsibility')->get($currentUserId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE))) {


                $showAsParent = true;
                $itemId = array_shift($responsibility); // сейчас нет возможности задать несколько responsibility
            }
        }

        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $items = [];
        $types = $onlyDepartments
            ? [HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT]
            : [
                HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT,
                HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                HM_Orgstructure_OrgstructureModel::TYPE_VACANCY,
            ];

        $params = ['type IN (?) AND blocked = 0'];
        $values = [$types];

        // При наличии ограничений по ОО, если это последнее подразделение в дереве, нужно его тоже в выборку брать
        // иначе при $onlyDepartments == true будет пустой список
        if($showAsParent) {
            $params[] = ' AND soid = ?';
            $values[] = $itemId;
        } else {
             $params[] = ' AND owner_soid = ?';
             $values[] = $itemId;
        }

        $collection = $this->getService('Orgstructure')->fetchAllDependence(
            'Descendant',
            $this->quoteInto($params, $values),
            false,
            null,
            [new Zend_Db_Expr('ABS(type)'), 'name']
        );

        if (count($collection)) {
            foreach($collection as $unit) {
                if (!is_null($this->emptyDepartmentsCache) && in_array($unit->soid, $this->emptyDepartmentsCache)) continue;
                if ($onlyDepartments) {
                    $leaf = true;
                    if (isset($unit->descendants) && count($unit->descendants)) {
                        foreach ($unit->descendants as $descendant) {
                        	if ($descendant->type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                        	    $leaf = false;
                        	    break;
                        	}
                        }
                    }
                } else {
                    $leaf = $unit->isPosition();
                }

                $items[] = [
                    "id" => $unit->soid,
                    "value" => htmlspecialchars($unit->name),
                    "leaf" => !!$leaf
                ];
            }
        }
        $response = [
            "owner" => $owner,
            "items" => $items,
            "path"  => $itemPath
        ];
/*        $xml = "<?xml version=\"1.0\" encoding=\"".Zend_Registry::get('config')->charset."\"?><tree owner=\"".$owner."\">".join('', $items)."</tree>";*/
//        $this->view->xml = $xml;
        $this->view->response = $response;

    }

    private function getTreePath($item, $path = []) {
        $path[] = [
            'id' => $item->soid,
            'name' => $item->getName()
        ];

        $owner = $this->getOne($this->getService('Orgstructure')->find($item->owner_soid));

        if (!$owner) return $path;
        return $this->getTreePath($owner, $path);
    }
}