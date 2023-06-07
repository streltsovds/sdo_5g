<?php

class Infoblock_HiringDismissalController extends HM_Controller_Action
{

    public function init()
    {
        parent::init();
        header("Pragma: cache");

        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
    }

    public function statsByDepartmentsAction()
    {
        if ($this->isAjaxRequest() && $this->_request->isPost()) {
            $soid = (int) $this->_getParam('soid', 0);
            $fromDate = $this->_getParam('from', 0);
            $fromDate = $fromDate === 'null' ? null : $fromDate;

            $toDate = $this->_getParam('to', 0);
            $toDate = $toDate === 'null' ? null : $toDate;

            Zend_Registry::get('session_namespace_default')->hrds_by_dep->fromDate = $fromDate;
            Zend_Registry::get('session_namespace_default')->hrds_by_dep->toDate = $toDate;
            Zend_Registry::get('session_namespace_default')->hrds_by_dep->soid = $soid;

            // Форматируется здесь, чтобы в темплейте потом передавать в виджет дату, которую он примет
            $fromDate = date('Y-m-d 00:00:00', strtotime($fromDate));
            $toDate = Zend_Date::isDate($toDate) ? date('Y-m-d 23:59:59', strtotime($toDate)) : date('Y-m-d 23:59:59');

            $hiringStats = $this->getStatsByDepartments($soid, $fromDate, $toDate, 'hiring');
            $dismissalStats = $this->getStatsByDepartments($soid, $fromDate, $toDate, 'dismissal');

            $response = array();

            if (count($hiringStats)) {
                foreach ($hiringStats as $hiringValue) {
                    $object = new stdClass();

                    $object->title = $hiringValue['title'];
                    $object->Принятые = (int) $hiringValue['count'];

                    foreach ($dismissalStats as $dismissalKey => $dismissalValue) {

                        // Приводим к нижнему регистру, т.к. группировка выборки из базы идет без учета регистра
                        if (mb_strtolower($hiringValue['title']) === mb_strtolower($dismissalValue['title'])) {
                            $object->Уволенные = (int) $dismissalValue['count'];
                            unset($dismissalStats[$dismissalKey]); // Уменьшаем последующее кол-во итерацией
                            $response[] = $object;
                            continue 2;
                        }
                    }

                    $object->Уволенные = 0;
                    $response[] = $object;
                }
            }

            if (count($dismissalStats)) {
                foreach ($dismissalStats as $dismissalValue) {
                    $object = new stdClass();
                    $object->title = $dismissalValue['title'];
                    $object->Принятые = 0;
                    $object->Уволенные = (int) $dismissalValue['count'];
                    $response[] = $object;
                }
            }

            usort($response, function ($a, $b) {
                return $b->Принятые - $a->Принятые;
            });

            return $this->responseJson($response);
        }
    }

    public function statsByPositionsAction()
    {
        if ($this->isAjaxRequest() && $this->_request->isPost()) {
            $soid = (int) $this->_getParam('soid', 0);

            $fromDate = $this->_getParam('from', 0);
            $fromDate = $fromDate === 'null' ? null : $fromDate;

            $toDate = $this->_getParam('to', 0);
            $toDate = $toDate === 'null' ? null : $toDate;

            Zend_Registry::get('session_namespace_default')->hrds_by_pos->fromDate = $fromDate;
            Zend_Registry::get('session_namespace_default')->hrds_by_pos->toDate = $toDate;
            Zend_Registry::get('session_namespace_default')->hrds_by_pos->soid = $soid;

            // Форматируется здесь, чтобы в темплейте потом передавать в виджет дату, которую он примет
            $fromDate = date('Y-m-d 00:00:00', strtotime($fromDate));
            $toDate = Zend_Date::isDate($toDate) ? date('Y-m-d 23:59:59', strtotime($toDate)) : date('Y-m-d 23:59:59');

            $hiringStats = $this->getStatsByPositions($soid, $fromDate, $toDate, 'hiring');
            $dismissalStats = $this->getStatsByPositions($soid, $fromDate, $toDate, 'dismissal');

            $response = array();

            if (!count($hiringStats) && !count($dismissalStats)) {
                return $this->responseJson($response);
            }

            if (count($hiringStats)) {
                foreach ($hiringStats as $hiringValue) {
                    $object = new stdClass();

                    $object->title = $hiringValue['title'];
                    $object->Принятые = (int) $hiringValue['count'];

                    foreach ($dismissalStats as $dismissalKey => $dismissalValue) {

                        // Приводим к нижнему регистру, т.к. группировка выборки из базы идет без учета регистра
                        if (mb_strtolower($hiringValue['title']) === mb_strtolower($dismissalValue['title'])) {
                            $object->Уволенные = (int) $dismissalValue['count'];
                            unset($dismissalStats[$dismissalKey]); // Уменьшаем последующее кол-во итерацией
                            $response[] = $object;
                            continue 2;
                        }
                    }

                    $object->Уволенные = 0;
                    $response[] = $object;
                }
            }

            // Если уволенных было больше, чем принятых
            if (count($dismissalStats)) {
                foreach ($dismissalStats as $dismissalValue) {
                    $object = new stdClass();
                    $object->title = $dismissalValue['title'];
                    $object->Принятые = 0;
                    $object->Уволенные = (int) $dismissalValue['count'];
                    $response[] = $object;
                }
            }

            usort($response, function ($a, $b) {
                return $b->Принятые - $a->Принятые;
            });

            return $this->responseJson($response);
        }
    }

