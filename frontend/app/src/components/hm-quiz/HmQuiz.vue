<template>
  <component :is="isLegacy ? `v-app` : `div`" :class="{ isIe: isIe }" class="hm-test">
    <!-- <bootloading v-if="isLoading" fill="#FFFFFF"/> -->

    <!--
      hm-test должен рендериться на странице с layout без отступа (`layout-content-full-width`)
      Задаётся в `HM_Controller_Action_Trait_Multipage::viewAction()`: `layoutContentFullWidth`

      См. класс отступа `.wrapper-headers-and-content`: в `src/scss/layout.scss`

      Так же применяется в:
      `src/components/hm-test/TestController.vue` для `<test-body>`

      TODO может быть придумать что-то получше
    -->

    <test-header
      class="wrapper-headers-and-content"

      v-bind="testHeaderProps"
      @finalize="finalizeInTestHeaderStatus(true)"
    />
    <test-error v-if="isErorred" :error="error" @close="handleErrorClose" />
    <div class="pb-3 mt-2" style="margin-top: 0!important;">
      <test-controller
        v-if="testModel && !hasErrorOnLoad && !isLoading"
        v-bind="testControllerProps"
        :finalizeInHmTestInHeader="finalizeInTestHeader"
        @finalizeInTestHeaderStatus="finalizeInTestHeaderStatus"
      />
      <p v-if="text">{{text}}</p>
    </div>
  </component>
</template>

