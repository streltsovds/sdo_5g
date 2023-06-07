<?php

use HM_Grid_ColumnCallback_Tc_ProviderCardLink     as ProviderCardLink;
use HM_Grid_ColumnCallback_Tc_TeachersList         as TeachersList;
use HM_Grid_ColumnCallback_Els_CitiesList          as CitiesList;
use HM_Grid_ColumnCallback_Els_TagsList            as TagsList;
use HM_Grid_ColumnCallback_Tc_SubjectsList         as SubjectsList;
use HM_Grid_ColumnCallback_Tc_ProviderContactsList as ProviderContactsList;

class HM_Provider_Grid_ProviderGrid extends HM_Grid
{
    const SWITCHER_ALL = 'all';
    const SWITCHER_ONLY_MY = 'only_my';

    protected static $_defaultOptions = array(
        'providerId'     => 0,
        'defaultOrder' => 'provider_name_ASC'
    );

    public function init($source = null)
    {
        parent::init($source);

        if ($this->getProviderId()) {
            $this->_grid->setClassRowCondition("'{{provider_id}}' != ''", "success");
        }
    }

    protected function _initColumns()
    {
        $providerCardLink = new ProviderCardLink();
        $teachersList     = new TeachersList($this, 'teachers');
        $citiesList       = new CitiesList($this, 'classifiers');
        $tagsList         = new TagsList($this, 'tags');
        $subjectList      = new SubjectsList($this, 'courses');
        $contactsList     = new ProviderContactsList($this, 'contacts');

        $this->_columns = array(
            'provider_id' => array('hidden' => true),
            'created_by'  => array('hidden' => true),
            'provider_name' => array(
                'title' => _('Название'),
                'callback' => $providerCardLink->getCallback('{{provider_id}}', '{{provider_name}}'),
            ),
            'status' => array(
                'title' => _('Утвержден'),
                'callback' => array(
                    'function'=> array($this, 'updateStatus'),
                    'params'=> array('{{status}}'))
            ),
            'classifiers' => array('hidden' => true),
//            array(
//                'title' => _('Город'),
//                'callback' => $citiesList->getCallback('{{classifiers}}')
//            ),
            'courses' => array(
                'title' => _('Курсы'),
                'callback' => $subjectList->getCallback('{{courses}}')
            ),
            'contacts' => array('hidden' => true),
//            array(
//                'title' => _('Контактные лица'),
//                'callback' => $contactsList->getCallback('{{contacts}}')
//            ),
            'teachers' => array(
                'title' => _('Тьюторы'),
                'callback' => $teachersList->getCallback('{{teachers}}')
            ),
            'graduated_count' => array(
                'title' => _('Количество обученных'),
            ),
            'tags' => array(
                'title' => _('Метки'),
                'callback' => $tagsList->getCallback('{{tags}}')
            ),
            'rating' => array(
                'title' => _('Рейтинг'),
                'callback' => array(
                    'function' => array($this, 'updateRating'),
                    'params'   => array('{{rating}}'))
            ),
        );
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'provider_name' => null,
            'status'        => array('values' => HM_Tc_Provider_ProviderModel::getStatusesShort()),
        ));

        $filters->addFromColumnCallbacks(array(
            'classifiers',
            'contacts',
            'teachers',
            'courses',
            'tags'
        ));
    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {
        if ($this->currentUserIs(array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            return;
        }

        $actions
            ->add('edit', array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'edit',
            ))
            ->setParams(array(
                'provider_id'
            ));

        $actions
            ->add('delete', array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'delete',
            ))
            ->setParams(array(
                'provider_id'
            ));

    }

    public function checkActionsList($row, HM_Grid_ActionsList $actions)
    {
        if ($row['provider_id'] == HM_Tc_Provider_ProviderModel::HARDCODED_ID_INTERNAL_STUDY) {
            $actions->setInvisibleActions(array(
                'edit', 'delete'
            ));
        }

        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $actions->setInvisibleActions(array(
                'edit', 'delete'
            ));
        }

        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))
            && ($row['created_by'] != $this->getService('User')->getCurrentUserId())) {
            $actions->setInvisibleActions(array(
                'edit', 'delete'
            ));
        }
    }

    protected function _initSwitcher(HM_Grid_Switcher $switcher)
    {
        if ($this->currentUserIs(array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $switcher->add(_('всех провайдеров'), self::SWITCHER_ALL);
            $switcher->add(_('провайдеров, созданых мной'), self::SWITCHER_ONLY_MY);
        }
    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {
        if ($this->currentUserIs(array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            return;
        }

        $massActions->add(
            array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'delete-by',
            ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $massActions->add(
            array(
                'module'     => 'provider',
                'controller' => 'list',
                'action'     => 'approve',
            ),
            _('Утвердить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );
    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {
        $menu->addItem(array(
            'urlParams' => array(
                'controller' => 'list',
                'action' => 'new'
            ),
            'title' => _('Создать провайдера')
        ));
    }

    public function getProviderId()
    {
        return $this->_options['providerId'];
    }

    public function getGridId()
    {
        $gridId = parent::getGridId();
        $courseId = $this->getProviderId();

        if (!$courseId) {
            return $gridId;
        }

        return $gridId.$courseId;

    }


    public function updateRating($rating)
    {
        $percent = round($rating * 100);
        return $percent.'%';
    }


    public function updateStatus($status)
    {
        return HM_Tc_Provider_ProviderModel::getStatusShort($status);
    }

} 