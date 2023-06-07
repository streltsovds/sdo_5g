<?php
class HM_Certificates_CertificatesService extends HM_Service_Abstract
{
    /**
     * Добавление пользователю курса сертификата
     * @param $user_id
     * @param $subject_id
     * @param int $period
     * @param null $fromDate
     * @param int $type
     * @return bool | HM_Certificates_CertificatesModel
     * @throws Zend_Date_Exception
     */
    public function addCertificate($user_id, $subject_id, $period = -1, $fromDate = null, $type = HM_Certificates_CertificatesModel::TYPE_CERTIFICATE_ELS)
    {
        if ( !$user_id || !$subject_id ) {
            return false;
        }
        
         $certificate = $this->getOne($this->fetchAll($this->getService('Certificates')->quoteInto(
             array('subject_id = ? ', ' AND user_id = ?'),
             array($subject_id, $user_id)
         )));

        // если сертификата нет - он создается
        if ( !$certificate ) {

            $begin = new HM_Date($fromDate);

            $data = array('user_id' => (int) $user_id,
                'subject_id' => (int) $subject_id,
                'created' => $begin->toString('yyyy-MM-dd'),
                'type' => $type,
            );

            if ($period && $period != -1) {
                $end = clone $begin;
                $end->add($period, Zend_Date::MONTH);
                $data['enddate'] = $end->toString('yyyy-MM-dd');
            }
            $certificate = $this->insert($data);
        } 
        
        if ( !$certificate ) {
            return false;
        }

        if($this->getService('Option')->getOption('generateCertificateFiles')) {
            $this->createFile($certificate->certificate_id);
        }
        
        return $certificate;
    }

    public function addCertificateFile($filePath, $fileNameString)
    {
        $dest = realpath(Zend_Registry::get('config')->path->upload->certificates);
        $fileName = basename($filePath);
        $temp = explode('.', $fileName);
        $ext = $temp[count($temp) - 1];
        $data = array(
            'name'      => $fileNameString . '.'. $ext,
            'path'      => 'none',
            'file_size' => filesize($filePath),
            'item_type' => HM_Files_FilesModel::ITEM_TYPE_CERTIFICATE
        );

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $data['created']    = HM_Date::now()->toString(HM_Date::SQL);
        $data['created_by'] = $userService->getCurrentUserId();

        $fileData = $this->getService('Files')->insert(
            $data
        );

        if(!$fileData)
            return false;

        $destFile = ( count ($temp) > 1 )? $dest . '/' . $fileNameString . '.'. $ext : $dest . '/' . $fileData->file_id . '.tmp';
        copy($filePath, $destFile);
        return $fileData;
    }

    public static function getPath($fileId)
    {
        $dest = realpath(Zend_Registry::get('config')->path->upload->certificates);
        $glob = glob($dest . '/' . $fileId . '.*');

        return realpath($glob[0]);
    }

