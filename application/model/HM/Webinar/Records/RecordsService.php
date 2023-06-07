<?php
class HM_Webinar_Records_RecordsService extends HM_Service_Abstract
{


    public function createRecord($pointId,$xmlId) {
        try {
            /* Создаем ресурс */
            $lesson = $this->getOne($this->getService('Lesson')->find($pointId));
            
            $data = array();
            $data['title'] = $lesson->title . " (" . date('Y-m-d H:i:s') . ")";
            $data['subject_id'] = $lesson->CID;
            $data['type'] = HM_Resource_ResourceModel::TYPE_WEBINAR;
            $resource = $this->getService('Resource')->insert($data);
            $resourceId = $resource->resource_id;
            /**/
            
            /* Связка с курсом */
            $this->getService('SubjectResource')->insert(array('subject_id' => $data['subject_id'], 'resource_id' => $resource->resource_id));
            /**/

            /* Создаем директории */
            $path = APPLICATION_PATH . '/../public/upload/webinar-records/' . $resourceId;
            $zipFile = $path . '.zip';
            Library::mkDirIfNotExists($path);
            /**/

            /*
                Копирование и изменение путей в xml файле (xml находится на сервере ped5)
                $pathToFiles - путь где находятся файлы на сервере ped5
            */
	        $pathToFiles = Zend_Registry::get('config')->redFive->files;
            if (strlen($pathToFiles)) {

	            $xmlData = simplexml_load_file($pathToFiles.$pointId.'/'.$xmlId.'.xml');

	            $xmlDataForZip = $xmlData->asXML();
                $imagesForCopy = array();
	            foreach ($xmlData->outline->item as $value) {
		            $attr = $value->attributes();
					$href = (string)$attr->href;
                    if (strpos($href, 'youtube_http://') === false) {
                        $imagesForCopy[] = $href;
                    }
	            }
	            foreach ($xmlData->broadcast->item as $value) {
		            $attr = $value->attributes();
					$href = (string)$attr->href;
		            $flvFilesToCopy[] = $href;
            }
	            foreach ($xmlData->screenshare->item as $value){
		            $attr = $value->attributes();
					$href = (string)$attr->href;
                    $screenshareFilesToCopy[]=$href;
	            }
            }
            /**/
            /* Копирование трансляций и захватов экрана  */
            $files = $this->getService('Webinar')->getRecordFiles($pointId);
            if (is_array($files) && count($files)) {
                foreach ($files as $file) {
	               $fileName =  substr($file, strrpos($file,"/")+1);
	                if (in_array($fileName, $flvFilesToCopy)) {
	                    if (false === Library::streamCopy($file, $path . '/' . basename($file))) {
	                    }
	                }
                    elseif (in_array($fileName, $screenshareFilesToCopy)) {
                        if (false === Library::streamCopy($file, $path . '/' . basename($file))) {
                        }
                    }
                }
            }


            /**/
            /* Копирование swf, html */
            $files2copy = array('webinar.swf', 'index.html', 'expressInstall.swf', 'swfobject.js');
            foreach ($files2copy as $file2copy) {
                if (!copy(APPLICATION_PATH . '/../public/webinar/player/local/' . $file2copy, $path . '/' . $file2copy)) {
                }
            }

            /* Формирование XML для архивной версии */

          //  $xml = Webinar_Xml_Service::getInstance()->get($pointId, true);
            @file_put_contents($path . '/webinar.xml', $xmlDataForZip);
            /**/

            /* Копирование файлов вебинара */
            $webinarItems = Webinar_Files_Service::getInstance()->getItemList($pointId);

            if (count($webinarItems)) {
                foreach ($webinarItems as $item) {
                    if (strlen($item->path)&& file_exists($item->path)){
                        $fileName = basename($item->path);
                        if(in_array($fileName,$imagesForCopy)) {
                            try {
                                copy($item->path, $path . '/' . basename($item->path));
                            }
                            catch (Exception $e) {
                                Zend_Registry::get('log_system')->log("fatal copy item for point_id ".$pointId." !!!!   ".$e->getMessage(),0);
                            }
                        }
                    }
                    else {
                        Zend_Registry::get('log_system')->log("not found item for point_id ".$pointId." !!!!   ".$e->getMessage(),0);
                    }
                }
            }
            /**/
            /* Архивирование */
            $zip = new Zend_Filter_Compress(array(
                        'adapter' => 'zip',
                        'options' => array(
                            'archive' => $zipFile,
                            'target' => $path . '/'
                        ),
                    ));

            if (!$zip->filter($path . '/')) {
                
            }
            /**/

            /* Формирование XML для обычной версии */
            //$xml = Webinar_Xml_Service::getInstance()->get($pointId, false, $resourceId);
            @file_put_contents($path . '/webinar.xml', $xmlData->asXML());
            /**/
        } catch (Zend_Exception $e) {
            
        }
    }
 
    
}