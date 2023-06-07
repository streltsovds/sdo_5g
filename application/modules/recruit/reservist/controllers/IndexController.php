<?php

class Reservist_IndexController extends HM_Controller_Action {

    protected $_reservistId = 0;
    protected $_reservist = null;

    public function indexAction()
    {
        $reservistId = $this->_getParam('reservist_id', 0);

        $select = $this->getService('RecruitReservist')->getSelect();
        $select->from(
            array('r' => 'recruit_reservists'),
            array(
                'r.company',
                'r.department',
                'r.brigade',
                'r.position',
                'r.fio',
                'r.gender',
                'r.snils',
                'r.birthday',
                'r.age',
                'r.region',
                'r.citizenship',
                'r.phone',
                'r.phone_family',
                'r.email',
                'r.position_experience',
                'r.sgc_experience',
                'r.education',
                'r.retraining',
                'r.training',
                'r.qualification_result',
                'r.rewards',
                'r.violations',
                'r.comments_dkz_pk',
                'r.relocation_readiness',
                'r.evaluation_degree',
                'r.leadership',
                'r.productivity',
                'r.quality_information',
                'r.salary',
                'r.hourly_rate',
                'r.annual_income_rks',
                'r.annual_income_no_rks',
                'r.monthly_income_rks',
                'r.monthly_income_no_rks',
                'r.import_date',
                'r.importer_id',
            )
        );
        $select->where('r.reservist_id = ? ', $reservistId);
        $select->group(array(
            'r.company',
            'r.department',
            'r.brigade',
            'r.position',
            'r.fio',
            'r.gender',
            'r.snils',
            'r.birthday',
            'r.age',
            'r.region',
            'r.citizenship',
            'r.phone',
            'r.phone_family',
            'r.email',
            'r.position_experience',
            'r.sgc_experience',
            'r.education',
            'r.retraining',
            'r.training',
            'r.qualification_result',
            'r.rewards',
            'r.violations',
            'r.comments_dkz_pk',
            'r.relocation_readiness',
            'r.evaluation_degree',
            'r.leadership',
            'r.productivity',
            'r.quality_information',
            'r.salary',
            'r.hourly_rate',
            'r.annual_income_rks',
            'r.annual_income_no_rks',
            'r.monthly_income_rks',
            'r.monthly_income_no_rks',
            'r.import_date',
            'r.importer_id',
        ));
        $result = $select->query()->fetchAll();

        $result[0]['birthday']    = date('d.m.Y', strtotime($result[0]['birthday']));
        $result[0]['import_date'] = date('d.m.Y', strtotime($result[0]['import_date']));

        $headers = array(
            'Наименование организации',
            'Структурное подразделение',
            'Бригада',
            'Должность',
            'Фамилия, имя, отчество',
            'Пол',
            'СНИЛС',
            'Дата рождения',
            'Возраст',
            'Регион проживания',
            'Гражданство',
            'Телефон личный',
            'Телефон членов семьи',
            'e-mail',
            'Стаж работы в должности (профессии)',
            'Стаж работы в Компании',
            'Основное образование',
            'Профессиональная переподготовка',
            'Повышение квалификации',
            'Результаты проверки/подтверждения квалификации',
            'Наличие поощрений, наград',
            'Сведения о наличии нарушений требований ОТ, ПБ и ООС',
            'Наличие замечаний от ДКЗ',
            'Готовность к релокации для работы на объектах Компании',
            'Оценка степени профессиональной ценности, качества работы и исполнительской надежности по результатам анкетирования',
            'Лидерство в бригаде (структурном подразделении)',
            'Производительность труда',
            'Сведения о качестве выполняемых работ',
            'Должностной оклад',
            'Часовая тарифная ставка',
            'Среднегодовой доход (с РКС)',
            'Среднегодовой доход (без РКС)',
            'Среднемесячный доход (с РКС)',
            'Среднемесячный доход (без РКС)',
            'import_date',
            'importer_id',
        );

        $moneyFields = array(
            'annual_income_rks',
            'annual_income_no_rks',
            'monthly_income_rks',
            'monthly_income_no_rks',
        );
        foreach ($moneyFields as $moneyField) {
            $result[0][$moneyField] = round($result[0][$moneyField], 2);
        }

        $combine = array_combine($headers, $result[0]);

        $manager = $this->getService('User')->find($combine['importer_id'])->current();
        $managerFio = ($manager) ? $manager->LastName . ' ' . $manager->FirstName . ' ' . $manager->Patronymic : 'Нет данных';

        $fio = $combine['Фамилия, имя, отчество'];
        $importDetails = array('who' => $managerFio, 'when' => date('d.m.Y', strtotime($combine['import_date'])));
        unset($combine['Фамилия, имя, отчество']);
        unset($combine['import_date']);
        unset($combine['importer_id']);

        $this->view->setHeader(_('Данные о резервисте'));
        $this->view->setSubHeader($fio);

        $snilsDir = APPLICATION_PATH. '/../data/upload/staff_reserve_ext/' . $result[0]['snils'];
        $showButton = file_exists($snilsDir) && is_dir($snilsDir);

        $this->view->history = $combine;
        $this->view->fio = $fio;
        $this->view->importDetails = $importDetails;
        $this->view->reservistId = $reservistId;
        $this->view->showButton = $showButton;
    }

    public function exportZipAction()
    {
        $reservistId = $this->_getParam('reservist_id', 0);
        $reservist = $this->getService('RecruitReservist')->find($reservistId)->current();
        $snilsDir = APPLICATION_PATH. '/../data/upload/staff_reserve_ext/' . $reservist->snils;
        $zipFile = Zend_Registry::get('config')->path->upload->tmp.$reservist->snils.'.zip';

        if (file_exists($snilsDir)) {
            $this->_zip($snilsDir, $zipFile);

            $this->_helper->SendFile(
                $zipFile,
                'application/zip',
                array('filename' => $reservist->snils.'.zip')
            );
            exit();
        }
        $warningMessage = 'Директория с данными по СНИЛС №' . $reservist->snils . ' не найдена.';
        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _($warningMessage)));
        $this->_redirector->gotoSimple('index', 'index', 'reservist', array('reservist_id' => $reservistId));
    }

    protected function _zip($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) {
                    continue;
                }

//                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file) === true) {
                    $localName = str_replace($source . '/', '', $file);
                    $contents  = file_get_contents($file);
                    $zip->addFromString($localName, $contents);
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
}
