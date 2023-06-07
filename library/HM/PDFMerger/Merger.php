<?php
    require_once 'PDFMerger/PDFMerger.php';

class HM_PDFMerger_Merger
{
    public function mergeSessionPDFs() {
        $pdf = new PDFMerger;
        $pdf->addPDF('C:/Projects/develop/4g/data/upload/reports/11/101.pdf', 'all');
        $pdf->addPDF('C:/Projects/develop/4g/data/upload/reports/11/101.pdf', 'all');
        $pdf->addPDF('C:/Projects/develop/4g/data/upload/reports/11/101.pdf', 'all');
        $pdf->addPDF('C:/Projects/develop/4g/data/upload/reports/11/101.pdf', 'all');
        $pdf->addPDF('C:/Projects/develop/4g/data/upload/reports/11/101.pdf', 'all');
         $pdf->merge('file','C:/Projects/develop/4g/data/upload/reports/11/TEST2.pdf');
    }
}