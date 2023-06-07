<template>
  <v-layout v-if="isInit" ref="element" wrap class="hm-workflow-general-tab">
    <v-flex xs12 :class="{ 'sm6 md8 order-xs2 order-sm1': deadline }">
      <div class="hm-workflow-general-tab_text">
        <p v-text="description"></p>
        <v-alert
          v-if="extendedDescription"
          class="hm-workflow-general-tab_extended-description mb-4"
          :value="true"
          color="grey"
          outlined
        >
          <p
            v-if="extendedDescription.comment"
            class="hm-workflow-general-tab_comment"
          >
            {{ extendedDescription.comment }}
          </p>
          <ul
            v-if="extendedDescription.files"
            class="hm-workflow-general-tab_files"
          >
            <li v-for="(file, key) in extendedDescription.files" :key="key">
              <template v-if="file.url && file.name">
                <a :href="file.url" target="_blank">{{ file.name }}</a>
                <small v-if="file.creator">({{ file.creator }})</small>
              </template>
            </li>
          </ul>
        </v-alert>
      </div>
      <div class="hm-workflow-general-tab_actions">
        <ul>
          <li
            v-for="(link, key) in controlLinksFormatted"
            :key="key"
            class="hm-workflow-general-tab_action"
          >
            <v-icon :color="link.icon.color">{{ link.icon.name }}</v-icon>
            <a
              v-if="link.url"
              class="hm-workflow-general-tab_action-content"
              :href="link.url"
              @click.prevent="dialogOpen(link)"
            >
              <span v-text="link.title"></span>
            </a>
            <span
              v-else
              class="hm-workflow-general-tab_action-content"
              v-text="link.title"
            ></span>
          </li>
        </ul>
      </div>
    </v-flex>
    <v-flex v-if="deadline" xs12 sm6 md4 order-xs1 order-sm2>
      <div
        class="hm-workflow-general-tab_deadline mb-2"
        :class="{ 'ml-4': !$vuetify.breakpoint.xsOnly }"
      >
        <v-alert
          v-if="isSuccess || isFailed"
          :value="true"
          :color="isSuccess ? 'success' : 'error'"
          outlined
        >
          <p v-text="deadline.message"></p>
          <span v-if="deadline.date" v-text="deadline.date"></span>
        </v-alert>
        <v-alert v-else :value="true" color="info" outlined>
          <div class="hm-workflow-general-tab_deadline-date">
            <span v-text="deadline.label"></span>
            <hm-date-picker
              :class="{ 'mr-4': !$vuetify.breakpoint.xsOnly }"
              :label="deadline.begin.label"
              :disabled="loading || !dateSaveUrl"
              :value="deadline.begin.date"
              :left="isInSidebar"
              name="hm-workflow-general-tab_deadline-begin"
              @input="beginDate = $event"
            ></hm-date-picker>
            <hm-date-picker
              v-if="deadline.begin.date !== deadline.end.date"
              :attribs="{
                label: deadline.end.label,
                disabled: loading || !dateSaveUrl
              }"
              :value="deadline.end.date"
              :left="isInSidebar"
              name="hm-workflow-general-tab_deadline-end"
              @input="endDate = $event"
            ></hm-date-picker>
          </div>
        </v-alert>
      </div>
    </v-flex>
    <hm-dialog
      :status="dialog.status"
      size="small"
      semanticAccent="none"
    >
      <template v-slot:content>
        <p>{{ dialog.text }}</p>
      </template>
      <template v-slot:buttons>
        <div>
          <v-btn
            :href="dialog.url"
            :disabled="dialog.loading"
            @click="dialog.loading = true"
            :loading="dialog.loading"
            color="primary"
          >
            Да
          </v-btn>
          <v-btn :disabled="dialog.loading" @click="dialogClose" color="primary" text>
            Отмена
          </v-btn>
        </div>
      </template>
    </hm-dialog>
  </v-layout>
