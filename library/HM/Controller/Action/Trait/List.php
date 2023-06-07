<?php

trait HM_Controller_Action_Trait_List
{
    protected $_data = array();

    public function initList()
    {
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/grid.css'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/kbase.css'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/content-modules/grid.js'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/jquery/jquery.collapsorz_1.1.min.js'));
    }

    protected function _export($format) 
    {
        switch ($format) {
            case 'excel':
                $this->_exportToExcel();
            break;
            default:
                $this->view->error = _('Данный формат выгрузки не поддерживается');
            break;
        }
    }
    
    // duplicated from Bvb_Grid_Deploy_Excel
    protected function _exportToExcel()
    {
        $title = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('windowTitle');
        $title = $title ? $title : "text";
        $attribs = $this->_getExportAttribs();

        if (is_array ( $this->_data ) && count($this->_data)>65569) {
            throw new HM_Exception('Maximum number of recordsa allowed is 65569');
        }

        $xml = <<<HEAD
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
HEAD;


        $xml .= <<<STYLES
<Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="Title">
   <Font x:Family="Swiss" ss:Bold="1"/>
  </Style>
  <Style ss:ID="Str"/>
  <Style ss:ID="MultyLines">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
  </Style>
 </Styles>
STYLES;


        $xml .= '<Worksheet ss:Name="' .  $title  . '" ss:Description="' .  $title  . '"><Table>';
        $xml .= '<Row>';

        foreach ($attribs as $title => $value) {
            //$type = ! is_numeric ($value ['value'] ) ? 'String' : 'Number';
            $type = 'String';
            $xml .= '<Cell><Data ss:Type="' . $type . '">' . $title . '</Data></Cell>';
        }
        $xml .= '</Row>';

        if (is_array ( $this->_data )) {
            foreach ( $this->_data as $item ) {
                $xml .= '<Row>';
                foreach ($attribs as $title => $method) {
                    //$type = ! is_numeric ( $value ['value'] ) ? 'String' : 'Number';
                    $type = 'String';
                    $value = '';
                    if (method_exists($item, $method)) {
                        $value = $item->$method();
                    } elseif (!empty($item->$method)) {
                        $value = $item->$method;
                    }
                    $xml .= '<Cell><Data ss:Type="' . $type . '">' . $value . '</Data></Cell>';
                }
                $xml .= '</Row>';
            }
        }

        $xml .= '</Table></Worksheet>';
        $xml .= '</Workbook>';

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $contentType = strpos($request->getHeader('user_agent'), 'opera') ? 'application/x-download' : 'application/excel';
        $fileName = date(HM_Controller_Action::EXPORT_FILENAME);
        ob_end_clean();

        /*
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        */

        header('Content-type: '.$contentType);
        header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Pragma: public");
        header("Content-Transfer-Encoding: binary");

        echo $xml;
        exit();
    }

    // ??
    protected function _getExportAttribs()
    {
        return array(
            _('ФИО') => 'getName',
            _('Прохождение отбора') => 'getDescription',
        );
    }
}