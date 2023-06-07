<?php

class HM_View_Infoblock_SubjectsSliderBlock extends HM_View_Infoblock_Abstract
{
    const MAX_ITEMS = 10;

    protected $id = 'subjectssliderblock';

    public function subjectsSliderBlock($param = null)
    {
        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        $featuredSubjects = $subjectService->fetchAll(['in_slider = ?' => 1]);

        // скопировано из subject/catalog
        // ВНИМАНИЕ! в моделях будут доступны только эти свойства, т.к. нестандартный fetchAll
        /** @var Zend_Db_Select $select */
        $select = $subjectService->getSelect();
        $select->from(['s' => 'subjects'],
            [
                'subid' => 's.subid',
                'name' => 's.name',
                'base_id' => 's.base_id',
                'base_color' => 's.base_color',
                'type' => 's.type',
                'reg_type' => 's.reg_type',
                'price' => 's.price',
                'begin' => 's.begin',
                'end' => 's.end',
                'longtime' => 's.longtime',
                'claimant_process_id' => 's.claimant_process_id',
                // 'classes' => '', // если классификаторы объёмные - здесь может тормозить
                'classes' => new Zend_Db_Expr('GROUP_CONCAT(class.name)'),
                'created' => 's.created',
                'period'  => 's.period'
        ])
            ->joinLeft(['c' => 'classifiers_links'], 's.subid = c.item_id AND c.type = ' . (int)HM_Classifier_Link_LinkModel::TYPE_SUBJECT, [])
            ->joinLeft(['class' => 'classifiers'], 'c.classifier_id = class.classifier_id', [])
            ->order(['s.name'])
            ->group([
                's.subid',
                's.name',
                's.reg_type',
                's.type',
                's.claimant_process_id',
                's.base_id',
                's.base_color',
                's.price',
                's.begin',
                's.end',
                's.longtime',
                's.created',
                's.period',
            ]);

        if (count($featuredSubjects)) {
            $subjectIds = $featuredSubjects->getList('subid', 'subid');
            $select->where('subid IN (?)', $subjectIds);
        } else {
            $select->where('s.reg_type = ?', HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN)
                ->where($subjectService->quoteInto(
                    [
                        's.period IN (?) OR ',
                        's.period_restriction_type = ? OR ',
                        '(s.period_restriction_type = ?', ' AND (s.state = ? ', ' OR s.state = ? OR s.state is null) ) OR ',
                        '(s.period = ? AND ',
                        's.end > ?)',
                    ],
                    [
                        [HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED],
                        HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                        HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                        HM_Subject_SubjectModel::STATE_ACTUAL,
                        HM_Subject_SubjectModel::STATE_PENDING,
                        HM_Subject_SubjectModel::PERIOD_DATES,
                        $subjectService->getDateTime()
                    ]
                ));
        }

        if ($userId = (int)$userService->getCurrentUserId()) {
            $select->joinLeft(['st' => 'Students'], 's.subid = st.CID AND st.MID = ' . $userId, [])
                ->where('st.CID IS NULL');
            $select->joinLeft(['cl' => 'claimants'], 's.subid = cl.CID AND cl.status = ' . HM_Role_ClaimantModel::STATUS_NEW . ' AND cl.MID = ' . $userId, [])
                ->where('cl.CID IS NULL');
        }
        $rows = $select->query()->fetchAll();

        if (count($rows) > self::MAX_ITEMS) {
            $rows = array_slice($rows, 0, self::MAX_ITEMS);
            $this->view->limited = true;
        }

        $subjects = $subjectService->getMapper()->fetchAllFromArray($rows);

        $isGuest = $acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_GUEST);

        /** @var HM_Subject_SubjectModel $subject */
        foreach ($subjects as $subject) {

            $subject->teacherPhoto = '';
            $subject->teacherUrl = '';

            // Гостю всё равно не покажет личные данные из карточки
            if(!$isGuest) {

                /** @var HM_User_UserModel $teacher */
                $teacher = $subjectService->getAssignedTeachers($subject->subid)->current();

                if (!empty($teacher)) {
                    $subject->teacherPhoto = !empty($teacher->getPhoto()) ? $teacher->getPhoto() : $teacher->getDefaultPhoto();
                    $subject->teacherUrl = $this->view->url(['module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $teacher->MID]);
                }
            }

            $subject->new = $subject->isNew();
            $subject->userIcon = $subject->getUserIcon();
            $subject->defaultIcon = $subject->getDefaultIcon();
            $subject->viewUrl = $this->view->url($subject->getViewUrl());
            $subject->begin = $subject->getBegin();
            $subject->end = $subject->getEnd();
            $subject->classifiers = array_filter(explode(',', $subject->classes));

            $indexUrl = urlencode($this->view->url(['module' => 'default', 'controller' => 'index', 'action' => 'index'], null, true));

            $isStudent = $this->is('student', $userService->getCurrentUserId(), $subject->subid);
            $isClaimant = $this->is('claimant', $userService->getCurrentUserId(), $subject->subid);

            $o = new stdClass();
            if ($isStudent) {
                $o->text = _('Курс назначен');
                $o->isButton = false;
            } elseif ($isClaimant) {
                $o->text = _('Заявка на рассмотрении');
                $o->isButton = false;
            } elseif (!$isStudent && !$isClaimant) {
                if ($subject->claimant_process_id) {
                    $o->text = _('Подать заявку');
                } else {
                    $o->text = _('Записаться');
                }
                $o->isButton = true;
            }

            $o->href = $this->view->url([
                'module' => 'user',
                'controller' => 'reg',
                'action' => 'subject',
                'subid' => $subject->subid,
                'redirect' => $indexUrl
            ], null, true);

            $subject->regStatus = $o;
        }

        $this->view->subjects = HM_Json::encodeErrorSkip($subjects->asArrayOfArrays());
        $this->view->showEditLink = $this->getService('Activity')->isUserActivityPotentialModerator(
            $userService->getCurrentUserId()
        );

        $content = $this->view->render('subjectsSliderBlock.tpl');
        return $this->render($content);

    }

    protected function is($role, $userId, $subjectId)
    {
        if (!$userId) return false;
        $sum = 'SUM(claim.status)';

        if (false === strpos(Zend_Registry::get('config')->resources->db->adapter, 'mysql')) {
            $sum = 'SUM(CAST(claim.status AS INT))';
        }

        $subjectsSelect = $this->getService('Subject')->getSelect()
            ->from(
                ['s' => 'subjects'],
                ['s.subid']
            )
            ->joinLeft(
                ['st' => 'Students'],
                'st.CID = s.subid and st.MID = ' . $userId,
                ['isStudent' => new Zend_Db_Expr('CASE WHEN GROUP_CONCAT(st.SID) <> \'\' THEN 1 ELSE 0 END')]
            )
            ->joinLeft(
                ['claim' => 'claimants'],
                'claim.CID = s.subid and claim.MID = ' . $userId,
                ['isClaimant' => new Zend_Db_Expr("CASE WHEN ((GROUP_CONCAT(claim.SID) <> '') AND (" . $sum . " = 0)) THEN 1 ELSE 0 END")]
            )
            ->group(['s.subid'])
            ->where('s.subid = ?', $subjectId)
            ->where('s.reg_type <> ?', 2);

        $s = $subjectsSelect->__toString();
        $subjects = $subjectsSelect->query()->fetchAll();

        if ($role == 'student') return $subjects[0]['isStudent'];
        if ($role == 'claimant') return $subjects[0]['isClaimant'];
        return false;
    }
}