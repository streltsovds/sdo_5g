<?php
class HM_View_Helper_VueFile extends Zend_View_Helper_FormFile
{
    public function vueFile($name, $values = null, $attribs = null, array $errors = array())
    {

        $formData = array(
            'uniqid' => uniqid(),
            'sessid' => session_id(),
            'ishtml' => 'yes'
        );

        $params = array(
            'file_size_limit'   => $this->_convertIniToInteger(trim(ini_get('upload_max_filesize'))),
            'file_types'        => '*.*',
            'file_sample'       => null,
            'file_upload_limit' => 1,
            'upload_url'        => $this->view->url(array(
                'baseUrl' => '',
                'module' => 'file',
                'controller' => 'upload',
                'action' => 'index')
            ),
			'formData' => $formData,
//			'preview_url' => null,
			'delete_button' => '',
            'label' => null,
            'description' => null,
            'required' => false,
            'crop' => false,
            'tooltip' => false
        );

        $excludedParams = [
            'uploaded_file',
        ];

        foreach($params as $param => $value) {
            if (isset($attribs[$param]) && !in_array($param, $excludedParams)) {
                $params[$param] = $attribs[$param];
            }
        }

        if (!$params['file_upload_limit'] || $params['file_upload_limit'] < 0) {
            $params['file_upload_limit'] = 0;
        }

        if (!$params['file_size_limit'] || $params['file_size_limit'] < 0) {
            $params['file_size_limit'] = $this->_convertIniToInteger(trim(ini_get('upload_max_filesize')));
        }

        $params['file_size_limit_string'] = $this->_toByteString($params['file_size_limit']);

        $params['inputAttrs']['accept'] = implode(',', $this->_getMimeTypes($params['file_types']));

        if ($params['file_types']) {
            $params['file_types_extensions'] = HM_Mime_Info::getExtensionsArray($params['file_types']);
            if (false !== strpos($_SERVER['HTTP_REFERER'], 'lesson')){
                $params['file_types_extensions'] = [
                    'TXT', 'XLSX', 'PPTX', 'DOC', 'DOCX', 'PDF', 'JPG', 'PNG', 'MP4', 'WEBM'
                ];
            }
        }

        // данные уже загруженых файлов
        $uploadedItems = [];

        if (isset($attribs['uploaded_file']) && is_array($attribs['uploaded_file'])) {
            foreach ($attribs['uploaded_file'] as $uploadedItem) {
                if($uploadedItem instanceof HM_DataType_Form_Element_Vue_UploadedItem) {
                    $uploadedItems[] = $uploadedItem->asArray();

                    $params['file_upload_limit']--;
                }
            }
        }

        $params['file_upload_limit'] = max($params['file_upload_limit'], 0);


        $files = HM_Json::encodeErrorThrow($uploadedItems);
        $params = ZendX_JQuery::encodeJson($params);
        $errors = ZendX_JQuery::encodeJson($errors);

        $allow_conversion = isset($attribs['allow_conversion']) ? ($attribs['allow_conversion'] ? 'true' : 'false') : 'true';

        return <<<HTML
<hm-file
    name='$name'
    :params='$params'
    :errors='$errors'
    :uploaded-items='$files'
    :allow-conversion='{$allow_conversion}'
>
</hm-file>
HTML;
    }

    private function _toByteString($size)
    {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size >= 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $sizes[$i];
    }

    private function _convertBytes($value)
    {
        if (is_numeric( $value )) {
            return $value;
        } else {
            $value_length = strlen($value);
            $qty = substr($value, 0, $value_length - 1);
            $unit = strtolower(substr($value, $value_length - 1));
            switch ( $unit ) {
            case 'k':
                $qty *= 1024;
                break;
            case 'm':
                $qty *= 1048576;
                break;
            case 'g':
                $qty *= 1073741824;
                break;
            }
            return $qty;
        }
    }

    private function _convertIniToInteger($setting)
    {
        return $this->_convertBytes($setting);
    }

    private function _getMimeTypes($extensions)
    {
        return HM_Mime_Info::extensionsToMime($extensions);
    }

    private function _getTypesRegEx($extensions)
    {
        $extensions = HM_Mime_Info::getExtensionsArray($extensions);
        $extensionsFiltered = array();

        foreach ($extensions as $extension) {
            if ($extension == '*') {
                $extensionsFiltered[] = '[^\.]*';
            } else {
                $extensionsFiltered[] = preg_quote($extension);
            }
        }

        if (!empty($extensions)) {
            return '/\.('.implode('|', $extensionsFiltered).')$/i';
        }
    }
}
