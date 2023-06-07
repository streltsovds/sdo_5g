<?php echo $this->doctype();?>
<html lang="ru">
    <?php
    /**
     * Используется render, а не partial, т. к. нам нужно
     * закодировать все переменные вида для передачи их во Vue
     */
     ?>
    <?php echo $this->render('partials/head.tpl');?>
    <body style="margin: 0; padding: 0">
    <div class="loader" v-cloak >
        <div style="position: absolute;left:50%;top:50%;transform: translate(-50%, -50%);">
            <svg id="Layer_1" viewBox="0 0 358 358" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M179 353.28C275.252 353.28 353.28 275.252 353.28 179C353.28 82.7477 275.252 4.71983 179 4.71983C82.7477 4.71983 4.71983 82.7477 4.71983 179C4.71983 275.252 82.7477 353.28 179 353.28Z"
                      fill="white" stroke="rgba(0, 165, 81, 0.15)" stroke-width="8.56034" style="animation:LOADER_OPACITY_Stroke 2000ms ease-in 0ms infinite"/>
                <path d="M120.379 259.912C117.741 259.912 115.103 258.74 113.345 256.981C111.586 255.222 110.414 252.584 110.414 249.946V172.274C110.414 166.705 114.81 162.308 120.379 162.308C125.948 162.308 130.345 166.705 130.345 172.274V239.981H230.586V172.274C230.586 169.636 231.759 166.998 233.517 165.239C235.276 163.481 237.914 162.308 240.552 162.308H256.966L180.466 98.4119L88.7241 174.912C84.3276 178.429 78.1724 177.843 74.6552 173.74C71.1379 169.343 71.7241 163.188 75.8276 159.671L174.017 77.8947C177.828 74.6705 183.103 74.6705 186.914 77.8947L285.103 159.671C287.448 161.722 288.621 164.36 288.621 167.291V172.567C288.621 178.136 284.224 182.533 278.655 182.533H250.81V250.239C250.81 252.877 249.638 255.515 247.879 257.274C246.121 259.033 243.483 260.205 240.845 260.205H120.379V259.912Z"
                      fill="rgba(0, 165, 81, 0.15)" style="animation:LOADER_OPACITY_Fill 2000ms ease-in 0ms infinite"/>
            </svg>
            <style data-made-with="vivus-instant">
                @keyframes LOADER_OPACITY_Stroke {0%{stroke: rgba(0, 165, 81, 0.15)} 50%{stroke: rgba(0, 165, 81, 0.25)} 100%{stroke: rgba(0, 165, 81, 0.15)}
                @keyframes LOADER_OPACITY_Fill {0%{fill: rgba(0, 165, 81, 0.15)} 50%{fill: rgba(0, 165, 81, 0.55)} 100%{fill: rgba(0, 165, 81, 0.15)}
            </style>
        </div>
    </div>


<!--    <div class="loader" v-cloak>-->
<!--        <div style="width: 200px;height:200px;position: absolute;left:50%;top:50%;transform: translate(-50%, -50%);">-->
<!--            <svg id="Layer_1"-->
<!--                 style="width: 200px;height:200px;"-->
<!--                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">-->
<!--                <path fill="none" stroke="#00A551" stroke-width="4" d="M5 50a45 45 0 1 1 90 0 45 45 0 1 1-90 0"-->
<!--                      style="animation:NDECXEIV_draw_0 5000ms ease-in 0ms infinite,NDECXEIV_fade 5000ms linear 0ms infinite" stroke-dasharray="283 285" stroke-dashoffset="284"/>-->
<!--                <path fill="none" stroke="#00A551" stroke-width="2.5" d="M31.2 76.8c-.9 0-1.7-.3-2.4-.9-.6-.6-1-1.5-1-2.4V48.4c0-1.8 1.5-3.3 3.3-3.3 1.8 0 3.3 1.5 3.3 3.3v21.9h32.5V48.4c0-.9.3-1.7.9-2.4.6-.6 1.5-.9 2.4-.9h5.3L50.6 24.4 20.9 49.2c-1.3 1.1-3.5 1-4.6-.4-1.1-1.3-1-3.5.4-4.6l31.8-26.5c1.2-1 2.9-1 4.2 0l31.8 26.5c.8.7 1.1 1.6 1.1 2.5v1.7c0 1.8-1.5 3.3-3.3 3.3h-9.1v21.9c0 .9-.3 1.7-.9 2.4-.6.6-1.5.9-2.4.9H31.2v-.1z"-->
<!--                      style="animation:NDECXEIV_draw_1 5000ms ease-in 0ms infinite,NDECXEIV_fade 5000ms linear 0ms infinite" stroke-dasharray="381 383" stroke-dashoffset="382"/>-->
<!--                <style data-made-with="vivus-instant">@keyframes NDECXEIV_draw{to{stroke-dashoffset:0}}@keyframes NDECXEIV_fade{0%,80%{stroke-opacity:1}to{stroke-opacity:0}}@keyframes NDECXEIV_draw_0{0%{stroke-dashoffset:284}60%,to{stroke-dashoffset:0}}@keyframes NDECXEIV_draw_1{20%{stroke-dashoffset:382}80%,to{stroke-dashoffset:0}}</style>-->
<!--            </svg>-->
<!--        </div>-->
<!--    </div>-->



    <?php
    /**
     * Сюда, в `#hm-vue-app` неявно, сам, монтируется компонент `frontend/app/src/app.js`
     *
     * Данные со стороны php он получает через `window.__HM.php_view_vars`,
     * которые записываются в `partials/head.tpl`
     */
?>
        <div v-cloak id="hm-vue-app" :class="appComputedAppCssClasses" class="app-layout-default">
            <hm-hotkey keys="Esc" @pressed="appMethodHideCloseAndSidebar" on-event-name="keyup" ></hm-hotkey>
            <v-app>
                <!-- применение палитры цветов из конфига -->
                <hm-app-styles></hm-app-styles>

                <?php echo $this->partial('partials/header.tpl');?>
                <v-main
                    class="hm-app-content"
                    :class="isContentBeingHovered ? 'sidebar-is-hovering' : null"
                    style="
                        width: 100%;
                        <?php if($this->contentBackground): ?>
                                background: linear-gradient( rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3) ), url('<?php echo $this->contentBackground; ?>');
                                background-size: cover;
                                background-position: center;
                        <?php endif; ?>"
                    app
                >
                    <v-container
                        class="wrapper-headers-and-content"
                        :class="appComputedContentWrapperClasses"
                        fill-height
                        fluid
                        style="{
                            width: 100%;
                        }"
                    >
                        <v-layout column fill-height>
                            <?php if ($this->getSubHeader()): ?>
                                <v-flex class="layout-content-header" style="flex-grow: 0">
                                    <span class="header__headline-3">
                                        <?php echo $this->getSubHeader();?>
                                    </span>
                                </v-flex>
                            <?php endif;?>
                            <?php if (
                                ($contextNavigation = $this->getContextNavigation()) &&
                                $contextNavigation->hasVisiblePages()
                            ):?>
                                <div class="navigation__button">
                                    <?php echo $this->contextMenu($contextNavigation);?>
                                </div>
                            <?php endif;?>

                            <v-flex class="d-flex flex-grow-0 flex-row">
                                <?php if($this->getSubSubHeader()): ?>
                                    <v-flex  class="layout-content-header" style="flex-grow: 0;">
                                        <span class="header__headline-1">
                                            <?php echo $this->getSubSubHeader();?>
                                        </span>
                                    </v-flex>
                                <?php endif ?>

                                <v-flex>
                                    <?php echo $this->modes($this->getModesNavigation()); ?>
                                </v-flex>
                            </v-flex>

                            <v-flex>
                                <?php echo $this->layout()->content;?>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-main>
                <?php echo $this->partial('partials/footer.tpl');?>
            </v-app>
            <hm-modal-confirm></hm-modal-confirm>
            <hm-alerts></hm-alerts>
