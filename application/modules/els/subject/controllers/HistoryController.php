<?php

class Subject_HistoryController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'end_DESC');
        }

        $isLaborSafety = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL));

        $select = $this->getService('Graduated')->getSelect();
        $select->from(
            array('p' => 'People'),
            array(
                'MID',
                'notempty' => "CASE WHEN (p.LastName IS NULL AND p.FirstName IS NULL AND  p.Patronymic IS NULL) OR (p.LastName = '' AND p.FirstName = '' AND p.Patronymic = '') THEN 0 ELSE 1 END",
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'positions'   => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT d.soid)'),
                'department' => 'd2.name',
                'subject' => 's.name',
                'subject_id' => 's.subid',
                'f.file_id',
                'certificate_id' => 'c.certificate_id',
                'certificate_type' => 'c.type',
                'certificate_end' => 'c.enddate',
                'end' => 'g.end',
                'mark' => new Zend_Db_Expr("(CASE WHEN m.mark != '1' OR m.mark IS NULL THEN 0 ELSE m.mark END)")
            )
        )->joinLeft(array('d'  => 'structure_of_organ'), 'd.mid = p.MID', array()
        )->joinLeft(array('d2' => 'structure_of_organ'), 'd.owner_soid = d2.soid', array()
        )->joinLeft(array('d3' => 'structure_of_organ'), 'd3.soid = d2.owner_soid', array()
        )->joinInner(array('g' => 'graduated'), 'g.MID = p.MID', array()
        )->joinLeft(array('s'  => 'subjects'), 's.subid = g.CID', array()
        )->joinLeft(array('m'  => 'courses_marks'), 'm.mid = p.MID AND m.cid = g.cid', array()
        )->joinLeft(array('c'  => 'certificates'), 'c.user_id = p.MID AND c.subject_id = g.cid', array()
        )->joinLeft(array('f' => 'files'), '(c.certificate_id = f.item_id AND f.item_type = \''. HM_Files_FilesModel::ITEM_TYPE_CERTIFICATE .'\')', array()
        )->where('s.is_labor_safety = ?', $isLaborSafety ? 1 : 0
        )->group(array(
            'p.MID',
            'p.LastName',
            'p.FirstName',
            'p.Patronymic',
            'd.soid',
            'd2.name',
            's.name',
            's.subid',
            'g.end',
            'm.mark',
            'f.file_id',
            'c.certificate_id',
            'c.type',
            'c.enddate',
        ));

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL)) {
            $subjectsScope = $this->getService('Responsibility')->fetchAll(
                array(
                    'user_id = ?' => $this->getService('User')->getCurrentUserId(),
                    'item_type = ?' => HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT_OT
                )
            )->getList('item_id');

            if (count($subjectsScope)) {
                $select->where('s.subid IN (?)', $subjectsScope);
            }

        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            $allSlaves = $this->getService('Supervisor')->getSlaves($this->getService('User')->getCurrentUserId());
            $select->where('p.MID IN (?)', count($allSlaves) ? $allSlaves : array());
        }

        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("d.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $cardName = _('Карточка учебного курса');
        $url = array('baseUrl' => '', 'module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => '{{subject_id}}');

        $columns = array(
            'MID' => array('hidden' => true),
            'subject_id' => array('hidden' => true),
            'file_id' => array('hidden' => true),
            'notempty' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' => $this->view->cardLink(
                    $this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'list',
                            'action' => 'view',
                            'user_id' => '',
                            'baseUrl' => '',
                        ),
                        null, true).
                    '{{MID}}',_('Карточка пользователя')).
                    '<a href="'.$this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'report',
                            'user_id' => '',
                            'baseUrl' => '',
                        ),
                        null, true).
                    '{{MID}}'.'">'.'{{fio}}</a>',
                'position' => 1
            ),
            'positions' => array(
                'title' => _('Должность'),
                'callback' => array(
                    'function' => array($this, 'departmentsCache'),
                    'params' => array('{{positions}}', $select, true)
                ),
                'position' => 2
            ),
            'department' => array(
                'title' => _('Подразделение'),
                'position' => 3
            ),
            'subject' => array(
                'title' => _('Название курса'),
                'decorator' => $this->view->cardLink(
                    $this->view->url(
                        array(
                            'baseUrl' => '',
                            'module' => 'subject',
                            'controller' => 'list',
                            'action' => 'card',
                            'subject_id' => ''
                        )
                    ) . '{{subject_id}}', $cardName) . ' <a href="' .
                    $this->view->url(
                        $url, null, true, false) . '">{{subject}}</a>',
                'position' => 4
            ),
            'certificate_type' => array(
                'title' => _('Вид документа'),
                'callback' => array('function' => array($this, 'updateCertificateType'), 'params' => array('{{certificate_type}}')),
                'position' => 5
            ),
            'certificate_id' => array(
                'title' => _('Номер документа'),
                'position' => 6
            ),
            'end' => array(
                'title' => _('Дата прохождения'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'position' => 7
            ),
            'certificate_end' => array(
                'title' => _('Дата истечения сертификата'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'position' => 8
            ),
            'mark' => array('hidden' => true),
//            array(
//                'title' => _('Результат прохождения'),
//                'callback' => array(
//                    'function' => array($this, 'updateMark'),
//                    'params' => array('{{mark}}')
//                ),
//                'position' => 6
//            ),
        );

        $filters = array(
            'MID' => null,
            'fio' => null,
            'positions' => null,
            'department' => array('render' => 'department'),
            'subject' => null,
            'end' => array('render' => 'DateSmart'),
            'certificate_type' => array('values' => HM_Certificates_CertificatesModel::getCertificateTypes()),
            'certificate_id' => null,
            'certificate_end' => array('render' => 'DateSmart'),
            'mark' => array(
                'values' => array(
                    HM_Subject_Mark_MarkModel::MARK_BAD,
                    HM_Subject_Mark_MarkModel::MARK_GOOD
                )
            ),
        );

        $s = $select->__toString();

        $grid = $this->getGrid(
            $select,
            $columns,
            $filters,
            'grid'
        );

        $grid->updateColumn('certificate_id', array('callback' => array('function' => array($this,'updateCertificateNumber'),
            'params' => array('{{certificate_id}}', '{{file_id}}'))));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function updateMark($mark)
    {
        return $mark == 1 ? HM_Subject_Mark_MarkModel::MARK_GOOD : HM_Subject_Mark_MarkModel::MARK_BAD;
    }
}