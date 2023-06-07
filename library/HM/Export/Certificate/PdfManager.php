<?php

class HM_Export_Certificate_PdfManager extends HM_Export_PdfManager
{
    public function createPdf($template)
    {
        return $this->sendToPdflib($template);
    }
}