</template>
<script>
import { STATUS } from "../../const";
import HmDatePicker from "../../../../../forms/hm-date-picker/index";
import { mapActions } from "vuex";
import HmDialog from "../../../../../controls/hm-dialog/HmDialog";
export default {
  components: {
    HmDatePicker,
    HmDialog
  },
  props: {
    status: {
      type: Number,
      required: true
    },
    deadline: {
      type: Object,
      default: () => {}
    },
    extendedDescription: {
      type: Object,
      default: () => {}
    },
    description: {
      type: String,
      default: null
    },
    controlLinks: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      isSuccess: null,
      isFailed: null,
      beginDate: null,
      initBeginDate: false,
      endDate: null,
      initEndDate: false,
      loading: false,
      dateSaveUrl: null,
      state: null,
      isInit: false,
      isInSidebar: null,
      dialog: {
        status: false,
        text: "",
        url: "",
        loading: false
      }
    };
  },
  computed: {
    controlLinksFormatted() {
      let links = this.controlLinks
        .filter(link => link.title)
        .map(link => {
          let iconColor = "default";
          let iconName = "info";
          if (link.hasOwnProperty("class")) {
            switch (link.class) {
              case "success":
              case "next":
                iconColor = "success";
                iconName = "check_circle_outline";
                break;
              case "fail":
                iconColor = "error";
                iconName = "highlight_off";
                if (!link.hasOwnProperty("confirm")) {
                  link.confirm =
                    "Вы действительно хотите прекратить выполнение бизнес-процесса?";
                }
                break;
            }
          }
          link.icon = { color: iconColor, name: iconName };
          return link;
        });
      return links;
    }
  },
  watch: {
    $props() {
      this.init();
    },
    beginDate() {
      if (!this.initBeginDate) return (this.initBeginDate = true);
      this.updateTime();
    },
    endDate() {
      if (!this.initEndDate) return (this.initEndDate = true);
      this.updateTime();
    }
  },
  created() {
    this.init();
  },
  mounted() {
    this.$nextTick(() => {
      this.isInSidebar = !!this.$refs.element.closest(".hm-sidebar");
    });

    this.isInit = true;
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    init() {
      this.isSuccess = this.status === STATUS.STATE_STATUS_PASSED;
      this.isFailed = this.status === STATUS.STATE_STATUS_FAILED;

      if (this.deadline) {
        this.dateSaveUrl = this.deadline.dateSaveUrl || null;
        this.state = this.deadline.state || null;
      }
    },
    updateTime() {
      if (this.loading || !this.dateSaveUrl) return;
      this.loading = true;
      let data = {
        field: "date",
        state: this.state,
        startDate: this.beginDate,
        endDate: this.endDate
      };
      this.$axios
        .post(this.dateSaveUrl, data)
        .then(response => {
          if (response.status !== 200) throw new Error();
        })
        .catch(() => this.addErrorAlert("Произошла ошибка!"))
        .then(() => (this.loading = false));
    },
    dialogOpen(link) {
      if (!link.confirm) return (window.location.href = link.url);
      else {
        this.dialog = {
          status: true,
          text: link.confirm,
          url: link.url,
          loading: false
        }
      }
    },
    dialogClose() {
      this.dialog = {
        status: false,
        text: "",
        url: "",
        loading: false
      }
    }
  }
};
</script>
<style lang="scss">
.hm-workflow-general-tab {
  &_actions {
    margin-top: 5px;
    ul {
      list-style: none;
      padding-left: 0;
      li {
        margin-bottom: 15px;
      }
    }
  }
  &_action {
    position: relative;
    &_action-content {
      padding-left: 30px;
      text-decoration: none;
    }
    i {
      position: absolute;
      left: 0;
      top: -3px;
      font-size: 18px;
      display: inline-block;
    }
    a {
      font-size: 14px;
      text-decoration: none;
      padding-left: 4px;
    }
    &.success,
    &.fail {
      background-color: transparent !important;
      border-color: transparent !important;
    }
  }
  &_files {
    small {
      display: inline-block;
    }
  }
  &_text {
    p {
      color: #888;
      padding-bottom: 8px;
    }
  }
}

</style>