<script>
    import { mapActions } from "vuex";
    import Vue from "vue";
    import axios from "axios";
    const isInLegacyMode = process.env.IS_TEST;

    // @TODO delete after integration in other project!
    if (isInLegacyMode) {
        if (!window.Vue) {
            window.Vue = Vue;
        }
        axios.defaults.headers.common["XREQUESTEDWITH"] = "XMLHttpRequest";
    }

    const inProduction = process.env.NODE_ENV === "production";

    const UAParser = require("ua-parser-js");

    // @TODO delete after integration in other project!
    import Vuetify from "vuetify";

    import ru from "vuetify/lib/locale/ru";
    // import "./main.styl";

    const vuetifyConfig = {
        lang: {
            locales: { ru },
            current: "ru"
        },
        theme: {
            primary: "#005db8",
            secondary: "#6d6e71",
            accent: "#ed8b00",
            purple: "#4e2683",
            lightpurple: "#9283be",
            lightblue: "#00aced",
            info: "#00aced",
            green: "#48a23f",
            success: "#48a23f",
            red: "#da291c",
            error: "#da291c",
            yellow: "#f3d03e",
            warning: "#f3d03e",
            pink: "#d3007d"
        },
        options: {
            minifyTheme: function(css) {
                return process.env.NODE_ENV === "production"
                    ? css.replace(/[\s|\r\n|\r|\n]/g, "")
                    : css;
            }
        }
    };

    if (isInLegacyMode) {
        Vue.use(Vuetify, vuetifyConfig);
    }
    // @TODO delete after integration in other project!

    import smoothscroll from "smoothscroll-polyfill";
    // kick off the polyfill!
    // https://github.com/iamdustan/smoothscroll
    smoothscroll.polyfill();

    Vue.prototype.$uaparser = new UAParser();

    Vue.config.errorHandler = function(err, vm, info) {
        // handle error
        // `info` is a Vue-specific error info, e.g. which lifecycle hook
        // the error was found in. Only available in 2.2.0+
        console.error(err);
        console.info(`Vue Instance`, vm);
        console.info(`Error happened at ${info}.`);
    };

    if (isInLegacyMode) {
        // Just until integration done
        Vue.prototype.$log = Vue.prototype.$log
            ? Vue.prototype.$log
            : {
                debug: inProduction ? () => {} : console.log,
                log: inProduction ? () => {} : console.log,
                error: console.error,
                info: console.log,
                fatal: console.log,
                warn: console.log
            };
    }

    import TestHeader         from "./partials/test-header/TestHeader";
    import TestError          from "./partials/TestError";
    import TestController     from "./TestController";
    import HmBootloader       from "../helpers/hm-loading/Bootloading"

    import TestModel from "./models/TestModel";
    import Bootloading from "@/components/helpers/hm-loading/Bootloading";
    export default {
        name: "HmQuiz",
        components: {
            Bootloading,
            TestHeader,
            TestController,
            TestError,
            HmBootloader
        },
        props: {
            load: {
                type: String,
                default: () => false
            },
            isLegacy: { type: Boolean },
            saveUrl: {
              type: String,
              default: () => false
            }
        },
        data() {
            // TODO: перенести всё в Vuex store после интеграции в Danone
            return {
                text: null,
                testModel: null,
                isLoading: true,
                downloadPercent: 0,
                isErorred: false,
                error: null,
                hasErrorOnLoad: false,
                finalizeInTestHeader: null
            };
        },
        watch: {
            finalizeInTestHeader() {
                this.$emit("finalizeInHmTestInHeader");
            }
        },
        computed: {
            isMobile() {
                return document.body.classList.contains(`is-mobile`);
            },
            isIe() {
                return this.$uaparser.getBrowser().name == "IE";
            },
            testHeaderProps() {
                return {
                    // hasContext: this.testModel.hasContext,
                    title: this.testModel ? this.testModel.title : "Опрос",
                    // context: this.testModel.context,
                    // time: parseInt(this.testModel.time, 10),
                    // limitTime: this.testModel.limitTime,
                    // testType: this.testModel.type,
                };
            },
            testControllerProps() {
                return {
                    saveUrl: this.saveUrl,
                    resultsUrl: this.testModel.resultsUrl,
                    finalizeUrl: this.testModel.finalizeUrl,
                    restrictUserTraversal: this.testModel.isMovementRestricted,
                    progress: this.resetProgress(),
                    time: this.testModel.time,
                    currentItem: 0,
                    results: [],
                    questions: this.testModel.questions,
                    commentInProcessOfFilling: this.testModel.commentInProcessOfFilling,
                    limitTime: this.testModel.limitTime,
                    showCommentForQuestions: this.testModel.showCommentForQuestion,
                    type: this.testModel.type,
                    modeSelfTest: this.testModel.modeSelfTest
                };
            }
        },
        mounted() {
            this.$root.isLegacy = this.isLegacy ? this.isLegacy : true;

            let ZFDebug = document.getElementById(`ZFDebug_debug`);
            if (ZFDebug) ZFDebug.display = `none`;
            this.$log.debug("HM:test | mounted. Start getting data");
            this.$log.debug(
                `HM:test | mounted. Browser is "${this.$uaparser.getBrowser().name}"`
            );
            if (this.load) {
                this.getData();
            } else {
                throw new ReferenceError(
                    `Нужно указать ссылку для загрузки данных в аттрибутах!`
                );
            }
        },
        created() {
            this.$log.debug("HM:test | created. Disabling main layout");
            if (!isInLegacyMode) {
                this.$log.debug("HM:test | disabling main layout");
                this.disableMainLayout();
            }
        },
        methods: {
            resetProgress() {
              const newProgress = [];
              this.testModel.progress.forEach((item) => {
                item.itemProgress = 0;
                newProgress.push(item);
              });
              return newProgress;
            },
            finalizeInTestHeaderStatus(status) {
              this.finalizeInTestHeader = status;
            },
            enableLoadingStatus() {
                this.isLoading = true;
            },
            disableLoadingStatus() {
                this.isLoading = false;
            },
            handleLoadError(err) {
                this.disableLoadingStatus();
                this.hasErrorOnLoad = true;
                this.handleError(err);
            },
            handleError(err) {
                this.isErorred = true;
                this.error = err;
                this.$log.error(err);
            },
            handleErrorClose() {
                this.isErorred = false;
                this.error = null;
            },
            getData() {
                this.enableLoadingStatus();
                axios
                    .get(this.load, {
                        onDownloadProgress: ProgressEvent => {
                            this.$nextTick(() => {
                                this.downloadPercent = parseInt(
                                    Math.floor((ProgressEvent.loaded / ProgressEvent.total) * 100),
                                    10
                                );
                            });
                        }
                    })
                    .then(response => response.data)
                    .then(this.parseLoadData)
                    .catch(this.handleLoadError);
            },
            parseLoadData(data) {
                if(typeof data === "string") {
                  console.log(data)
                  this.text = data;
                  console.log(this.text)
                  return;
                }
                this.$log.debug(`HM:test | loaded data`, data);
                if (!(data instanceof Object)) return;
                // TODO: Clear out this mess
                if (data.context) {
                    this.testModel = new TestModel(data, true);
                    this.testModel.context = data.context;
                } else {
                    this.testModel = new TestModel(data);
                }

                window.setTimeout(() => {
                    // temporary for debug
                    this.$nextTick(() => {
                        this.isLoading = false;
                    });
                }, 600);
            },
            ...mapActions(["disableMainLayout"])
        }
    };
</script>
<style lang="scss">
  .isIe {
    & * {
      transition: none !important;
      box-shadow: none !important;
    }
    & .v-expansion-panel__container {
      border-width: 1px;
      border-style: solid;
    }
    & .hm-test-sorting_item,
    .hm-test-classification_item,
    .hm-test-classification_group {
      border-width: 1px;
      border-style: solid;
      border-color: rgba(black, 0.2);
    }
    & .v-card {
      border-width: 1px;
      border-style: solid;
      border-color: rgba(black, 0.2);
    }
  }
</style>
