<?php
class HM_Report_ReportService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        $data['created'] = $this->getDateTime();
        $data['created_by'] = $this->getService('User')->getCurrentUserId();
        return parent::insert($data);
    }

    public function getTreeContent(HM_Report_Config $config)
    {
        $tree = array();
        $role = $this->getService('User')->getCurrentUserRole(true); // объединяем младшие роли в enduser'а
        $i = 0;
        foreach($config->getDomains() as $domain => $domainTitle) {
            $i++;    
            $reports = $this->fetchAllDependenceJoinInner(
                'ReportRole',
                $this->quoteInto(
                        array('domain = ?', /*' AND status = ?',*/ ' AND role = ?'),
                        array($domain, /*1,*/ $role)
                ),
                'name'
            );

            if (count($reports) || $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN, HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_MANAGER))) {

                $tree[] =
                    array(
                        'title' => $domainTitle,
                        'key' => $i,
                        'isLazy' => false,
                        'isFolder' => true
                    );

            }

            if (count($reports)) {
                $tree[count($tree)-1]['expand'] = true;
                $subtree = array();
                foreach($reports as $report) {
                    $subtree[] = array(
                        'title' => $report->name,
                        'key' => $report->report_id,
                        'isLazy' => false,
                        'isFolder' => false
                    );
                }
                $tree[] = $subtree;
            }
        }
        return $tree;
    }

    public function getListContent(HM_Report_Config $config)
    {
        $list = array();
        $role = $this->getService('User')->getCurrentUserRole(true); // объединяем младшие роли в enduser'а
        $i = 0;
        foreach($config->getDomains() as $domain => $domainTitle) {
            $i++;
            $reports = $this->fetchAllDependenceJoinInner(
                'ReportRole',
                $this->quoteInto(
                        array('domain = ?', /*' AND status = ?',*/ ' AND role = ?'),
                        array($domain, /*1,*/ $role)
                ),
                'name'
            );

            if (count($reports) || $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN, HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_MANAGER))) {

                $sublist = [];
                if (count($reports)) {
                    foreach($reports as $report) {
                        $sublist[] = array(
                            'title' => $report->name,
                            'id' => $report->report_id,
                        );
                    }
                }

                $list[] =
                    array(
                        'title' => $domainTitle,
                        'reports' => $sublist
                    );
            }

        }
        return $list;
    }
}