<!--            ниже массив с ошибкой -->
            <hm-notifications :notifications='<?php echo $this->notifications ? $this->notifications : "[]"; ?>'></hm-notifications>
        </div>
        <style>
            .loader div,
            .loader svg {
                width: 358px;
                height: 358px;
            }
            @media(max-width: 768px) {
                .loader div,
                .loader svg {
                    width: 265px;
                    height: 265px;
                }
            }
            .notifications-badge .v-badge__badge {
                height: 20px; width: 20px;
                right: -10px;
            }
            .bottom-gradient {
                -webkit-box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.2);
                box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.2);
                background-image: -webkit-linear-gradient(bottom, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 10%, rgba(0, 0, 0, 0.15) 15%, transparent 72px);
                background-image: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 10%, rgba(0, 0, 0, 0.15) 15%, transparent 72px);
            }
             /* Custom style */
            .v-navigation-drawer .no-padd-inside .v-list__tile {
                 padding-left: 2rem;
             }
            .default-subject-icon .v-image__image {
                background-size: 100px 100px;
            }


        </style>

        <script>
          // Общие данные для Vue. Пример использования: `:show="view.showMainLayout"`
          // их нужно выводить в самом низу
          <?php
            $this->showMainLayout = $this->hmVue()->showMainLayout();
          ?>

          <?php
            // JSON_INVALID_UTF8_SUBSTITUTE:
            // PHP 7.2+ required https://www.php.net/manual/ru/function.json-encode.php
          ?>
          window.__HM.php_view_vars = <?php echo HM_Json::encodeErrorThrow($this->getViewVars()); ?>;
          window.__HM.php_config_colors = <?php echo HM_Json::encodeErrorThrow($this->getDesignSetting('colors')); ?>;
        </script>

        <?php echo $this->VueScript(); ?>

        <?php echo $this->headScript(); ?>

        <?php echo $this->inlineScript(); ?>

        <script>
            /* проверка работы скриптов */
            window.addEventListener('DOMContentLoaded', function () {
                /* возвращаем к работе консоль*/
                if (window.hm) {
                    window.console = window.hm.core.Console;
                }
                console.log('DOMContentLoaded!');
                document.querySelector('.loader').removeAttribute('v-cloak');
            });
        </script>
    </body>
</html>
