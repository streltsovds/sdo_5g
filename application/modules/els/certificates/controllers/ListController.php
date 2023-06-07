<?php
class Certificates_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function indexAction ()
    {
        $select = $this->getService('Certificates')->getSelect();
        $select->from(array('c' => 'certificates'),
            array(
                'c.enddate',
                'g.end',
                'p.MID',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'position'    => 'o.name' ,
                'position_id' => 'o.soid' ,
                'subject'     => 's.name' ,
                'subject_id'  => 's.subid'
            )
        )
            ->joinInner(array('g' => 'graduated'), 'c.certificate_id = g.certificate_id', array())
            ->joinLeft(array('p' => 'People'            ), 'p.MID   = c.user_id'   , array())
            ->joinLeft(array('o' => 'structure_of_organ'), 'o.mid   = p.MID'       , array())
            ->joinLeft(array('s' => 'subjects'          ), 's.subid = c.subject_id', array())
            ->where('c.enddate IS NOT NULL')
            ->order('enddate ASC');

        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("o.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $cardName = _('Карточка учебного курса');
        $url = array('baseUrl' => '', 'module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => '{{subject_id}}');

        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),
                'position_id' => array('hidden' => true),
                'subject_id'  => array('hidden' => true),
                'fio' => array(
                    'title'    => _('ФИО'),
                    'decorator' =>  $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{MID}}') . ' <a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{MID}}' . '">' . '{{fio}}</a>',
                    'position' => 1
                ),
                'position' => array(
                    'title' => _('Должность'),
                    'decorator' => $this->view->cardLink(
                            $this->view->url(
                                array(
                                    'module' => 'orgstructure',
                                    'controller' => 'list',
                                    'action' => 'card',
                                    'baseUrl' => '',
                                    'org_id' => ''
                                )) . '{{position_id}}',
                            HM_Orgstructure_OrgstructureService::getIconTitle(HM_Orgstructure_OrgstructureModel::TYPE_POSITION),
                            'icon-custom',
                            'pcard',
                            'pcard',
                            'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass(HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
                        ) . '{{position}}',
                    'position' => 2,
                ),
                'subject' => array(
                    'title' => _('Курс'),
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
                    'position' => 3
                ),
                'end' => array(
                    'title' => _('Дата прохождения'),
                    'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                    'position' => 4
                ),
                'enddate' => array(
                    'title' => _('Дата истечения сертификата'),
                    'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                    'position' => 5
                )
            ),
            array(
                'fio'      => null,
                'position' => array('render' => 'Department'),
                'subject'  => null,
                'end'  => array('render' => 'Date'),
                'enddate'  => array('render' => 'Date'),
            )
        );

        $this->view->grid = $grid;
    }
}
