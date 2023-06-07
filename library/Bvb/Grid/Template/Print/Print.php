<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id: Print.php 1173 2010-05-19 18:25:41Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Template_Print_Print implements Bvb_Grid_Template_Print_PrintInterface
{

    public $i;

    /**
     * Options
     * @var array
     */
    public $options = array();


    public function globalStart ()
    {


        $return = "
            <!DOCTYPE html>
            <html>
                <head>
                    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $this->options['charEncoding']. "\" />
                    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                    <link href=\"/frontend/css/normalize.css\" rel=\"stylesheet\" media=\"print, screen\">
                    <link href=\"/frontend/css/table-print.css\" rel=\"stylesheet\" media=\"print, screen\">
                </head>
            <body onload='window.print()';>";
        $return .= Zend_Registry::get('serviceContainer')->getService('Option')->getOption('template_report_header');
        $return .= "<table>";

        return $return;
    }


    public function header ()
    {

        if (isset($this->options ['logo']) && is_file ( $this->options ['logo'] )){
            $img = "<img align=\"left\" src=\"" . $this->options['logo'] . "\" border=\"0\">";
        } else {
            $img = '';
        }

        $this->options['title'] = isset($this->options['title'])?$this->options['title']:'';
        $this->options['subtitle'] = isset($this->options['subtitle'])?$this->options['subtitle']:'';

        // Если тут все пусто то не рисуем вообще эту tr-ку
        if ($img === "" && $this->options['title'] === "" && $this->options['subtitle'] === "") {
            return "";
        }
        
        return " <tr><td colspan=\"{$this->options['colspan']}\" style='border:solid black 1.0pt;background-color:#FFFFFF;color:#000000;padding:5px'> $img <p align=center style='text-align:center'><b><span style='font-size:10.0pt;'><o:p>" . $this->options['title'] . "</o:p></span></b><span style='font-size:9.0pt;'><o:p><br>" . $this->options['subtitle'] . "</o:p></span></p>
  </td></tr>";
    }


    public function globalEnd ()
    {
        return "</table></div>".Zend_Registry::get('serviceContainer')->getService('Option')->getOption('template_report_footer')."</body></html>";
    }


    public function titlesStart ()
    {
        return "<thead><tr>";
    }

    public function titlesEnd ()
    {
        return "</tr></thead>";
    }


    public function titlesLoop ()
    {
        return "<th>{{value}}</th>";
    }


    public function loopStart ()
    {
        $this->i ++;

        return "<tr>";
    }



    public function loopEnd ()
    {
        return "</tr>";
    }



    public function loopLoop ()
    {
        return "<td>{{value}}</td>";
    }


    public function hRow ()
    {
        return "<tr><td colspan=\"" . $this->options['colspan']. "\" style='border-top:none; color:#FFFFFF;
        border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;
        padding:3px; background:#666;'> <p  style='text-align:center' class=MsoNormal><span style='font-size:10.0pt;
        font-family:Helvetica; '>{{value}}<o:p></o:p></span></p>
  </td></tr>";
    }

    public function noResults ()
    {
        return "<tr><td colspan=\"" . $this->options['colspan'] . "\" style='border-top:none; color:#FFFFFF;
        border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;
        padding:3px; background:#666;'> <p  style='text-align:center' class=MsoNormal><span style='font-size:10.0pt;
        font-family:Helvetica; '>{{value}}<o:p></o:p></span></p>
  </td></tr>";
    }

    public function sqlExpStart ()
    {
        return "<tr>";
    }



    public function sqlExpEnd ()
    {
        return "</tr>";
    }



    public function sqlExpLoop ()
    {
        return "<td  style='border-top:none;border-left:none;  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;
        padding:5px;'> <p><span style='font-size:8.0pt; font-family:Helvetica;'>{{value}}<o:p></o:p></span></p>
  </td>";
    }


}

