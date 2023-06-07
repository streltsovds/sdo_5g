<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tincan Курс</title>
    <link rel="stylesheet" href="/hm/css/themes/default/screen.css">
    <style>
        html {
            font-size: 14px;
            overflow-x: hidden;
            overflow-y: hidden;
            height: 100%;
        }
        body {
            font-family: "Open Sans", -apple-system , BlinkMacSystemFont , Segoe UI, Helvetica, Arial ,sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol;
            padding: 0;
            margin: 0;
            height: 100%;
        }
        .hm-tmw-footer:empty {
            display: none;
        }
        .hm-tmw-row-footer {
            display: none;
        }
        #ZFDebug_debug {
            display: none !important;
        }
    </style>
</head>
<body>
    <iframe id="tincanViewer" width="100%" height="100%"></iframe>
    <script src="/js/lib/jquery/jquery-1.7.2.min.js"></script>
    <script>
        $('#tincanViewer').attr('src', `<?= $this->courseUrl ?>`);
    </script>
</body>
</html>
