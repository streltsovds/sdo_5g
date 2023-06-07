<script>
import Vue from "vue";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";

export default {
  components: {
    hmProctoringTeacher: () => import("@/components/helpers/hm-proctoring-teacher/index"),
    hmProctoringTeacherActivator: () => import("@/components/helpers/hm-proctoring-teacher/activator"),
    hmTreeSelect: () => import("@/components/forms/hm-tree-select/index"),
    hmDatePicker: () => import("@/components/forms/hm-date-picker/index"),
    hmText: () => import("@/components/forms/hm-text/index"),
    hmCheckbox: () => import("@/components/forms/hm-checkbox/index"),
    hmPasswordCheckbox: () => import("@/components/forms/hm-password-checkbox/index"),
    hmRadio: () => import("@/components/forms/hm-radio/index"),
    hmCounter: () => import("@/components/forms/hm-counter/index"),
    hmTimeSlider: () => import("@/components/forms/hm-time-slider/index"),
    hmTimePicker: () => import("@/components/forms/hm-time-picker/index"),
    hmSelect: () => import("@/components/forms/hm-select/index"),
    hmCropper: () => import("@/components/media/hm-cropper"),
    hmElfinder: () => import("@/components/forms/hm-elfinder/index"),
    hmFile: () => import("@/components/forms/hm-file/index"),
    hmMultiCheckbox: () => import("@/components/forms/hm-multi-checkbox/index"),
    hmMultiSelect: () => import("@/components/forms/hm-multi-select/index"),
    hmMultiSet: () => import("@/components/forms/hm-multi-set/index"),
    hmMultiText: () => import("@/components/forms/hm-multi-text/index"),
    hmSlider: () => import("@/components/forms/hm-slider/index"),
    hmSubmit: () => import("@/components/forms/hm-submit/index"),
    hmSubmitLink: () => import("@/components/forms/hm-submit-link/index"),
    hmTags: () => import("@/components/forms/hm-tags/index"),
    hmTextarea: () => import("@/components/forms/hm-textarea/index"),
    hmTinyMce: () => import("@/components/forms/hm-tiny-mce/index"),
    hmRadioGroup: () => import("@/components/forms/hm-radio-group/index"),
    hmMaterialSearch: () => import("@/components/els/subject/lesson/materialSearch"),
    hmChooseMaterial: () => import("@/components/els/subject/chooseMaterial"),
    hmWorkflowFull: () => import("@/components/els/hm-workflow/full/index"),
    hmPrintBtn: () => import("@/components/controls/hm-print-btn/index"),
    hmResumeBtn: () => import("@/components/els/resume/hm-resume-btn/index"),
    hmCardLink: () => import("@/components/els/hm-card-link/index"),
    hmWorkflowShort: () => import("@/components/els/hm-workflow/short/index"),
    hmMaterialPreview: () => import("@/components/media/preview/card"),
    hmMaterialViewPlayer: () => import("@/components/media/preview/player"),
    hmSwiper: () => import("@/components/controls/hm-swiper/index"),
    hmLogin: () => import("@/components/els/auth/hm-login/index"),
    hmLoginButton: () => import("@/components/els/auth/hm-login-button"),
    hmForm: () => import("@/components/forms/hm-form-alternative/index"),
    hmActionsEdit: () => import("@/components/icons/actions/edit"),
    hmActionsDetails: () => import("@/components/icons/actions/details"),
    hmActionsResults: () => import("@/components/icons/actions/results"),
    hmActionsDownload: () => import("@/components/icons/actions/download"),
    hmSubjectWrapper: () => import("@/components/els/subject/cardCourse/subjectWrapper"),
    subjectCardCourse: () => import("@/components/els/subject/cardCourse/subjectCardCourse"),
    hmKbaseIcon: () => import("@/components/els/kbase/icon"),
    hmCalendar: () => import("@/components/controls/hm-calendar"),
    // infoblocks
    hmActivityBlock: () => import("@/components/layout/infoblocks/hm-activity-block/index"),
    hmActivityDevBlock: () =>
      import("@/components/layout/infoblocks/hm-activity-dev-block/index"),
    hmClaims: () => import("@/components/layout/infoblocks/hm-claims/index"),
    hmFeedback: () => import("@/components/layout/infoblocks/hm-feedback/index"),
    hmKbaseBlock: () => import("@/components/layout/infoblocks/hm-kbase-block/index"),
    hmLeasing: () => import("@/components/layout/infoblocks/hm-leasing/index"),
    hmNewsBannerBlock: () => import("@/components/layout/infoblocks/hm-news-banner-block/index"),
    hmInfosliderBlock: () => import("@/components/layout/infoblocks/hm-infoslider-block/index"),
    hmMyEventsBlock: () => import("@/components/layout/infoblocks/hm-my-events-block/index"),
    hmRandomSubjects: () => import("@/components/layout/infoblocks/hm-random-subjects/index"),
    hmRatingBlock: () => import("@/components/layout/infoblocks/hm-rating-block/index"),
    hmMyMates: () => import("@/components/layout/infoblocks/hm-my-mates/index"),
    hmRecommendedMaterial: () => import("@/components/layout/infoblocks/hm-recommended-material/index"),
    hmResourcesBlock: () => import("@/components/layout/infoblocks/hm-resources-block/index"),
    hmScheduleDaily: () => import("@/components/layout/infoblocks/hm-schedule-daily/index"),
    hmScreencastBlock: () => import("@/components/layout/infoblocks/hm-screencast-block/index"),
    hmTimesheet: () => import("@/components/layout/infoblocks/hm-timesheet/index"),
    hmTopSubjects: () => import("@/components/layout/infoblocks/hm-top-subjects/index"),
    hmUsersSystemCounter: () =>
      import("@/components/layout/infoblocks/hm-users-system-counter/index"),
    hmVideoBlock: () => import("@/components/layout/infoblocks/hm-video-block/index"),
    hmFaqBlock: () => import("@/components/layout/infoblocks/hm-faq-block/index"),
    hmYieldBlock: () => import("@/components/layout/infoblocks/hm-yield-block/index"),
    hmEmpty: () => import("@/components/helpers/hm-empty"),
    hmChartComparisonBlock: () =>
      import("@/components/layout/infoblocks/hm-chart-comparison-block"),
    hmChartSubjectProgressBlock: () =>
      import("@/components/layout/infoblocks/hm-chart-subject-progress-block"),

    hmLongTextTooltip: () => import("../hm-long-text-tooltip"),
    // widgets
    hmWidgetCalendar: () => import("@/components/controls/hm-widget-calendar"),
    hmQuiz: () => import("@/components/hm-quiz/HmQuiz.vue"),

    // TODO норм ли включать сюда компоненты материалов?
    hmMaterialResponsive: () => import("@/components/layout/hm-material-responsive/index"),
    hmMaterialVideo: () => import("@/components/media/view/video"),
    hmMaterialFlash: () => import("@/components/media/view/flash"),
    hmMaterialHtml: () => import("@/components/media/view/html"),
    hmMaterialIframe: () => import("@/components/media/view/iframe"),
    hmImage: () => import("@/components/media/hm-image"),
    pdfViewer: () => import("@/components/media/vue-pdfjs"),
    hmSlides: () => import("@/components/media/hm-slides"),

    //icons
    iconContainerOld: () => import("@/components/icons/iconContainerOld"),
    iconCourseOld: () => import("@/components/icons/oldItems/iconCourseOld"),
    iconHtmlOld: () => import("@/components/icons/oldItems/iconHtmlOld"),
    iconPdfOld: () => import("@/components/icons/oldItems/iconPdfOld"),
    iconTextOld: () => import("@/components/icons/oldItems/iconTextOld"),
    iconUrlOld: () => import("@/components/icons/oldItems/iconUrlOld"),
    iconFlashOld: () => import("@/components/icons/oldItems/iconFlashOld"),
    iconDiode: () => import("@/components/icons/items/iconDiode"),
    svgIcon: () => import("@/components/icons/svgIcon"),
    svgTypeLesson: () => import("@/components/icons/lessonType/svgTypeLesson"),

    hmModalActivator: () => import("@/components/layout/hm-modal/activator"),
  },
  mixins: [VueMixinConfigColors],
  props: {
    template: {
      type: String,
      required: true
    },
    wrap: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      templateRender: null
    };
  },
  computed: {
    formattedTemplate() {
      const template = this.template.replace(/Ⓠ/gi, "'");
      const isVideoblock = template.includes('hm-video-block');
      return this.wrap ? `<span class="${isVideoblock? 'hm-dependency__wrap hm-dependency__wrap--videoblock': 'hm-dependency__wrap'} ">${template}</span>` : template;
    }
  },
  watch: {
    template: {
      immediate: true,
      handler() {
        let res = Vue.compile(this.formattedTemplate);

        this.templateRender = res.render;

        // staticRenderFns belong into $options,
        // appearantly
        this.$options.staticRenderFns = [];

        // clean the cache of static elements
        // this is a cache with the results from the staticRenderFns
        this._staticTrees = [];

        // Fill it with the new staticRenderFns
        for (let i in res.staticRenderFns) {
          //staticRenderFns.push(res.staticRenderFns[i]);
          this.$options.staticRenderFns.push(res.staticRenderFns[i]);
        }
      }
    }
  },
  render(h) {
    if (!this.templateRender) {
      return h("div", "loading...");
    }
    return this.templateRender();
  }
};
</script>
