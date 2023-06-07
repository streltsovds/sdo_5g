<?php
    $target_dir = "scheme/dataschemes/";
    $type = explode("/", $_FILES["upload_files"]["type"]);
    $target_file = $target_dir . uniqid('scheme_') .'.'. $type[1];

    $path = $_SERVER['DOCUMENT_ROOT']."/upload/editor/";

    $parts = pathinfo($_FILES["upload_files"]['name']);

    $fname = date('dmHis').'.'.$parts['extension'];

    if(move_uploaded_file($_FILES["upload_files"]['tmp_name'], $path.$fname)) {
        //echo str_replace('scheme/', '', $target_file);
        die('/upload/editor/'.$fname);
    }
?>
