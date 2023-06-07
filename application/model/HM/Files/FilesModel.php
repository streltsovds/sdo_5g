<?php
// migration:
//update files set item_type='4' where item_type='certificate'
//update files set item_type='2' where item_type='process_state_data'

class HM_Files_FilesModel extends HM_Model_Abstract
{
    const FILETYPE_UNKNOWN  = 'unknown';
    const FILETYPE_TEXT     = 'text';
    const FILETYPE_HTML     = 'html';
    const FILETYPE_IMAGE    = 'image';
    const FILETYPE_AUDIO    = 'audio';
    const FILETYPE_VIDEO    = 'video';
    const FILETYPE_FLASH    = 'flash';
    const FILETYPE_DOC      = 'doc';
    const FILETYPE_XLS      = 'xls';
    const FILETYPE_XLSX     = 'xlsx';
    const FILETYPE_PPT      = 'ppt';
    const FILETYPE_PDF      = 'pdf';
    const FILETYPE_ZIP      = 'zip';

    const ITEM_TYPE_SUBJECT            = 1; //'subject';
    const ITEM_TYPE_PROCESS_STATE_DATA = 2; //'process_state_data';
    const ITEM_TYPE_TC_TEACHER         = 3; //'tc/teacher';
    const ITEM_TYPE_CERTIFICATE        = 4; //'tc/teacher';
    const ITEM_TYPE_TASK_VARIANT       = 5;
    const ITEM_TYPE_TASK_CONVERSATION  = 6;
    const ITEM_TYPE_IDEA = 7;
    const ITEM_TYPE_COURSES_FREE = 8;

