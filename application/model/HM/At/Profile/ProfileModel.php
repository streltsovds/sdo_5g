<?php
class HM_At_Profile_ProfileModel extends HM_Model_Abstract implements HM_Model_ContextInterface
{
    const THUMB_WIDTH = 640;
    const THUMB_HEIGHT = 320;

    public function getCategory()
    {
        if (count($this->category)) {
            return $this->category->current()->name;
        }
        return false;
    }
    
    public function getName()
    {
        return $this->name;    
    }
    
    public function getIcon()
    {
        return Zend_Registry::get('config')->url->base . 'images/session-icons/profile.png';
    }

    public function getShortName()
    {
        return $this->name;
    }

    static public function getVariant($variantId, $method)
    {
        $variants = self::$method();
        if (isset($variants[$variantId])) {
            return $variants[$variantId];
        }
        return false;        
    }    
    
    static public function getGenderVariants()
    {
        return array(
            1 => _('Мужской'),
            2 => _('Женский'),
        );
    }    
    
    static public function getAcademicDegreeVariants()
    {
        return array(
            1 => _('Бакалавр'),
            2 => _('Специалист'),
            3 => _('Магистр'),
            4 => _('Кандидат'),
            5 => _('Доктор')
        );
    }    
    
    static public function getMainEducationVariants()
    {
        return array(
            1 => _('Основное'),
            2 => _('Среднее'),
            3 => _('Профессиональное'),
            4 => _('Неоконченное высшее'),
            5 => _('Высшее'),
            6 => _('Два и более высших')
        );
    }    
    
    static public function getTripsVariants()
    {
        return array(
            1 => _('Нет'),
            2 => _('1 раз в квартал'),
            3 => _('1 раз в месяц'),
            4 => _('несколько раз в месяц'),
        );
    }    
    
    static public function getMobilityVariants()
    {
        return array(
            1 => _('Низкий'),
            2 => _('Средний'),
            2 => _('Высокий'),
        );
    }

    static public function getNotLinkedYetProfiles()
    {
        $result = array();

        $select = Zend_Registry::get('serviceContainer')->getService('AtProfile')->getSelect();
        $select->from(array('p' => 'at_profiles'),
            array(
                'profile_id',
                'name',
                'department_path',
            )
        )
            ->where('p.base_id IS NULL OR p.base_id = 0');

        $rows = $select->query()->fetchAll();

        if (count($rows)) {
            foreach ($rows as $row) {
                $result[$row['profile_id']] = $row['department_path'] ? sprintf("%s (%s)", $row['name'], html_entity_decode($row['department_path'])) : $row['name'];
            }
        }
        asort($result);

        return $result;
    }

    // Зачем я это здесь сделал?! :)
    // TODO: вынести в Service
    static public function getProfilesToFrontend()
    {
        $select = Zend_Registry::get('serviceContainer')->getService('AtProfile')->getSelect();
        $select->from(
            array('p' => 'at_profiles'),
            array(
                'id' => 'profile_id',
                'name',
                'department_path',
            )
        )
            ->where('p.base_id IS NULL OR p.base_id = 0')
            ->order('name');

        $rows = $select->query()->fetchAll();

        if (count($rows)) {
            foreach ($rows as &$row) {
                $row['name'] = $row['department_path'] ? sprintf("%s (%s)", $row['name'], html_entity_decode($row['department_path'])) : $row['name'];
                unset($row['department_path']);
            }
            unset($row);
        }

        return $rows;
    }

    public function getUserIcon($full = false)
    {
        $full = $full ? '-full' : '';
        $path = HM_At_Profile_ProfileModel::getIconFolder($this->profile_id) . $this->profile_id . $full . '.jpg';
        if (is_file($path)) {
            return preg_replace('/^.*public\//', Zend_Registry::get('config')->url->base, $path) . '?_=' . @filemtime($path);
        }
        return null;
    }

    public function getDefaultIcon()
    {
        return Zend_Registry::get('config')->url->base.'images/icons/profile.svg';
    }

    public function getIconHtml()
    {
        $result = '';
        if (!$icon = $this->getUserIcon()) {
            $icon = $this->getDefaultIcon();
            $result.= '<v-img src="'.$icon.'" :width="\'100%\'" :height="\'150\'" color="primary" class="primary default-subject-icon"></v-img>';
        } else {
            $result.='<v-img src="'.$icon.'" :width="\'100%\'" :height="\'150\'"></v-img>';
        }

        return $result;
    }

    public function getIconSrc()
    {
        if (!$icon = $this->getUserIcon()) {
            $icon = $this->getDefaultIcon();
        }
        return $icon;
    }

    public static function getIconFolder($profileId = 0)
    {
        $folder = Zend_Registry::get('config')->path->upload->profile;
        $maxFilesPerFolder = Zend_Registry::get('config')->path->upload->maxfilescount;
        $folder = $folder . floor($profileId / $maxFilesPerFolder) . '/';

        if (!is_dir($folder)) {
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }

        return $folder;
    }
}