    /**
     * Генерация PDF файла сертификата 
     * @param INT $certificateId
     * @return boolean
     */
    public function createFile($certificateId, $returnAsString = null, $certificateText = null)
    {
        if ( !$certificateId || $certificateId != intval($certificateId)) {
//             return false; // для preview нам нужно генерить сертификат без реального номера
        }
        
        // получение инормации о курсе и получателе сертификата
        $certificate = $this->getOne($this->findDependence(array('User','Subject'), $certificateId));

        if($certificate && count($certificate->student) && count($certificate->courses)) {
            $student = $certificate->student[0];
            $cource  = $certificate->courses[0];

            $graduated = $this->getService('Graduated')->fetchAll($this->quoteInto(
                array('MID = ?', ' AND CID = ?'),
                array($student->MID, $cource->subid)
            ))->current();

            //вызываем модель - таблица оценок
            $marks = new HM_Subject_Mark_MarkTable();
            //извлекаем методом оценку за конкретный курс
            $studentMarks = $marks->getMarks($student->MID, $cource->subid);
            $provmid = $student->MID;
            $provcid = $cource->subid;
            
            
            $studentName = ($student->FirstName || $student->LastName) ?
                            $student->FirstName . " " . $student->Patronymic . " " . $student->LastName :
                            _("Пользователь")." #".$student->MID;
                            
            $subjectName = iconv('cp1251',
                                 'utf-8',
                                 wordwrap(iconv('utf-8',
                                                 'cp1251',
                                                 $certificate->courses[0]->name), 
                                           85, 
                                           "\n"));

            //структурное подразделение
            $unitInfo = Zend_Registry::get('serviceContainer')->getService('User')->getUnitInfo($student->MID);

            //план занятий
            $lessons = $this->getService('Lesson')->fetchAll($this->quoteInto(
                array('CID=?', ' AND isfree = ?', ' AND typeID NOT IN (?)'),
                array($cource->subid, HM_Lesson_LessonModel::MODE_PLAN, array_keys(HM_Event_EventModel::getExcludedTypes()))
            ))->getList('SHEID', 'title');
        }

        $oldEncoding = mb_internal_encoding();
        //mb_internal_encoding("Windows-1251");
        //mb_internal_encoding("UTF-8");
        
        $template = $this->getService('Option')->getOption('template_certificate_text');
        $template = '<html ><meta http-equiv="content-type" content="text/html; charset=utf-8" />'.$template.'</html>';

        //$template = str_replace("/upload/files/","../public/upload/files/", $template); // зачем это здесь? в любом случае нужно рефакторить
        $template = str_replace("&nbsp;"," ", $template);
        $template = str_replace("<em>","<em> ", $template);
        $template = str_replace("<strong>","<strong> ", $template);

        if ($studentName)   $template = str_replace("[NAME]", " ".$studentName." ", $template);
        if ($subjectName)   $template = str_replace("[COURSE]", " ".$subjectName." ", $template);
        if ($certificateId) $template = str_replace("[CERTIFICATE]", " ".$this->getFormatNubmer($certificateId)." ", $template);
        if ($studentMarks)  {
            $template = str_replace("[GRADE]", " ".$studentMarks." ", $template);
        } else {
            $template = str_replace("[GRADE]", _('Оценка не получена'), $template);
        }
        if ($lessons) {
            $template = str_replace("[STUDY_PLAN]",
                "<ul><li>".implode('<li>', $lessons)."</ul>"
                , $template);
        }

        if ($unitInfo && $unitInfo[0]['department']) {
            $template = str_replace("[DEPARTMENT]", " ".$unitInfo[0]['department']->name." ", $template);
        } else {
            $template = str_replace("[DEPARTMENT]", " "._('Нет')." ", $template);
        }

        if ($graduated)
        {
            $template = str_replace("[STUDY_BEGIN]", " ".date('d.m.Y', strtotime($graduated->begin))." ", $template);
            $template = str_replace("[STUDY_END]"  , " ".date('d.m.Y', strtotime($graduated->end))." "  , $template);
        } else {
            $template = str_replace("[STUDY_BEGIN]", " - ", $template);
            $template = str_replace("[STUDY_END]", " - ", $template);
        }

        $pdfManager = new HM_Export_Certificate_PdfManager();
        $output = $pdfManager->createPdf($template);

        //file_put_contents("100001.pdf",$output);
        
        //создание файла сертификата
        //$pdf = new Zend_Pdf();
        
        //$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ROMAN);
         
        //$font = Zend_Pdf_Font::fontWithPath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 
        //                                       ".." . DIRECTORY_SEPARATOR . 
        //                                       "public" . DIRECTORY_SEPARATOR . 
        //                                    "fonts" . DIRECTORY_SEPARATOR .
        //                                    "arial.ttf");
        
                
        //$text_lines = explode("\n",$template);
        
        
        //$cur_page = 0;
        # создаем первую страницу
        //$pdf->pages[$cur_page] = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        //$pdf->pages[$cur_page]->setFont($font, 10);
        //$padding = $pdf->pages[$cur_page]->getHeight() - 30;
                 
        //foreach ( $text_lines as $line) {
            # перенос на др страницу
        //    if ( $padding < 30) {
        //         $cur_page ++;
        //         $pdf->pages[$cur_page] = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        //         $pdf->pages[$cur_page]->setFont($font, 10);
        //         $padding = $pdf->pages[$cur_page]->getHeight() - 30;
        //    }
            
        //    $pdf->pages[$cur_page]->drawText($line,70,$padding,'UTF-8');
        //    $padding -= 20;
        //}
        
        if (!$returnAsString) {
            $oldUmask = umask(0);
            $fileName = Zend_Registry::get('config')->path->upload->certificates . "{$certificateId}.pdf";
            file_put_contents($fileName, $output);
            //$pdf->save($fileName);
            chmod($fileName, 0777);
            umask($oldUmask);
            //mb_internal_encoding($oldEncoding);                                    
            return true;
        } 
        return $output;
    }
    
    /**
     * Форматирует ИД сертификата для печати или отображения на экране
     * @param int|string $certificateId
     * @return string
     */
    public function getFormatNubmer($certificateId = null)
    {
        if ( !$certificateId ) return '';

        $certificate = $this->find($certificateId)->current();
        
        return $certificate->number ? $certificate->number : str_pad($certificateId, 10, "0", STR_PAD_LEFT);
    }

}