    public static function getAvailMimeTypes()
    {
        return [
            "323"     => "text/h323",
            "acx"     => "application/internet-property-stream",
            "ai"      => "application/postscript",
            "aif"     => "audio/x-aiff",
            "aifc"    => "audio/x-aiff",
            "aiff"    => "audio/x-aiff",
            "asf"     => "video/x-ms-asf",
            "asr"     => "video/x-ms-asf",
            "asx"     => "video/x-ms-asf",
            "au"      => "audio/basic",
            "avi"     => "video/x-msvideo",
            "axs"     => "application/olescript",
            "bas"     => "text/plain",
            "bcpio"   => "application/x-bcpio",
            "bin"     => "application/octet-stream",
            "bmp"     => "image/bmp",
            "c"       => "text/plain",
            "cat"     => "application/vnd.ms-pkiseccat",
            "cdf"     => "application/x-cdf",
            "cer"     => "application/x-x509-ca-cert",
            "class"   => "application/octet-stream",
            "clp"     => "application/x-msclip",
            "cmx"     => "image/x-cmx",
            "cod"     => "image/cis-cod",
            "cpio"    => "application/x-cpio",
            "crd"     => "application/x-mscardfile",
            "crl"     => "application/pkix-crl",
            "crt"     => "application/x-x509-ca-cert",
            "csh"     => "application/x-csh",
            "css"     => "text/css",
            "dcr"     => "application/x-director",
            "der"     => "application/x-x509-ca-cert",
            "dir"     => "application/x-director",
            "dll"     => "application/x-msdownload",
            "dms"     => "application/octet-stream",
            "doc"     => "application/msword",
            "docx"    => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "dot"     => "application/msword",
            "dvi"     => "application/x-dvi",
            "dxr"     => "application/x-director",
            "eps"     => "application/postscript",
            "etx"     => "text/x-setext",
            "evy"     => "application/envoy",
            "exe"     => "application/octet-stream",
            "fif"     => "application/fractals",
            "flr"     => "x-world/x-vrml",
            "gif"     => "image/gif",
            "gtar"    => "application/x-gtar",
            "gz"      => "application/x-gzip",
            "h"       => "text/plain",
            "hdf"     => "application/x-hdf",
            "hlp"     => "application/winhlp",
            "hqx"     => "application/mac-binhex40",
            "hta"     => "application/hta",
            "htc"     => "text/x-component",
            "htm"     => "text/html",
            "html"    => "text/html",
            "htt"     => "text/webviewhtml",
            "ico"     => "image/x-icon",
            "ief"     => "image/ief",
            "iii"     => "application/x-iphone",
            "ins"     => "application/x-internet-signup",
            "isp"     => "application/x-internet-signup",
            "jfif"    => "image/pipeg",
            "jpe"     => "image/jpeg",
            "jpeg"    => "image/jpeg",
            "jpg"     => "image/jpeg",
            "js"      => "application/x-javascript",
            "latex"   => "application/x-latex",
            "lha"     => "application/octet-stream",
            "lsf"     => "video/x-la-asf",
            "lsx"     => "video/x-la-asf",
            "lzh"     => "application/octet-stream",
            "m13"     => "application/x-msmediaview",
            "m14"     => "application/x-msmediaview",
            "m3u"     => "audio/x-mpegurl",
            "man"     => "application/x-troff-man",
            "mdb"     => "application/x-msaccess",
            "me"      => "application/x-troff-me",
            "mht"     => "message/rfc822",
            "mhtml"   => "message/rfc822",
            "mid"     => "audio/mid",
            "mny"     => "application/x-msmoney",
            "mov"     => "video/quicktime",
            "movie"   => "video/x-sgi-movie",
            "mp2"     => "video/mpeg",
            "mp3"     => "audio/mpeg",
            "mpa"     => "video/mpeg",
            "mpe"     => "video/mpeg",
            "mpeg"    => "video/mpeg",
            "mpg"     => "video/mpeg",
            "mpp"     => "application/vnd.ms-project",
            "mpv2"    => "video/mpeg",
            "ms"      => "application/x-troff-ms",
            "mvb"     => "application/x-msmediaview",
            "nws"     => "message/rfc822",
            "oda"     => "application/oda",
            "p10"     => "application/pkcs10",
            "p12"     => "application/x-pkcs12",
            "p7b"     => "application/x-pkcs7-certificates",
            "p7c"     => "application/x-pkcs7-mime",
            "p7m"     => "application/x-pkcs7-mime",
            "p7r"     => "application/x-pkcs7-certreqresp",
            "p7s"     => "application/x-pkcs7-signature",
            "pbm"     => "image/x-portable-bitmap",
            "pdf"     => "application/pdf",
            "pfx"     => "application/x-pkcs12",
            "pgm"     => "image/x-portable-graymap",
            "pko"     => "application/ynd.ms-pkipko",
            "pma"     => "application/x-perfmon",
            "pmc"     => "application/x-perfmon",
            "pml"     => "application/x-perfmon",
            "pmr"     => "application/x-perfmon",
            "pmw"     => "application/x-perfmon",
            "pnm"     => "image/x-portable-anymap",
            "pot"     => "application/vnd.ms-powerpoint",
            "ppm"     => "image/x-portable-pixmap",
            "pps"     => "application/vnd.ms-powerpoint",
            "ppt"     => "application/vnd.ms-powerpoint",
            "prf"     => "application/pics-rules",
            "ps"      => "application/postscript",
            "pub"     => "application/x-mspublisher",
            "qt"      => "video/quicktime",
            "ra"      => "audio/x-pn-realaudio",
            "ram"     => "audio/x-pn-realaudio",
            "ras"     => "image/x-cmu-raster",
            "rgb"     => "image/x-rgb",
            "rmi"     => "audio/mid",
            "roff"    => "application/x-troff",
            "rtf"     => "application/rtf",
            "rtx"     => "text/richtext",
            "scd"     => "application/x-msschedule",
            "sct"     => "text/scriptlet",
            "setpay"  => "application/set-payment-initiation",
            "setreg"  => "application/set-registration-initiation",
            "sh"      => "application/x-sh",
            "shar"    => "application/x-shar",
            "sit"     => "application/x-stuffit",
            "snd"     => "audio/basic",
            "spc"     => "application/x-pkcs7-certificates",
            "spl"     => "application/futuresplash",
            "src"     => "application/x-wais-source",
            "sst"     => "application/vnd.ms-pkicertstore",
            "stl"     => "application/vnd.ms-pkistl",
            "stm"     => "text/html",
            "svg"     => "image/svg+xml",
            "sv4cpio" => "application/x-sv4cpio",
            "sv4crc"  => "application/x-sv4crc",
            "t"       => "application/x-troff",
            "tar"     => "application/x-tar",
            "tcl"     => "application/x-tcl",
            "tex"     => "application/x-tex",
            "texi"    => "application/x-texinfo",
            "texinfo" => "application/x-texinfo",
            "tgz"     => "application/x-compressed",
            "tif"     => "image/tiff",
            "tiff"    => "image/tiff",
            "tr"      => "application/x-troff",
            "trm"     => "application/x-msterminal",
            "tsv"     => "text/tab-separated-values",
            "txt"     => "text/plain",
            "uls"     => "text/iuls",
            "ustar"   => "application/x-ustar",
            "vcf"     => "text/x-vcard",
            "vrml"    => "x-world/x-vrml",
            "wav"     => "audio/x-wav",
            "wcm"     => "application/vnd.ms-works",
            "wdb"     => "application/vnd.ms-works",
            "wks"     => "application/vnd.ms-works",
            "wmf"     => "application/x-msmetafile",
            "wps"     => "application/vnd.ms-works",
            "wri"     => "application/x-mswrite",
            "wrl"     => "x-world/x-vrml",
            "wrz"     => "x-world/x-vrml",
            "xaf"     => "x-world/x-vrml",
            "xbm"     => "image/x-xbitmap",
            "xla"     => "application/vnd.ms-excel",
            "xlc"     => "application/vnd.ms-excel",
            "xlm"     => "application/vnd.ms-excel",
            "xls"     => "application/vnd.ms-excel",
            "xlt"     => "application/vnd.ms-excel",
            "xlw"     => "application/vnd.ms-excel",
            "xof"     => "x-world/x-vrml",
            "xpm"     => "image/x-xpixmap",
            "xwd"     => "image/x-xwindowdump",
            "z"       => "application/x-compress",
            "zip"     => "application/zip",
            'mp4'     => 'video/mp4',
            'flv'     => 'video/x-flv',
            'swf'     => 'application/x-shockwave-flash'
        ];
    }

