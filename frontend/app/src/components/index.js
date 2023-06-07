// import hmPerformance from "./hm-performance";

export default {
  /* Для внедрения и рендеринга кусочков vue-шаблоков после их догрузки */
  "hm-proctoring": () =>
    import(/* webpackChunkName: "hmProctoring" */ "./helpers/hm-proctoring"),
  "hm-proctoring-teacher": () =>
    import(/* webpackChunkName: "hmProctoringTeacher" */ "./helpers/hm-proctoring-teacher/index.vue"),
  "hm-proctoring-teacher-activator": () =>
    import(/* webpackChunkName: "hmProctoringTeacherActivator" */ "./helpers/hm-proctoring-teacher/activator.vue"),
  "hm-dependency": () =>
    import(/* webpackChunkName: "hmDependency" */ "./helpers/hm-dependency"),
  "hm-load-more-btn": () =>
    import(/* webpackChunkName: "HmLoadMoreBtn" */ "./helpers/hm-load-more-btn"),
  "hm-question-step-two-wysiwyg": () =>
    import(/* webpackChunkName: "HmQuestionStepTwoWysiwyg" */ "./forms/hm-question-step-two-wysiwyg"),
  "hm-multi-select-areas-image": () =>
    import(/* webpackChunkName: "HmMultiSelectAreasImage" */ "./forms/hm-multi-select-areas-image"),
  "hm-support-modal": () =>
    import(/* webpackChunkName: "hmSupportModel" */ "./els/hm-support/modal"),
  "hm-hotkey": () =>
    import(/* webpackChunkName: "hmHotkey" */ "./helpers/hm-hotkey"),
  "hm-image": () =>
    import(/* webpackChunkName: "hmImage" */ "./media/hm-image"),
  "hm-lesson-sidebar": () =>
    import(/* webpackChunkName: "hmLessonSidebar" */ "./layout/hm-lesson-sidebar"),
  "hm-widgets-composer": () =>
    import(/* webpackChunkName: "hmWidgetsComposer" */ "./layout/hm-widgets-composer"),
  "hm-bookmarks-block": () =>
    import(/* webpackChunkName: "hmBookmarksBlock" */ "./layout/infoblocks/hm-bookmarks-block"),
  "hm-statement-performance-table": () =>
    import(/* webpackChunkName: "hmStatementPerformanceTable" */ "./els/marksheet/hm-statement-performance-table"),
  "hm-report-chart": () =>
    import(/* webpackChunkName: "HmReportChart" */ "./els/marksheet/hm-report-chart"),
  "hm-report-competence": () =>
    import(/* webpackChunkName: "HmReportCompetence" */ "./els/marksheet/hm-report-competence"),
  "hm-my-mates": () =>
    import(/* webpackChunkName: "hmMyMates" */ "./layout/infoblocks/hm-my-mates"),
  "hm-user-events": () =>
    import(/* webpackChunkName: "hmUserEvents" */ "./els/hm-user-events"),
  "hm-my-programs": () =>
    import(/* webpackChunkName: "hmMyPrograms" */ "./els/hm-my-programs"),
  "hm-swiper": () => import(/* webpackChunkName: "hmSwiper" */ "./controls/hm-swiper"),
  "hm-form-alternative": () => import(/* webpackChunkName: "hmForm" */ "./forms/hm-form-alternative"),
  "hm-grid": () =>
    import(/* webpackChunkName: "hmGrid" */ "./hm-grid/HmGrid.vue"),
  "hm-chart": () => import(/* webpackChunkName: "hmChart" */ "./media/hm-chart"),
  "hm-timesheet": () =>
    import(/* webpackChunkName: "hmTimesheet" */ "./layout/infoblocks/hm-timesheet"),
  "hm-random-subjects": () =>
    import(/* webpackChunkName: "hmRandomSubjects" */ "./layout/infoblocks/hm-random-subjects"),
  "hm-video-block": () =>
    import(/* webpackChunkName: "hmVideoBlock" */ "./layout/infoblocks/hm-video-block"),
  "hm-faq-block": () =>
    import(/* webpackChunkName: "hmFaqBlock" */ "./layout/infoblocks/hm-faq-block"),
  "hm-screencast-block": () =>
    import(/* webpackChunkName: "hmScreencastBlock" */ "./layout/infoblocks/hm-screencast-block"),
  "hm-leasing": () =>
    import(/* webpackChunkName: "hmLeasing" */ "./layout/infoblocks/hm-leasing"),
  "hm-users-system-counter": () =>
    import(/* webpackChunkName: "hmUsersSystemCounter" */ "./layout/infoblocks/hm-users-system-counter"),
  "hm-my-subjects": () =>
    import(/* webpackChunkName: "hmMySubjects" */ "./els/hm-my-subjects"),
  "hm-my-assessment": () =>
    import(/* webpackChunkName: "hmMyAssessment" */ "./els/hm-my-assessment"),
  "hm-hint": () =>
    import(/* webpackChunkName: "hmHint" */ "./helpers/hm-hint"),
  "hm-task-preview": () =>
    import(/* webpackChunkName: "hmTaskPreview" */ "./els/hm-task-preview"),
  "hm-kbase-icon": () =>
    import(/* webpackChunkName: "hmKbaseCardIcon" */ "./els/kbase/icon"),
  "hm-chart-comparison-block": () =>
    import(/* webpackChunkName: "hmKbaseCardIcon" */ "./layout/infoblocks/hm-chart-comparison-block"),


    // Компоненты sidebars
  "hm-sidebar-profile": () =>
    import(/* webpackChunkName: "hmSidebarProfile" */ "./layout/hm-sidebars/hm-sidebar-profile"),
  "hm-sidebar-extras-manager": () =>
    import(/* webpackChunkName: "hmSidebarExtrasManager" */ "./layout/hm-sidebars/hm-sidebar-extras-manager"),
  "hm-sidebar-chat": () =>
    import(/* webpackChunkName: "hmSidebarExtrasManager" */ "./layout/hm-sidebars/hm-sidebar-chat"),
  "hm-sidebar-extras-user": () =>
    import(/* webpackChunkName: "hmSidebarExtrasUser" */ "./layout/hm-sidebars/hm-sidebar-extras-user"),
  "hm-sidebar-enduser": () =>
    import(/* webpackChunkName: "hmSidebarEnduser" */ "./layout/hm-sidebars/hm-sidebar-enduser"),
  "hm-sidebar-lesson": () =>
    import(/* webpackChunkName: "hmSidebarLesson" */ "./layout/hm-sidebars/hm-sidebar-lesson"),
  "hm-sidebar-updates-news": () =>
    import(/* webpackChunkName: "hmSidebarUpdatesNews" */ "./layout/hm-sidebars/hm-sidebar-updates-news"),
  "hm-sidebar-general-info-manager": () =>
    import(/* webpackChunkName: "hmSidebarGeneralInfoManager" */ "./layout/hm-sidebars/hm-sidebar-general-info-manager"),
  "hm-sidebar-catalog": () =>
    import(/* webpackChunkName: "hmSidebarCatalog" */ "./layout/hm-sidebars/hm-sidebar-catalog"),
  "hm-sidebar-user-card": () =>
    import(/* webpackChunkName: "hmSidebarUserCard" */ "./layout/hm-sidebars/hm-sidebar-user-card"),
  "hm-sidebar-task": () =>
    import(/* webpackChunkName: "hmSidebarTask" */ "./layout/hm-sidebars/hm-sidebar-task"),
  "hm-sidebar-resource-manager": () =>
      import(/* webpackChunkName: "hmSidebarResourceManager" */ "./layout/hm-sidebars/hm-sidebar-resource-manager"),
  "hm-sidebar-resource-enduser": () =>
      import(/* webpackChunkName: "hmSidebarResourceEnduser" */ "./layout/hm-sidebars/hm-sidebar-resource-enduser"),
  "hm-sidebar-course": () =>
      import(/* webpackChunkName: "hmSidebarCourse" */ "./layout/hm-sidebars/hm-sidebar-course"),
  "hm-sidebar-test": () =>
      import(/* webpackChunkName: "hmSidebarTest" */ "./layout/hm-sidebars/hm-sidebar-test"),
  "hm-sidebar-poll": () =>
      import(/* webpackChunkName: "hmSidebarPoll" */ "./layout/hm-sidebars/hm-sidebar-poll"),
  "hm-sidebar-user": () =>
    import(/* webpackChunkName: "hmSidebarUser" */ "./layout/hm-sidebars/hm-sidebar-user"),
  "hm-sidebar-userhome": () =>
    import(/* webpackChunkName: "hmSidebarUserHome" */ "./layout/hm-sidebars/hm-sidebar-userhome"),
  "hm-sidebar-session": () =>
    import(/* webpackChunkName: "hmSidebarSession" */ "./layout/hm-sidebars/hm-sidebar-session"),

  //комопненты формы
  "hm-file-downloader": () =>
    import(/* webpackChunkName: "hmFileDownloader" */ "./media/hm-file-downloader"),

  "hm-matrix-progress": () =>
    import(/* webpackChunkName: "hmUserLessonsPlan" */ "./els/hm-matrix-progress"),
  "hm-user-lessons-plan-row": () =>
    import(/* webpackChunkName: "hmUserLessonsPlan" */ "./els/hm-user-lessons-plan/row"),
  "hm-user-lessons-plan-wrapper": () =>
    import(/* webpackChunkName: "hmUserLessonsPlan" */ "./els/hm-user-lessons-plan/wrapper"),
  "hm-material-responsive": () =>
    import(/* webpackChunkName: "hmSidebars" */ "./layout/hm-material-responsive"),
  "hm-partials-actions": () =>
    import(/* webpackChunkName: "layout" */ "./layout/hm-partials-actions"),
  "hm-actions-edit": () =>
    import(/* webpackChunkName: "hmActionsEdit" */ "./icons/actions/edit"),
  "hm-actions-details": () =>
    import(/* webpackChunkName: "hmActionsDetails" */ "./icons/actions/details"),
  "hm-actions-results": () =>
    import(/* webpackChunkName: "hmActionsResults" */ "./icons/actions/results"),
  "hm-actions-download": () =>
    import(/* webpackChunkName: "hmActionsDownload" */ "./icons/actions/download"),
  "hm-subject-wrapper": () =>
    import(/* webpackChunkName: "hmSubjectWrapper" */ "./els/subject/cardCourse/subjectWrapper"),
  "hm-sidebar": () =>
    import(/* webpackChunkName: "hmSidebars" */ "./layout/hm-sidebar"),
  "hm-sidebar-toggle": () =>
    import(/* webpackChunkName: "hmSidebars" */ "./layout/hm-sidebar-toggle"),
  "hm-notification-counter": () =>
    import(/* webpackChunkName: "hmNotificationCounter" */ "./icons/addToTheIcon/notificationCounter"),
  "hm-role-switcher": () =>
    import(/* webpackChunkName: "hmSidebars" */ "./layout/hm-role-switcher"),
  "hm-contacts-sidebar": ()=>
    import(/* webpackChunkName: "hmContactsSidebar" */ "./els/hm-contacts/hm-cotntacts-sidebar/hm-contacts-sidebar.vue"),
  "hm-kbase-search": () =>
    import(/* webpackChunkName: "hmSidebarsSearch" */ "./els/kbase/sidebars/hm-kbase-search"),
  "hm-subjects-search": () =>
    import(/* webpackChunkName: "hmSidebarsSubjectSearch" */ "./els/subject/sidebars/hm-subjects-search"),
  "hm-schedule-daily": () =>
    import(/* webpackChunkName: "hmScheduleDaily" */ "./layout/infoblocks/hm-schedule-daily"),
  "hm-resources-block": () =>
    import(/* webpackChunkName: "hmResourcesBlock" */ "./layout/infoblocks/hm-resources-block"),
  "hm-kbase-block": () =>
    import(/* webpackChunkName: "hmKbaseBlock" */ "./layout/infoblocks/hm-kbase-block"),
  "hm-yield-block": () =>
    import(/* webpackChunkName: "hmYieldBlock" */ "./layout/infoblocks/hm-yield-block"),
  "hm-activity-block": () =>
    import(/* webpackChunkName: "hmActivityBlock" */ "./layout/infoblocks/hm-activity-block"),
  "hm-news-banner-block": () =>
    import(/* webpackChunkName: "hmSubjectBannerBlock" */ "./layout/infoblocks/hm-news-banner-block"),
  "hm-infoslider-block": () =>
    import(/* webpackChunkName: "hmInfosliderBlock" */ "./layout/infoblocks/hm-infoslider-block"),
  "hm-activity-dev-block": () =>
    import(/* webpackChunkName: "hmActivityDevBlock" */ "./layout/infoblocks/hm-activity-dev-block"),
  "hm-my-events-block": () =>
    import(/* webpackChunkName: "hmMyEventsBlock" */ "./layout/infoblocks/hm-my-events-block"),
  "hm-top-subjects": () =>
    import(/* webpackChunkName: "hmTopSubjects" */ "./layout/infoblocks/hm-top-subjects"),
  "hm-claims": () =>
    import(/* webpackChunkName: "hmClaims" */ "./layout/infoblocks/hm-claims"),
  "hm-rating-block": () =>
    import(/* webpackChunkName: "hmRatingBlock" */ "./layout/infoblocks/hm-rating-block"),  'hm-main-nav-menu-action-with-tooltip': () =>
    import(
      /* webpackChunkName: "hmMainNavMenu" */ './layout/nav-menu/components/action-with-tooltip/index.vue'
    ),
  // "hm-pdf-viewer": () =>
  //   import(/* webpackChunkName: "hmPdfViewer" */ "./hm-pdf-viewer"),
  "hm-test": () =>
    import(/* webpackChunkName: "hmTest" */ "./hm-test/HmTest.vue"),
  "hm-quiz": () =>
    import(/* webpackChunkName: "hmQuiz" */ "./hm-quiz/HmQuiz.vue"),
  // "hm-performance": hmPerformance,
  "hm-feedback-course": () =>
    import(/* webpackChunkName: "HmFeedbackCourse" */ "./els/hm-feedback-course"),
  "hm-feedback": () =>
    import(/* webpackChunkName: "hmFeedback" */ "./layout/infoblocks/hm-feedback"),
  "hm-eclass-video": () =>
    import(/* webpackChunkName: "HmEclassVideo" */ "./els/hm-eclass-video"),
  "hm-multi-select": () =>
    import(/* webpackChunkName: "hmMultiSelect" */ "./forms/hm-multi-select"),
  "hm-single-choice": () =>
    import(/* webpackChunkName: "hmSingleChoice" */ "./forms/hm-single-choice"),
  "hm-tree-select": () =>
    import(/* webpackChunkName: "hmTreeSelect" */ "./forms/hm-tree-select"),
  "hm-radio-group": () =>
    import(/* webpackChunkName: "hmRadioGroup" */ "./forms/hm-radio-group"),
  "hm-switch-checkmark": () =>
    import(/* webpackChunkName: "hmSwitchCheckmark" */ "./controls/hm-switch-checkmark"),
  "hm-select": () =>
    import(/* webpackChunkName: "hmSelect" */ "./forms/hm-select"),
  "hm-file": () =>
    import(/* webpackChunkName: "hmFile" */ "./forms/hm-file"),
  "hm-date-picker": () =>
    import(/* webpackChunkName: "hmDatePicker" */ "./forms/hm-date-picker"),
  "hm-date-range-field": () =>
    import(/* webpackChunkName: "hmDateRangeField" */ "./forms/hm-date-range-field"),
  "hm-checkbox": () =>
    import(/* webpackChunkName: "hmCheckbox" */ "./forms/hm-checkbox"),
  "hm-checkboxs": () =>
    import(/* webpackChunkName: "hmCheckboxs" */ "./forms/hm-checkboxs"),
  "hm-text": () =>
    import(/* webpackChunkName: "hmText" */ "./forms/hm-text"),
  "hm-textarea": () =>
    import(/* webpackChunkName: "hmTextArea" */ "./forms/hm-textarea"),
  "hm-tiny-mce": () =>
    import(/* webpackChunkName: "hmTinyMce" */ "./forms/hm-tiny-mce"),
  "hm-elfinder": () =>
    import(/* webpackChunkName: "hmElfinder" */ "./forms/hm-elfinder"),
  "hm-submit": () =>
    import(/* webpackChunkName: "hmSubmit" */ "./forms/hm-submit"),
  "hm-submit-link": () =>
    import(/* webpackChunkName: "hmSubmitLink" */ "./forms/hm-submit-link"),
  "hm-multi-checkbox": () =>
    import(/* webpackChunkName: "hmMultiCheckbox" */ "./forms/hm-multi-checkbox"),
  "hm-multi-set": () =>
    import(/* webpackChunkName: "hmMultiSet" */ "./forms/hm-multi-set"),
  "hm-multi-text": () =>
    import(/* webpackChunkName: "hmMultiText" */ "./forms/hm-multi-text"),
  "hm-radio": () =>
    import(/* webpackChunkName: "hmRadio" */ "./forms/hm-radio"),
  "hm-tags": () =>
    import(/* webpackChunkName: "hmTags" */ "./forms/hm-tags"),
  "hm-counter": () =>
    import(/* webpackChunkName: "hmCounter" */ "./forms/hm-counter"),
  "hm-time-slider": () =>
    import(/* webpackChunkName: "hmTimeSlider" */ "./forms/hm-time-slider"),
  "hm-time-picker": () =>
    import(/* webpackChunkName: "hmTimePicker" */ "./forms/hm-time-picker"),
  "hm-slider": () =>
    import(/* webpackChunkName: "hmSlider" */ "./forms/hm-slider"),
  "hm-tabs": () =>
    import(/* webpackChunkName: "hmTabs" */ "./forms/hm-tabs"),
  "hm-stepper": () =>
    import(/* webpackChunkName: "hmStepper" */ "./forms/hm-stepper"),
  "hm-my-reports": () =>
    import(/* webpackChunkName: "hmWorkflowShort" */ "./els/hm-my-reports"),
  "hm-workflow-short": () =>
    import(/* webpackChunkName: "hmWorkflowShort" */ "./els/hm-workflow/short"),
  "hm-workflow-full": () =>
    import(/* webpackChunkName: "hmWorkflowFull" */ "./els/hm-workflow/full"),
  "hm-data-agreement": () =>
    import(/* webpackChunkName: "hmDataAgreement" */ "./els/hm-data-agreement"),
  "hm-password-checkbox": () =>
    import(/* webpackChunkName: "hmPasswordCheckbox" */ "./forms/hm-password-checkbox"),
  "hm-material-preview": () =>
    import(/* webpackChunkName: "hmMaterialPreview" */ "./media/preview/card"),
  "hm-material-html": () =>
    import(/* webpackChunkName: "hmMaterialViewHtml" */ "./media/view/html"),
  "hm-material-iframe": () =>
    import(/* webpackChunkName: "hmMaterialViewIframe" */ "./media/view/iframe"),
  "hm-material-flash": () =>
    import(/* webpackChunkName: "hmMaterialViewFlash" */ "./media/view/flash"),
  "hm-material-video": () =>
    import(/* webpackChunkName: "hmMaterialViewVideo" */ "./media/view/video"),
  "hm-material-player": () =>
    import(/* webpackChunkName: "hmMaterialPreviewPlayer" */ "./media/preview/player"),
  "hm-material-search": () =>
    import(/* webpackChunkName: "hmMaterialSearch" */ "./els/subject/lesson/materialSearch.vue"),
  "hm-lessons": () =>
    import(/* webpackChunkName: "hmLessons" */ "./els/subject/lessons/edit.vue"),
  "hm-kbase": () => import(/* webpackChunkName: "hmKbase" */ "./els/kbase"),
  "hm-subject": () => import(/* webpackChunkName: "hmSubject" */ "./els/subject"),
  "hm-modal": () =>
    import(/* webpackChunkName: "hmModal" */ "./layout/hm-modal/index"),
  "hm-modal-activator": () =>
    import(/* webpackChunkName: "hmModal" */ "./layout/hm-modal/activator"),
  "hm-modal-confirm": () =>
    import(/* webpackChunkName: "hmModal" */ "./layout/hm-modal/confirm"),
  "hm-modal-html": () =>
    import(/* webpackChunkName: "hmModal" */ "./layout/hm-modal/html"),
  "hm-alerts": () => import(/* webpackChunkName: "hmAlerts" */ "./layout/hm-alerts"),
  "hm-panels": () => import(/* webpackChunkName: "hmPanels" */ "./controls/hm-panels"),
  "hm-group-btn": () =>
    import(/* webpackChunkName: "hmGroupBtn" */ "./controls/hm-group-btn"),
  "hm-print-btn": () =>
    import(/* webpackChunkName: "hmPrintBtn" */ "./controls/hm-print-btn"),
  "hm-autocomplete": () =>
    import(/* webpackChunkName: "hmDownloadBtn" */ "./controls/hm-autocomplete"),
  "hm-download-btn": () =>
    import(/* webpackChunkName: "hmDownloadBtn" */ "./controls/hm-download-btn"),
  "hm-resume-btn": () =>
    import(/* webpackChunkName: "hmResumeBtn" */ "./els/resume/hm-resume-btn"),
  "hm-notifications": () =>
    import(/* webpackChunkName: "hmNotifications" */ "./els/hm-notifications"),
  "hm-rubricator": () =>
    import(/* webpackChunkName: "hmRubricator" */ "./controls/hm-rubricator"),
  "hm-rubricator-grid-button": () =>
    import(/* webpackChunkName: "hmRubricator" */ "./controls/hm-rubricator/grid-button"),
  "hm-card-link": () =>
    import(/* webpackChunkName: "hmCardLink" */ "./els/hm-card-link"),
  "hm-news-page": () =>
    import(/* webpackChunkName: "hmNewsPage" */ "./els/hm-news-page"),
  "hm-news": () =>
    import(/* webpackChunkName: "hmNews" */ "./els/hm-news-page/components/hm-news"),
  "hm-news-item": () =>
    import(/* webpackChunkName: "hmNewsItem" */ "./els/hm-news-page/components/hm-news-item"),
  "hm-programm-builder": () =>
    import(/* webpackChunkName: "hmProgrammBuilder" */ "./els/hm-programm-builder"),
  "hm-subject-edit": () =>
    import(/* webpackChunkName: "hmSubjectEdit" */ "./els/subject/edit.vue"),
  "hm-interface-edit": () =>
    import(/* webpackChunkName: "hmInterfaceEdit" */ "./layout/hm-interface-edit"),
  "hm-global-loader": () =>
    import(/* webpackChunkName: "hmGlobalLoader" */ "./layout/hm-global-loader"),
  "hm-login": () => import(/* webpackChunkName: "hmLogin" */ "./els/auth/hm-login"),
  "hm-login-form": () => import(/* webpackChunkName: "hmLogin" */ "./els/auth/hm-login-form"),
  "hm-login-button": () =>
    import(/* webpackChunkName: "hmLogin" */ "./els/auth/hm-login-button"),
  "hm-login-logo": () =>
    import(/* webpackChunkName: "hmMultiSelect" */ "./els/auth/hm-login-logo"),
  "hm-contacts": () =>
    import(/* webpackChunkName: "hmContacts" */ "./els/hm-contacts"),
  "hm-logout": () =>
    import(/* webpackChunkName: "hmLogin" */ "./els/auth/hm-logout"),
  "v-style": () =>
    import(/* webpackChunkName: "helpers" */ "./helpers/v-style"),
  "hm-app-styles": () =>
    import(/* webpackChunkName: "helpers" */ "./helpers/hm-app-styles"),
  "hm-long-text-tooltip": () =>
    import(/* webpackChunkName: "helpers" */ "./helpers/hm-long-text-tooltip"),
  "subject-card-course": () =>
    import(/* webpackChunkName: "subjectCardCourse" */ "./els/subject/cardCourse/subjectCardCourse"),
  "hm-job-interface": () =>
    import(/* webpackChunkName: "hmJobInterface" */ "./els/hm-job-interface"),
  "hm-faq": () =>
    import(/* webpackChunkName: "hmFaq" */ "./els/hm-faq"),
  "hm-congratulations": () =>
    import(/* webpackChunkName: "hmCongratulations" */ "./els/hm-congratulations"),
  //diagramm
  "hm-diagramm": () =>
    import("./media/hm-diagramm"),
  "hm-results": () =>
    import("./els/hm-results"),
  "hm-at-session-events": () =>
    import("./els/hm-at-session-events"),
  //icons
  "icon-container-old": () =>
    import(/* webpackChunkName: "materialIcons" */ "./icons/iconContainerOld"),
  "icon-course-old": () =>
    import(/* webpackChunkName: "materialIcons" */ "./icons/oldItems/iconCourseOld"),
  "icon-html-old": () =>
    import(/* webpackChunkName: "materialIcons" */ "./icons/oldItems/iconHtmlOld"),
  "icon-pdf-old": () =>
    import(/* webpackChunkName: "materialIcons" */ "./icons/oldItems/iconPdfOld"),
  "icon-text-old": () =>
    import(/* webpackChunkName: "materialIcons" */ "./icons/oldItems/iconTextOld"),
  "icon-url-old": () =>
    import(/* webpackChunkName: "materialIcons" */ "./icons/oldItems/iconUrlOld"),
  "icon-flash-old": () =>
    import(/* webpackChunkName: "materialIcons" */ "./icons/oldItems/iconFlashOld"),
  "icon-diode": () =>
    import(/* webpackChunkName: "materialIcons" */ "./icons/items/iconDiode"),
  "svg-icon": () =>
    import(/* webpackChunkName: "svgIcon" */ "./icons/svgIcon"),
  'svg-icon-converter': () =>
    import(
      /* webpackChunkName: "svgIconConverter" */
      './icons/svg-icon-converter'
    ),
  "file-icon": () =>
    import(/* webpackChunkName: "svgIcon" */ "./icons/file-icon"),
  'demo-better-scroll': () =>
    import(
      /* webpackChunkName: "demoBetterScrollFlex" */
      './demo/demo-better-scroll'
    ),
  "demo-hm-icons": () =>
    import(/* webpackChunkName: "demoHmIcons" */ "./icons/demo-hm-icons"),
  "demo-hm-file-icons": () =>
    import(/* webpackChunkName: "demoHmFileIcons" */ "./icons/demo-hm-file-icons"),
  "demo-hm-form-components": () =>
    import(/* webpackChunkName: "demoHmFormComponents" */ "./demo/demo-hm-form-components"),
  "demo-hm-typography": () =>
    import(/* webpackChunkName: "demoHmFormComponents" */ "./demo/demo-hm-typography"),
  "demo-hm-modal": () =>
    import(/* webpackChunkName: "demoHmIcons" */ "./layout/hm-modal/demo"),
  "demo-ts-vue-extend": () =>
    import(/* webpackChunkName: "demoHmIcons" */ "./demo/demo-ts-vue-extend"),
  "vue-pdfjs": () =>
    import(/* webpackChunkName: "pdfViewer" */ "./media/vue-pdfjs"),
  "hm-slides": () =>
    import(/* webpackChunkName: "hmSlides" */ "./media/hm-slides"),
  "hm-bootloader": () =>
    import(/* webpackChunkName: "hmBootloader" */ "./helpers/hm-loading/Bootloading.vue"),
  "hm-choose-material": () =>
    import(/* webpackChunkName: "hmChooiceMaterial" */ "./els/subject/chooseMaterial"),
  "hm-empty": () =>
    import(/* webpackChunkName: "hmEmpty" */ "./helpers/hm-empty"),
  "hm-widget-calendar": () =>
    import(/* webpackChunkName: "hmWidgetCalendar" */ "./controls/hm-widget-calendar"),
  "hm-calendar": () =>
    import(/* webpackChunkName: "hmCalendar" */ "./controls/hm-calendar"),
  "hm-htmlpage": () =>
    import(/* webpackChunkName: "HmHtmlpage" */ "./els/hm-htmlpage"),
  "hm-forum": () =>
    import(/* webpackChunkName: "HmForum" */ "./els/hm-forum"),
  "hm-forum-section": () =>
    import(/* webpackChunkName: "HmForumSection" */ "./els/hm-forum/components/section.vue"),
  "multi-select-areas-image": () =>
    import(/* webpackChunkName: "multiSelectAreasImage" */ "../libs/multi-select-areas-image/MultiSelectAreasImage.vue"),
  "hm-report-designer": () =>
    import(/* webpackChunkName: "HmReportDesigner" */ "./els/hm-report-designer/index.vue"),
};
