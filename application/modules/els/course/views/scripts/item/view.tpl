<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="initial-scale=0.5, width=device-width, minimum-scale=0.1, user-scalable=no" />
        <style type="text/css">
            html,
            body {
                height: 100%;
                margin: 0;
                padding: 0;
                overflow: hidden;
                border: 0;
                outline: 0;
                background: transparent;
            }
            iframe {
                height: 100%;
                width: 100%;
                display: block;
                outline: 0;
                border: 0;
                margin: 0;
                padding: 0;
                background: transparent;
            }
        </style>
        <?php echo $this->action('scorm', 'api', 'course', array('course_id' => $this->courseId, 'item_id' => $this->item->oid, 'module_id' => $this->item->module, 'lesson_id' => $this->lessonId))?>
        <?php $this->headScript()->prependFile( $this->serverUrl('/js/api/jquery.min.js') ); ?>
        <?php echo $this->headLink()?>
        <?php echo $this->headStyle()?>
        <?php echo $this->headScript()?>
        <?php echo $this->inlineScript()?>
        <?php echo $this->jQuery()?>

        <script type="text/javascript">
            function getPrintPageOutOfSkillsoftCourses () {
                var printString;

                try {
                    printString = window.frames['main'].frames['Player']
                        .document.getElementById("Applet1")
                        .remoteControl("getPrintableText");
                } catch (e) {}

                printString && jQuery.ajax(<?php echo HM_Json::encodeErrorSkip($this->url(array('module' => 'course', 'controller' => 'api', 'action' => 'store-skillsoft-data')))?>, {
                    async: false,
                    global: false,
                    type: 'POST',
                    //contentType: 'text/plain',
                    //timeout: 1500, DANGEROUS,
                    data: { lesson_id: <?php echo HM_Json::encodeErrorSkip($this->lessonId)?>, html: printString }
                });
            }
            (window.API && "function" == typeof window.API.LMSFinish) && (function () {
                var oldTerminate = window.API.LMSFinish;
                window.API.LMSFinish = function (arg) {
                    try {
                        getPrintPageOutOfSkillsoftCourses();
                    } catch (e) {}
                    return oldTerminate.call(window.API, arg);
                }
            })();
            (window.API_1484_11 && "function" == typeof window.API_1484_11.Terminate) && (function () {
                var oldTerminate = window.API_1484_11.Terminate;
                window.API_1484_11.Terminate = function (arg) {
                    try {
                        getPrintPageOutOfSkillsoftCourses();
                    } catch (e) {}
                    return oldTerminate.call(window.API_1484_11, arg);
                }
            })();
        </script>

        <script type="text/javascript">
            //if (typeof parent.eLearning_server_metadata != 'undefined') {
            //    parent.eLearning_server_metadata.coursexml = '<?php echo $this->baseUrl('COURSES/course'.$this->courseId.'/course.xml') ?>';
            //}
            (!window.parent || window.parent == window) && (function () {
                var interval
                  , overloadClose;
                overloadClose = function () {
                    var check = false;
                    try {
                        check = window.frames['main'] && window.frames['main'].close && !window.frames['main'].close.itemView;
                    } catch (error) {}

                    if (check) {
                        overloadClose.oldClose = window.frames['main'].close;
                        window.frames['main'].close = function () {
                            try { overloadClose.oldClose.call(window.frames['main']); } catch (error) {}
                            window.close();
                        }
                        window.frames['main'].close.itemView = true;
                    }
                };
                interval = setInterval(overloadClose, 100);
            })();
        </script>

        <script type="text/javascript">
        <?php if ($this->resource && $this->resource->external_viewer == HM_Resource_ResourceModel::EXTERNAL_VIEWER_GOOGLE): ?>
            $(document).ready(function () {
                var timerId = setInterval(function() {
                    var iframe = document.getElementsByTagName("iframe")[0];

                    if (iframe && iframe.contentDocument && iframe.contentDocument.URL == "about:blank") {
                        iframe.src = '<?php echo $this->iframeUrl; ?>';
                    } else {
                        clearInterval(timerId);
                    }
                }, 2000);
            });
        <?php endif; ?>
        </script>

        <?php if ($this->emulateScorm) { ?>
        <script type="text/javascript">
            $(function(){
                //эмуляция SCORM 1.2
                if (window.API) {
                    window.API.LMSInitialize('');

                    $(window).unload(function(){
                        window.API.LMSSetValue("cmi.core.score.raw", 100);
                        window.API.LMSSetValue("cmi.core.score.max", 100);
                        window.API.LMSSetValue("cmi.core.score.min", 100);
                        window.API.LMSFinish('');
                    });
                }
                //эмуляция SCORM 1.3
                if (window.API_1484_11) {
                    window.API_1484_11.Initialize('');

                    $(window).unload(function(){
                        window.API_1484_11.SetValue("cmi.completion_status", "completed");
                        window.API_1484_11.SetValue("cmi.score.raw", 100);
                        window.API_1484_11.SetValue("cmi.score.max", 100);
                        window.API_1484_11.SetValue("cmi.score.min", 100);
                        window.API_1484_11.Terminate('');
                    });
                }
            });
        </script>
        <?php } ?>
    </head>
    <body>
        <iframe name="main" frameborder="0" src="<?php echo $this->executeUrl; ?>"></iframe>
    </body>
</html>