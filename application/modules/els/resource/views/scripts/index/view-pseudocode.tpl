<?php $this->headScript() //serverUrl
    ->prependFile( $this->baseUrl('/editor/inner/scheme/js/scheme.js') )
    ->prependFile( $this->baseUrl('/editor/inner/scheme/js/dragndrop.js') ) 
    ->prependFile( $this->baseUrl('/editor/inner/scheme/js/jquery.js') );
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$this->title?></title>
    <link rel="stylesheet" type="text/css" href="/editor/inner/scheme/css/schemes.css">
    <link rel="stylesheet" type="text/css" href="/editor/inner/scheme/css/dragndrop.css">
</head>
<body id="content">
    <main class="main"><?=$this->content?></main>
</body>
</html>