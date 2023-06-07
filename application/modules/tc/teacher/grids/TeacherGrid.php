<?php

use HM_Grid_ColumnCallback_Tc_TeacherCardLink  as TeacherCardLink;
use HM_Grid_ColumnCallback_Tc_ProviderCardLink as ProviderCardLink;
use HM_Grid_ColumnCallback_Tc_SubjectsList     as SubjectsList;

class HM_Teacher_Grid_TeacherGrid extends HM_Grid
{
    protected static $_defaultOptions = array(
        'courseId'     => 0,
        'providerId'   => 0,
        'defaultOrder' => 'teacher_name_ASC'
    );

    protected function _initColumns()
    {
        $teacherCardLink = new TeacherCardLink();

        if ($this->isSubjectContext()) {
            $teacherCardLink->setSubjectId($this->getCourseId());
        } elseif ($this->isProviderContext()) {
            $teacherCardLink->setProviderId($this->getProviderId());
        }

        $this->_columns = array(
            'teacher_id' => array('hidden' => true),
            'provider_id' => array('hidden' => true),
            'teacher_name' => array(
                'title' => _('ФИО'),
                'callback' => $teacherCardLink->getCallback('{{teacher_id}}', '{{teacher_name}}')
            ),
            'provider_name' => array('hidden' => true),
            'created_by'    => array('hidden' => true),
            'contacts' => array(
                'title' => _('Контактная информация'),
            ),
            'courses' => array('hidden' => true),
            'rating' => array(
                'title' => _('Рейтинг'),
                'callback' => array(
                    'function' => array($this, 'updateRating'),
                    'params'   => array('{{rating}}'))
            ),
        );

        if (!$this->getProviderId() && !$this->isSubjectContext()) {

            $providerCardLink = new ProviderCardLink();

            $this->_columns['provider_name'] = array(
                'title' => _('Провайдер'),
                'callback' => $providerCardLink->getCallback('{{provider_id}}', '{{provider_name}}')
            );

        }

        if (!$this->isSubjectContext()) {

            $subjectList = new SubjectsList($this, 'courses');

            $this->_columns['courses'] = array(
                'title' => _('Курсы'),
                'callback' => $subjectList->getCallback('{{courses}}')
            );

        }
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'teacher_name' => null,
            'provider_name' => null,
        ));

        $filters->addFromColumnCallbacks(array(
            'courses'
        ));

    }

    protected function _initActions(HM_Grid_ActionsList $actions)
    {
        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            return;
        }

        $actions
            ->add('edit', array(
                'module'     => 'teacher',
                'controller' => 'edit',
                'action'     => 'edit',
            ))
            ->setParams(array(
                'teacher_id'
            ));

        $actions
            ->add('delete', array(
                'module'     => 'teacher',
                'controller' => 'edit',
                'action'     => 'delete',
            ))
            ->setParams(array(
                'teacher_id'
            ));

    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            return;
        }

        $massActions->add(
            array(
                'module'     => 'teacher',
                'controller' => 'edit',
                'action'     => 'delete-by',
            ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );
    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))
            && ($row['created_by'] != $this->getService('User')->getCurrentUserId())) {
            $actions->setInvisibleActions(array(
                'edit', 'delete'
            ));
        }
    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        $menu->addItem(array(
            'urlParams' => array(
                'controller' => 'edit',
                'action' => 'new'
            ),
            'title' => _('Создать тьютора')
        ));

        if ($this->isSubjectContext()) {
            $menu->addItem(array(
                'urlParams' => array(
                    'controller' => 'assign',
                    'action'     => 'index',
                    'subject_id' => $this->getCourseId()
                ),
                'title' => _('Назначить тьюторов на курс')
            ));
        }

    }

    public function isSubjectContext()
    {
        return ($this->getCourseId() > 0);
    }

    public function isProviderContext()
    {
        return ($this->getProviderId() > 0);
    }

    public function getCourseId()
    {
        return $this->_options['courseId'];
    }

    public function getProviderId()
    {
        return $this->_options['providerId'];
    }

    public function getGridId()
    {
        $gridId     = parent::getGridId();
        $courseId   = $this->getCourseId();
        $providerId = $this->getProviderId();

        return "{$gridId}_{$courseId}_{$providerId}";

    }

    public function updateRating($rating)
    {
        return $rating ? number_format((double)($rating), 1, '.', ' ') : '';
    }
}