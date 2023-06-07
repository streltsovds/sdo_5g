<?php
class HM_Word
{

    private $_html = '';

    public function setHtml($html)
    {
        $this->_html = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $html);
    }

    public function appendHtml($html)
    {
        $this->_html .= iconv(Zend_Registry::get('config')->charset, 'UTF-8', $html);
    }

    public function appendTable($fields, $data)
    {
        $table = "
        <table  border=1 cellspacing=0 cellpadding=0 width='100%'
                style='width:100%;margin-left:-.35pt;border-collapse:collapse;border:none;'>";
        $table .= "<tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>";
        foreach($fields as $name) {
            $table .= "<td style='border:solid black 1.0pt;background-color:black;color:#FFFFFF;padding:5px'><p align=center style='text-align:center'><b><span style='font-size:10.0pt;'>$name<o:p></o:p></span></b></p></td>";
        }
        $table .= "</tr>";

        $i = 0;
        foreach($data as $item) {
            $table .= "<tr>";
            foreach($item as $value) {
                if ($i%2) {
                    $table .= "<td style='border-top:none;border-left:solid black 1.0pt;border-bottom:solid black 1.0pt; border-right:solid black 1.0pt; background:#E0E0E0;padding:3px'> <p><span><span style='font-size:8.0pt;font-family:Helvetica;'>$value<o:p></o:p></span></p></td>";
                } else {
                    $table .= "<td style='border-top:none;border-left:solid black 1.0pt; border-bottom:solid black 1.0pt;border-right:solid black 1.0pt; padding:3px'> <p class=MsoNormal><span style='font-size:8.0pt; font-family:Helvetica;'>$value<o:p></o:p></span></p> </td>";
                }
            }
            $i++;            
            $table .= "</tr>";
        }

        $table .= "</table>";
        $this->appendHtml($table);
    }


    public function send()
    {
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-type', 'application/vnd.ms-word');
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', 'attachment;Filename=order_'.date('Y-m-d-H-i').'.doc');
        Zend_Controller_Front::getInstance()->getResponse()->sendHeaders();
        echo "<html xmlns:v=\"urn:schemas-microsoft-com:vml\"
                xmlns:o=\"urn:schemas-microsoft-com:office:office\"
                xmlns:w=\"urn:schemas-microsoft-com:office:word\"
                xmlns:m=\"http://schemas.microsoft.com/office/2004/12/omml\"
                xmlns:css=\"http://macVmlSchemaUri\" xmlns=\"http://www.w3.org/TR/REC-html40\">

                <head>
                <meta name=Keywords content=\"\">
                <meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">
                <meta name=ProgId content=Word.Document>
                <meta name=Generator content=\"Microsoft Word 2008\">
                <meta name=Originator content=\"Microsoft Word 2008\">
                <title></title>
                <!--[if gte mso 9]><xml>
                 <o:OfficeDocumentSettings>
                  <o:AllowPNG/>
                 </o:OfficeDocumentSettings>
                </xml><![endif]--><!--[if gte mso 9]><xml>
                 <w:WordDocument>
                  <w:View>Print</w:View>
                  <w:Zoom>BestFit</w:Zoom>
                  <w:SpellingState>Clean</w:SpellingState>
                  <w:GrammarState>Clean</w:GrammarState>
                  <w:TrackMoves>false</w:TrackMoves>
                  <w:TrackFormatting/>
                  <w:DoNotHyphenateCaps/>
                  <w:PunctuationKerning/>
                  <w:DrawingGridHorizontalSpacing>9,35 pt</w:DrawingGridHorizontalSpacing>
                  <w:DrawingGridVerticalSpacing>9,35 pt</w:DrawingGridVerticalSpacing>
                  <w:ValidateAgainstSchemas/>
                  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
                  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
                  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
                  <w:Compatibility>
                   <w:SplitPgBreakAndParaMark/>
                   <w:DontVertAlignCellWithSp/>
                   <w:DontBreakConstrainedForcedTables/>
                   <w:DontVertAlignInTxbx/>
                   <w:Word11KerningPairs/>
                   <w:CachedColBalance/>
                   <w:UseFELayout/>
                  </w:Compatibility>
                 </w:WordDocument>
                </xml><![endif]--><!--[if gte mso 9]><xml>
                 <w:LatentStyles DefLockedState=\"false\" LatentStyleCount=\"276\">
                 </w:LatentStyles>
                </xml><![endif]-->
                <style>
                <!--p.MSONORMAL
                    {mso-bidi-font-size:8pt;}
                li.MSONORMAL
                    {mso-bidi-font-size:8pt;}
                div.MSONORMAL
                    {mso-bidi-font-size:8pt;}
                p.SMALL
                    {mso-bidi-font-size:1pt;}

                 /* Font Definitions */
                @font-face
                    {font-family:Times;
                    panose-1:2 0 5 0 0 0 0 0 0 0;
                    mso-font-charset:0;
                    mso-generic-font-family:auto;
                    mso-font-pitch:variable;
                    mso-font-signature:3 0 0 0 1 0;}
                @font-face
                    {font-family:Verdana;
                    panose-1:2 11 6 4 3 5 4 4 2 4;
                    mso-font-charset:0;
                    mso-generic-font-family:auto;
                    mso-font-pitch:variable;
                    mso-font-signature:3 0 0 0 1 0;}
                @font-face
                    {font-family:Cambria;
                    panose-1:2 4 5 3 5 4 6 3 2 4;
                    mso-font-charset:0;
                    mso-generic-font-family:auto;
                    mso-font-pitch:variable;
                    mso-font-signature:3 0 0 0 1 0;}
                 /* Style Definitions */
                p.MsoNormal, li.MsoNormal, div.MsoNormal
                    {mso-style-parent:\"\";
                    margin:0cm;
                    margin-bottom:.0001pt;
                    mso-pagination:widow-orphan;
                    font-size:7.5pt;
                    font-family:Verdana;
                    mso-fareast-font-family:Verdana;
                    mso-bidi-font-family:\"Times New Roman\";
                    mso-bidi-theme-font:minor-bidi;}
                p.small, li.small, div.small
                    {mso-style-name:small;
                    mso-style-parent:\"\";
                    margin:0cm;
                    margin-bottom:.0001pt;
                    mso-pagination:widow-orphan;
                    font-size:1.0pt;
                    font-family:Verdana;
                    mso-fareast-font-family:Verdana;
                    mso-bidi-font-family:\"Times New Roman\";
                    mso-bidi-theme-font:minor-bidi;}
                span.SpellE
                    {mso-style-name:\"\";
                    mso-spl-e:yes;}
                @page Section1
                    {size:612.0pt 792.0pt;
                    margin:72.0pt 90.0pt 72.0pt 90.0pt;
                    mso-header-margin:35.4pt;
                    mso-footer-margin:35.4pt;
                    mso-paper-source:0;}
                div.Section1
                    {page:Section1;}
                -->
                </style>
                <!--[if gte mso 10]>
                <style>
                 /* Style Definitions */
                table.MsoNormalTable
                    {mso-style-name:\"Table Normal\";
                    mso-tstyle-rowband-size:0;
                    mso-tstyle-colband-size:0;
                    mso-style-noshow:yes;
                    mso-style-parent:\"\";
                    mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
                    mso-para-margin:0cm;
                    mso-para-margin-bottom:.0001pt;
                    mso-pagination:widow-orphan;
                    font-size:12.0pt;
                    font-family:Cambria;
                    mso-ascii-font-family:Cambria;
                    mso-ascii-theme-font:minor-latin;
                    mso-hansi-font-family:Cambria;
                    mso-hansi-theme-font:minor-latin;}
                </style>
                <![endif]--><!--[if gte mso 9]><xml>
                 <o:shapedefaults v:ext=\"edit\" spidmax=\"1027\">
                  <o:colormenu v:ext=\"edit\" strokecolor=\"none\"/>
                 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
                 <o:shapelayout v:ext=\"edit\">
                  <o:idmap v:ext=\"edit\" data=\"1\"/>
                 </o:shapelayout></xml><![endif]-->
                </head>

                <body lang=PT style='tab-interval:36.0pt'>
                <div class=Section1>{$this->_html}</div>
                </body>
                </html>
            ";

        exit();
    }
}