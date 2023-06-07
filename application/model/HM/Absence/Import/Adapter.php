<?php

class HM_Absence_Import_Adapter
{
    protected $_fileName = null;

    public function __construct($fileName)
    {
        $this->_fileName = $fileName;
    }


    public function prepare()
    {

        $handler  = fopen($this->_fileName, "r");
        $tempfile = tempnam(sys_get_temp_dir(), "ABST_");
        $fh       = fopen($tempfile, 'w+');

        while ($line = fgetcsv($handler,0,';')) {

            $userIdExternal = trim($line[0]);
            $type           = trim($line[1]);
            $begin          = trim($line[2]);
            $end            = trim($line[3]);

            $dt = array(
                $userIdExternal,
                $type,
                $begin,
                $end
            );

            fputcsv($fh, $dt, ';');
        }

        fclose($fh);

        return $tempfile;
    }

}
