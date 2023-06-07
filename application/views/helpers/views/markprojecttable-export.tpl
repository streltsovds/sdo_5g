<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
xmlns:css="http://macVmlSchemaUri" xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta name=Title content="">
<meta name=Keywords content="">
<meta http-equiv=Content-Type content="text/html; charset=<?php echo Zend_Registry::get('config')->charset?>">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 2008">
<meta name=Originator content="Microsoft Word 2008">
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
 <w:LatentStyles DefLockedState="false" LatentStyleCount="276">
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
	{mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:7.5pt;
	font-family:Verdana;
	mso-fareast-font-family:Verdana;
	mso-bidi-font-family:"Times New Roman";
	mso-bidi-theme-font:minor-bidi;}
p.small, li.small, div.small
	{mso-style-name:small;
	mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:1.0pt;
	font-family:Verdana;
	mso-fareast-font-family:Verdana;
	mso-bidi-font-family:"Times New Roman";
	mso-bidi-theme-font:minor-bidi;}
span.SpellE
	{mso-style-name:"";
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
	{mso-style-name:"Table Normal";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:"";
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
 <o:shapedefaults v:ext="edit" spidmax="1027">
  <o:colormenu v:ext="edit" strokecolor="none"/>
 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=PT style='tab-interval:36.0pt'>

	<?php $totalSchedules = count($this->schedules); ?>
	<?php $totalPersons = count($this->persons); ?>
	
	<table cellspacing="0" cellpadding="0" border="1px">
	    <colgroup><col><col><col span="<?php echo $totalSchedules;?>"></colgroup>
	    <thead>
	        <tr>
	            <td></td>
	            <?php foreach($this->schedules as $key => $schedule):?>
	            <td>
	                <div style="margin:5px"><?php echo $this->escape($schedule->title)?></div>
	            </td>
	            <?php endforeach;?>
	            <td><div style="margin:5px">Место</div></td>
	        </tr>
	        <tr>
	            <th><div style="margin:5px"><?php echo _('ФИО');?></div></th>
	            <?php foreach($this->schedules as $key => $schedule):?>
	            <td></td>
	            <?php endforeach;?>
	            <td></td>
	        </tr>
	    </thead>
	    <tbody>
	        <?php
	        $flag = 0;
	        foreach($this->persons as $key => $person):?>
            <?php
            $flag = !$flag;
            $color = ($flag) ? '#C9C9C9' : '#FFFFFF';
            ?>
	        <tr style="background-color:<?php echo $color;?> ">
	            <td><div style="margin:5px"><?php echo $this->escape($person->getName());?></td>
	            <?php
	            foreach($this->schedules as $schedule):?>
	            <td><div style="margin:5px">
	                <?php if (isset($this->scores[$key.'_'.$schedule->meeting_id]) && $this->scores[$key.'_'.$schedule->meeting_id]->V_STATUS > -1):?>
	                <?php echo $this->scores[$key.'_'.$schedule->meeting_id]->V_STATUS;?>
	                <?php endif;?>
                    </div>
	            </div></td>
	            <?php endforeach;?>
	            <td><div style="margin:5px"><?php if($this->scores[$key."_total"]['mark'] > -1) echo $this->scores[$key."_total"]['mark'];?></div></td>
	        </tr>
	        <?php endforeach;?>
	    </tbody>
	</table>
	
	<script type="text/javascript">
	<!--
	window.onload = function cleanPage(){ document.getElementById('ZFDebug_debug').innerHTML = '';};
	//-->
	</script>
	
	
</body>
</html>