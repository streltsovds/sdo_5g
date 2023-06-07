<?php
class HM_At_Profile_ProfileService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        $data['shortname'] = $data['shortname'] ?: $data['name'];

        $profile = parent::insert($data);       
        $this->assignProgramms($profile);
           
        return $profile;
    }    

    public function update($data, $unsetNull = true)
    {
        $profile = parent::update($data);
        $this->updateProgramms($profile);

        return $profile;
    }

    public function updateProgramms($profile)
    {
        $programms = $this->getService('Programm')->fetchAll(array(
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,
            'item_id = ?' => $profile->profile_id,
        ));

        if (count($programms)) {
            foreach ($programms as $programm) {
                $this->getService('Programm')->update(array(
                    'programm_id' => $programm->programm_id,
                    'name' => HM_Programm_ProgrammModel::getProgrammTitle($programm->programm_type, HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $profile->name),
                ));
            }
        }
    }

    public function assignProgramms($profile)
    {
        $return = array();
        
        $this->getService('Programm')->deleteBy(array(
            'item_id = ?' => $profile->profile_id,
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,
        ));

        $categoryId = $profile->category_id;
        if (!empty($categoryId)) {
            $programms = $this->getService('Programm')->fetchAll(array(
                'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY,       
                'item_id = ?' => $profile->category_id,       
            ));
        }
        
        if (count($programms)) {
            // если программы созданы на уровне категории должности - копируем их со всеми event'ами
            foreach ($programms as $programm) {
                
                $return[$programm->programm_type] = $this->getService('Programm')->copy($programm, array(
                    'name' => HM_Programm_ProgrammModel::getProgrammTitle($programm->programm_type, HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $profile->shortname),
                    'item_id' => $profile->profile_id,
                    'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,
                ));
            }
        } else {
            // если категории не используются или по каким-то причинам у категорий не созданы програмыы - создаём заново
            foreach (HM_Programm_ProgrammModel::getTypes(false) as $programmType => $title) {
                $return[$programmType] = $this->getService('Programm')->insert(array(
                    'programm_type' => $programmType,
                    'name' => HM_Programm_ProgrammModel::getProgrammTitle($programmType, HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $profile->name),
                    'item_id' => $profile->profile_id,
                    'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,
                ));
            }            
        }
        return $return;        
    }

    public function assignProgrammFromCategory($profile, $programm)
    {
        // если программа не уровня категории, то сразу выходим
        if ($programm->item_type != HM_Programm_ProgrammModel::ITEM_TYPE_CATEGORY) return false;

        // старая программа такого типа, подключенная к профилю
        $oldProgramm = $this->getService('Programm')->getOne($this->getService('Programm')->fetchAll(array(
            'programm_type = ?' => $programm->programm_type,
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,
            'item_id = ?' => $profile->profile_id
        )));

        if ($oldProgramm) {
            $programmEvents = $this->getService('ProgrammEvent')->fetchAllDependence('ProgrammEventUser', array('programm_id = ?' => $oldProgramm->programm_id));
            foreach ($programmEvents as $programmEvent) {
                $this->getService('ProgrammEvent')->deleteEvent($programmEvent);
            }
            $this->getService('Programm')->delete($oldProgramm->programm_id);
        }

        // новая программа ,скопированная из категории
        $newProgramm = $this->getService('Programm')->copy($programm, array(
            'name' => HM_Programm_ProgrammModel::getProgrammTitle($programm->programm_type, HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $profile->name),
            'item_id' => $profile->profile_id,
            'item_type' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,
        ));

        // аккуратно отсоединяем и заново назначаем юзеров
        if (count($profile->positions)) {
            foreach ($profile->positions as $position) {
                if ($position->mid) {
                    $this->getService('ProgrammUser')->unassign($position->mid, $oldProgramm->programm_id);
                    $this->getService('Programm')->assignToUser($position->mid, $newProgramm->programm_id);
                }
            }
        }

        return $newProgramm;
    }



    public function delete($profileId)
    {
        $collection = $this->getService('Programm')->fetchAll(array(
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,       
            'item_id = ?' => $profileId,       
        ));        
        if (count($collection)) {
            foreach ($collection as $programm) {
                $this->getService('Programm')->delete($programm->programm_id);
            }
        }
         $this->getService('AtEvaluation')->deleteBy(array('profile_id = ?' => $profileId));
        return parent::delete($profileId);
    } 

    public function individualize(&$profile, $entityId, $entity = 'user')
    {
        $data = $profile->getValues();
        
        $oldProfileId = $data['profile_id'];      
        unset($data['profile_id']);   
        
        switch ($entity) {
            case 'user':
                $data['user_id'] = $entityId;
                $this->deleteBy(array('user_id = ?' => $entityId));
                $profile = parent::insert($data);
                $this->getService('Orgstructure')->updateWhere(array(
                    'profile_id' => $profile->profile_id
                ), array(
                    'mid = ?' => $entityId,
                    'profile_id = ?' => $oldProfileId,
                ));
                break;
            case 'vacancy':
                $data['vacancy_id'] = $entityId;
                $this->deleteBy(array('vacancy_id = ?' => $entityId));
                $profile = parent::insert($data);
                $this->getService('RecruitVacancy')->updateWhere(array(
                    'profile_id' => $profile->profile_id
                ), array(
                    'vacancy_id = ?' => $entityId
                ));
                break;
            default:
                throw new Exception(_('Неизвестный тип сущности для индивидуализации профиля'));
        }
                
        // копировать настройки оценки из общего профиля
        $evaluations = $this->getService('AtEvaluation')->fetchAll(array(
            'profile_id = ?' => $oldProfileId,        
        ));
        foreach ($evaluations as $evaluation) {
            $data = $evaluation->getValues();
            $data['profile_id'] = $profile->profile_id;
            unset($data['evaluation_type_id']);
            $this->getService('AtEvaluation')->insert($data);
        }
        
        return true;
    }

    public function getProfileSpecialities($profileId, $type = HM_Classifier_Type_TypeModel::BUILTIN_TYPE_SPECIALITIES)
    {
        if (!$profileId) return false;

        $result = array('all' => array(), 'profile' => array());

        $where = $this->quoteInto('type=?', $type);
        $allClassifiers = $this->getService('Classifier')->fetchAll($where, 'name')->getList('classifier_id', 'name');

        if ( count($allClassifiers) ) {

            $result['all'] = $allClassifiers;

            $where = $this->quoteInto(
                array(
                    'item_id = ?',
                    ' AND classifier_id IN (?)',
                    ' AND type = ?'
                ),
                array(
                    $profileId,
                    array_keys($allClassifiers),
                    $type
                )
            );

            $profileSpec = $this->getService('ClassifierLink')->fetchAll($where)->getList('classifier_id');
            if ( count($profileSpec) ) {
                $result['profile'] = array_intersect_key($allClassifiers, $profileSpec);
            }
        }

        return $result;
    }

    public function getProfileClassifiers($profileId, $type = HM_Classifier_Type_TypeModel::BUILTIN_TYPE_SPECIALITIES)
    {
	$return = array();
        if (!$profileId) return false;

        $where = $this->quoteInto(
            array(
                'item_id = ?',
                ' AND type = ?'
            ),
            array(
                $profileId,
                $type
            )
        );

        $collection = $this->getService('ClassifierLink')->fetchAllDependence('Classifier', $where);
        if (count($collection)) {
		foreach ($collection as $classifierLink) {
			if (count($classifierLink->classifiers)) {
				$classifier = $classifierLink->classifiers->current();
				$return[$classifier->classifier_id] = $classifier->name;

			}
		}
        }

        return $return;
    }

    
    public function assign($profileId, $soids)
    {
        $positions = $this->getService('Orgstructure')->fetchAllDependence('Profile', array('soid IN (?)' => $soids));
        if (count($positions)) {
            foreach ($positions as $position) {
                $this->assignPosition($profileId, $position);
            }
        }
//         $this->getService('AtKpiProfile')->assignUserKpisByProfile($profileId);
        return true;
    }
    
    public function assignPosition($profileId, $position, $assignEvents = true)
    {
        // при смене профиля удаляем все старые программы
        $this->unassignUser($position->mid, $position->profile_id);

        $profile = $this->getService('AtProfile')->find($profileId);
        $isManager = 0;
        if (count($profile)) {
            $profile = $profile->current();
            $isManager = $profile->is_manager;
        }

        $this->getService('Orgstructure')->update(array(
            'profile_id' => $profileId,
            'soid' => $position->soid,
            'is_manager' => $isManager
        ));

        if ($position->mid) {

            $isSupervisor = $this->getService('User')->isRoleExists($position->mid, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR);
            
            if ($isManager && !$isSupervisor) {
                $this->getService('Supervisor')->assign($position->mid);
            }

            $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $profileId);
            foreach ($programms as $programm) {
                // в ГСП программы нач.обучения назначались только через адаптацию
                // if ($programm->programm_type == HM_Programm_ProgrammModel::TYPE_ELEARNING) continue;
                $this->getService('Programm')->assignToUser($position->mid, $programm->programm_id, $assignEvents);
            }
        }
    }
    
    // когда в существующую должность с профилем назначают нового юзера
    public function assignPositionUser($position)
    {
        if ($position->mid && $position->profile_id) {
            $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $position->profile_id);
            foreach ($programms as $programm) {
                $this->getService('Programm')->assignToUser($position->mid, $programm->programm_id);
            }
        }        
    }
    
    public function unassign($soids)
    {
//         $this->getService('AtKpiProfile')->unassignUserKpisBySoids($soids);
        
        $data = array('profile_id' => 0);
        $this->getService('Orgstructure')->updateWhere($data, array(
            'soid IN (?)' => $soids,
        ));    
                
        $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $profileId);
        $positions = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $soids));
        if (count($positions)) {
            foreach ($positions as $position) {
                foreach ($programms as $programm) {
                    if ($position->mid) {
                        $this->getService('ProgrammUser')->unassign($position->mid, $programm->programm_id);
                    }
                }
            }
        }
        return true;
    }
    
    public function unassignUser($mids, $profileId = false)
    {
//         $this->getService('AtKpiProfile')->unassignUserKpis($mids);
        $mids = is_array($mids) ? $mids : [$mids];

        $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $profileId);
        if (count($mids)) {
            foreach ($mids as $mid) {
                foreach ($programms as $programm) {
                    if ($mid) {
                        $this->getService('ProgrammUser')->unassign($mid, $programm->programm_id);
                    }
                }
            }
        }
        return true;
    }

    public function getRequirements4Report($profileId)
    {
        $select = $this->getService('AtProfile')->getSelect();
        $select->from(
            array('p'=>'at_profile_function'),
            array('standard'=>'at_ps_standard.name')
        )->joinLeft(
             array('at_ps_function'),
            'at_ps_function.function_id=p.function_id',
            array('[function]'=>'name')
        )->joinLeft(
             array('at_ps_standard'),
            'at_ps_standard.standard_id=at_ps_function.standard_id',
            array('standard'=>'name')
        )->joinLeft(
             array('at_ps_requirement'),
            'at_ps_requirement.function_id=at_ps_function.function_id',
            array('requirement_type1'=>new Zend_Db_Expr("GROUP_CONCAT(case when at_ps_requirement.type=".HM_At_Standard_Function_FunctionModel::TYPE_EDUCATION." then (at_ps_requirement.name) else null END)"),
                'requirement_type2'=>new Zend_Db_Expr("GROUP_CONCAT(case when at_ps_requirement.type=".HM_At_Standard_Function_FunctionModel::TYPE_WORKING." then REPLACE(at_ps_requirement.name, ',', '&comma;') else null END)"),
                'requirement_type3'=>new Zend_Db_Expr("GROUP_CONCAT(case when at_ps_requirement.type=".HM_At_Standard_Function_FunctionModel::TYPE_SPECIAL." then REPLACE(at_ps_requirement.name, ',', '&comma;') else null END)"),
                'requirement_type4'=>new Zend_Db_Expr("GROUP_CONCAT(case when at_ps_requirement.type=".HM_At_Standard_Function_FunctionModel::TYPE_SGC_EDUCATION." then REPLACE(at_ps_requirement.name, ',', '&comma;') else null END)"),
                'requirement_type5'=>new Zend_Db_Expr("GROUP_CONCAT(case when at_ps_requirement.type=".HM_At_Standard_Function_FunctionModel::TYPE_SGC_WORKING." then REPLACE(at_ps_requirement.name, ',', '&comma;') else null END)")
        )
        )->where('p.profile_id=?', $profileId
        )->group(array('at_ps_function.function_id', 'at_ps_standard.name','at_ps_function.name'));

        $results = $select->query()->fetchAll();
        foreach($results as &$result) {

            $i = 0;
            foreach($result as &$req) {
                $i++;
                if($i<=2) continue;
                $req = trim($req) ? '<li>'.str_replace(',', '<li>', $req) : '';
            }
        }

        return $results;
    }

    public function setBaseProfile($profile, $baseProfileId)
    {
        $profile->base_id = $baseProfileId;
        $this->update($profile->getData());

        // переназначить профиль должностям
        $positions = $this->getService('Orgstructure')->fetchAll(array(
            'profile_id = ?' => $profile->profile_id
        ));

        foreach ($positions as $position) {

            $position->original_profile_id = $position->profile_id; // на случай если потом вернуть
            $this->getService('Orgstructure')->update($position->getData());

            if ($baseProfileId) {
                $this->unassign(array($position->soid));
                $this->assign($baseProfileId, array($position->soid));
            }
        }
    }

    public function unsetBaseProfile($profile)
    {
        $profile->base_id = false;
        $this->update($profile->getData());

        // вернуть профиль должностям
        $positions = $this->getService('Orgstructure')->fetchAll(array(
            'original_profile_id = ?' => $profile->profile_id
        ));

        foreach ($positions as $position) {

            $originalProfileId = $position->original_profile_id;
            $position->original_profile_id = false;
            $this->getService('Orgstructure')->update($position->getData());

            $this->unassign(array($position->soid));
            $this->assign($originalProfileId, array($position->soid));
        }
    }

    public static function updateIcon($profileId, $photo, $destination = null, $skipResize = false, $removeIcon = 0)
    {
        if (empty($destination)) {
            $destination = HM_At_Profile_ProfileModel::getIconFolder($profileId);
            $isSubject = true;
        } else {
            $isSubject = false;
        }
        $w = HM_At_Profile_ProfileModel::THUMB_WIDTH;
        $h = HM_At_Profile_ProfileModel::THUMB_HEIGHT;

        $path = rtrim($destination, '/') . '/' . $profileId . '.jpg';

        if ($removeIcon) {
            unlink($path);
            return true;
        }

        if (is_null($photo)) return false;

        if ($photo instanceof HM_Form_Element_ServerFile) {
            $photoVal = $photo->getValue();
            //если инпут пустой - удаляем текущее изображение
            if (empty($photoVal)) {
                unlink($path);
                return true;
            }
            $original = APPLICATION_PATH . '/../public' . $photoVal;
            //если новая картинка = старой, то ничего не меняем
            if (md5_file($original) == md5_file($path)) {
                return true;
            }
            if ($skipResize) {
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);
            $img->save($path);
        } elseif ($photo->isUploaded()){
            $original = rtrim($photo->getDestination(), '/') . '/' . $photo->getValue();
            if ($skipResize) {
                $path = rtrim($destination, '/') . '/' . $profileId . '-full.jpg';
                @copy($original, $path);
                return true;
            }
            $img = PhpThumb_Factory::create($original);
            $img->save($path);
            unlink($original);
        }
        return true;
    }

    public function unlinkCategory($categoryIds)
    {
        if (!is_array($categoryIds)) {
            $categoryIds = (array) $categoryIds;
        }

        if (count($categoryIds)) {
            $this->updateWhere(
                [
                    'category_id' => 0,
                ],
                $this->quoteInto('category_id IN (?)', $categoryIds)
            );
        }
    }
}