    private function getStatsByDepartments($soid, $fromDate, $toDate, $statsType)
    {
        switch ($statsType) {
            case 'hiring':
                $statTableName = 'structure_of_organ'; // Принятые
                $whereDateColumn = 'position_date';
                break;

            case 'dismissal':
                $statTableName = 'structure_of_organ_history'; // Уволенные
                $whereDateColumn = 'deleted_at';
                $unit = $this->getService('OrgstructureHistory')->getOne(
                    $this->getService('OrgstructureHistory')->find($soid)
                );

                if (!$unit && $soid > 0) {
                    return array();
                }
                break;
        }

        $subSelect = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
        $subSelect
            ->from(
                array('so' => "{$statTableName}"),
                array('pos_count' => new Zend_Db_Expr('COUNT(so.name)'), 'so.owner_soid')
            )
            ->where($this->quoteInto('so.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION))
            ->where($this->quoteInto("so.{$whereDateColumn} >= ?", $fromDate))
            ->where($this->quoteInto("so.{$whereDateColumn} <= ?", $toDate))
            ->group(array('so.name', 'so.owner_soid'));

        if ($soid > 0) {
            $subSelect
                ->joinInner(
                    array('parent_so' => "{$statTableName}"),
                    'so.lft > parent_so.lft AND so.rgt < parent_so.rgt',
                    array()
                )
                ->where($this->quoteInto('parent_so.soid = ?', $soid));
        }

        $select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
        $select
            ->from(
                array('owner_so' => "{$statTableName}"),
                array('title' => 'owner_so.name', 'count' => new Zend_Db_Expr('SUM(pos_stat.pos_count)'))
            )
            ->joinInner(
                array('pos_stat' => $subSelect),
                'owner_so.soid = pos_stat.owner_soid',
                array()
            )
            ->group(array('owner_so.name'));


        $return = $select->query()->fetchAll();

        return $return;
    }

    private function getStatsByPositions($soid, $fromDate, $toDate, $statsType)
    {
        switch ($statsType) {
            case 'hiring':
                $statTableName = 'structure_of_organ'; // Принятые
                $whereDateColumn = 'position_date';
                break;

            case 'dismissal':
                $statTableName = 'structure_of_organ_history'; // Уволенные
                $whereDateColumn = 'deleted_at';
                $unit = $this->getService('OrgstructureHistory')->getOne(
                    $this->getService('OrgstructureHistory')->find($soid)
                );

                if (!$unit && $soid > 0) {
                    return array();
                }
                break;
        }

        $select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();

        $select
            ->from(
                array('so' => "{$statTableName}"),
                array('title' => 'ap.name', 'count' => new Zend_Db_Expr('COUNT(so.soid)'))
            )
            ->joinInner(
                array('ap' => 'at_profiles'),
                'so.profile_id = ap.profile_id',
                array()
            )
            ->where($this->quoteInto('so.type = ?', HM_Orgstructure_OrgstructureModel::TYPE_POSITION))
            ->where($this->quoteInto("so.{$whereDateColumn} >= ?", $fromDate))
            ->where($this->quoteInto("so.{$whereDateColumn} <= ?", $toDate))
            ->group(array('ap.profile_id', 'ap.name'));

        if ($soid > 0) {
            $select
                ->joinInner(
                    array('parent_so' => "{$statTableName}"),
                    'so.lft > parent_so.lft AND so.rgt < parent_so.rgt',
                    array()
                )
                ->where($this->quoteInto('parent_so.soid = ?', $soid));
        }

        $return = $select->query()->fetchAll();

        return $return;
    }
}