    public function getUrl()
    {
        $fileId = $this->file_id;
        $dest   = realpath(APPLICATION_PATH . '/../public/upload/files/');
        $glob   = glob($dest . '/' . $fileId . '.*');
        if (count($glob) == 0) {
            return false;
        }
        $url = $GLOBALS['sitepath'] . 'upload/files/' . basename($glob[0]);
        return $url;
    }

    public function getDeleteUrl()
    {
        return Zend_Registry::get('view')->url([
            'module' => 'file',
            'controller' => 'upload',
            'action' => 'delete',
            'file_id' => $this->file_id,
        ]);
    }

    public function getThisMimeType()
    {
        return self::getMimeType($this->name);
    }

    static public function getMimeType($filename)
    {
        $explode = explode('.', $filename);
        $ext     = strtolower(array_pop($explode));
        $mimes   = self::getAvailMimeTypes();
        if (isset($mimes[$ext])) {
            return $mimes[$ext];
        } else {
            return "application/octet-stream";
        }
    }

    public function getThisFileType()
    {
        return self::getFileType($this->name);
    }

    static public function getFileType($filename)
    {
        $explode = explode('.', $filename);
        $ext = strtolower(array_pop($explode));
        switch ($ext) {
            case 'txt':
                return HM_Files_FilesModel::FILETYPE_TEXT;
            case 'htm':
            case 'html':
            case 'xml':
                return HM_Files_FilesModel::FILETYPE_HTML;
            case 'bmp':
            case 'png':
            case 'gif':
            case 'jpg':
            case 'jpeg':
            case 'svg':
                return HM_Files_FilesModel::FILETYPE_IMAGE;
            case 'mp3':
                return HM_Files_FilesModel::FILETYPE_AUDIO;
            case 'mov':
            case 'mpg':
            case 'mpeg':
            case 'mp4':
            case 'webm':
            case 'wmv':
                return HM_Files_FilesModel::FILETYPE_VIDEO;
            case 'flv':
            case 'swf':
                return HM_Files_FilesModel::FILETYPE_FLASH;
//        case 'csv':
            case 'xls':
                return HM_Files_FilesModel::FILETYPE_XLS;
            case 'xlsx':
                return HM_Files_FilesModel::FILETYPE_XLSX;
            case 'rtf':
            case 'doc':
            case 'docx':
                return HM_Files_FilesModel::FILETYPE_DOC;
            case 'ppt':
            case 'pptx':
                return HM_Files_FilesModel::FILETYPE_PPT;
            case 'pdf':
                return HM_Files_FilesModel::FILETYPE_PDF;
            case 'zip':
                return HM_Files_FilesModel::FILETYPE_ZIP;
        }
        return self::FILETYPE_UNKNOWN;
    }

    static public function getFileTypeString($filename) {
        return self::fileTypeToString(self::getFileType($filename));
    }

    public function isViewable()
    {
        $viewable = [
            self::FILETYPE_AUDIO,
            self::FILETYPE_FLASH,
            self::FILETYPE_HTML,
            self::FILETYPE_IMAGE,
            self::FILETYPE_PDF,
            self::FILETYPE_TEXT,
            self::FILETYPE_VIDEO
        ];

        if (in_array($this->getThisFileType(), $viewable)) {
            return true;
        }
        return false;
    }

    static public function toByteString($size)
    {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size >= 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $sizes[$i];
    }

    static public function fileTypeToStringTable() {
        return [
            self::FILETYPE_UNKNOWN => "default",
            self::FILETYPE_TEXT => "text",
            self::FILETYPE_HTML => "web",
            self::FILETYPE_IMAGE => "image",
            self::FILETYPE_AUDIO => "audio",
            self::FILETYPE_VIDEO => "video",
            self::FILETYPE_FLASH => "flash",
            self::FILETYPE_DOC => "document",
            self::FILETYPE_XLS => "table",
            self::FILETYPE_XLSX => "table",
            self::FILETYPE_PPT => "presentation",
            self::FILETYPE_PDF => "pdf",
            self::FILETYPE_ZIP => "archive",
        ];
    }

    static public function fileTypeToString($fileType) {
        $s = self::fileTypeToStringTable();
        return isset($s[$fileType]) ? $s[$fileType] : "default";
    }

    static public function isConvertableToPdf($fileTypeString) {
        return in_array($fileTypeString, ["table", "document", "presentation"]);
    }

    public function getDataForVueFile()
    {
        $uploadedItem = new HM_DataType_Form_Element_Vue_UploadedItem();
        $file = new HM_DataType_Form_Element_Vue_FileInfo();
        $uploadedItem->file = $file;

        $file->name = $this->name;
        $file->mimeType = $this->getThisMimeType();
        $file->size = $this->file_size;
        $file->url = Zend_Registry::get('view')->baseUrl($this->getUrl());
        $file->previewUrl = Zend_Registry::get('view')->baseUrl($this->getUrl());
        $file->deleteUrl = $this->getDeleteUrl();
        $file->type = $this->getThisFileType();

        return $uploadedItem;
    }
}
