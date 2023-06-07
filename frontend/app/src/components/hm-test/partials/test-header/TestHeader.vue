<template>
  <v-toolbar
    class="hm-test-header__wrapper toolbar-align toolbar-fixed"
    :class="$vuetify.breakpoint.smAndDown ? 'hm-test-header__wrapper--breakpoint-sm-and-down' : ''"
  >
    <component :is="'v-slide-y-transition'" v-if="hasContext">
      <test-header-details
        v-if="areDetailsShown"
        :details="detailsProps"
        @click-outside="areDetailsShown = false"
      />
    </component>

   <!-- <v-toolbar-items v-if="hasContext">
      <v-btn
        :large="$vuetify.breakpoint.lgAndUp"
        :small="$vuetify.breakpoint.smAndDown"
        icon
        @click="areDetailsShown = !areDetailsShown"
      >
        <v-icon> {{ areDetailsShown ? "expand_less" : "expand_more" }} </v-icon>
      </v-btn>
    </v-toolbar-items>-->
    <div class="hm-test-header">
      <div class="hm-test-header__title mr-4">
        <span>{{ title }} <!-- заголовок теста -->
        </span>
      </div>
      <test-timer
        class="hm-test-header__timer"
        v-if="limitTime"
        :time="time"
        @end="onTimerEnd"
      />
      <v-tooltip-simple
        :content-style="{display: 'inline-flex'}"
        :text="testType === 'poll'
          ? _('Прервать опрос')
          : _('Прервать тестирование')
        "
      >
        <mouse-state>
          <div class="hm-test-header__close ml-4"
               @click="onFinalizeBtnClick"
               :title="_()"
               slot-scope="{ mouseState }"
          >
            <icon-cross-in-circle
              :color="mouseState.hover
                ? getColor('warning', '#FF0000')
                : '#000000'
              "
              :title="''"
            />
          </div>
        </mouse-state>
      </v-tooltip-simple>
    </div>
  </v-toolbar>
</template>

<script>
import iconCrossInCircle from "@/components/icons/items/iconCrossInCircle";
import TestHeaderDetails from "./partials/TestHeaderDetails";
import TestTimer from "./../TestTimer";
import VTooltipSimple from "@/components/helpers/v-tooltip-simple";
// import imerge from "@/utilities/immutableMerge";
import MouseState from "@/components/helpers/mouse-state";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";

export default {
  components: {
    iconCrossInCircle,
    MouseState,
    TestHeaderDetails,
    TestTimer,
    VTooltipSimple,
  },
  mixins: [VueMixinConfigColors],
  props: {
    title: {
      type: String,
      default: () => false
    },
    hasContext: Boolean,
    context: {
      type: Object,
      default: () => ({})
    },
    time: {
        type: Number,
        default: null
    },
    limitTime: {
        type: Number,
        default: null
    },
    testType: {
      type: String,
      default: null,
    }
  },
  data() {
    return {
      areDetailsShown: false
    };
  },
  computed: {
    detailsProps() {
      return this.context;
    },
    headerClass() {
      if (this.$vuetify.breakpoint.lgAndUp) {
        return "display-2";
      }
      if (this.$vuetify.breakpoint.mdOnly) {
        return "display-1";
      }
      if (this.$vuetify.breakpoint.smOnly) {
        return "display-1";
      }
      if (this.$vuetify.breakpoint.xsOnly) {
        return "headline";
      }
      return "headline";
    }
  },
  methods: {
    // imerge,
    onTimerEnd() {
      this.isTimeEnded = true;
      this.doFinalize = true;
      this.handleNextClick().then(() => {
        setTimeout(() => this.endTest(), 1000 * 30);
      });
    },
    onFinalizeBtnClick() {
      this.$emit(`finalize`);
    },
  }
};
</script>

<style lang="scss">
.toolbar-align {
  height: auto !important;
}
.toolbar-align .v-toolbar__content {
  height: auto !important;
  display: flex;
  align-items: center;
  padding-left: 0.5rem;
}

.hm-test-header__wrapper {
  box-shadow: none;
  background: none !important;
  .v-toolbar__content {
    color: #000;

    padding: 0 !important;
  }
}

.hm-test-header__timer {
  height: auto;
  box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);
  flex-basis: 600px;
}

.hm-test-header {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;

  &__title {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;

    /** место для кнопки закрытия */
    flex-basis: calc(100% - 64px);
    flex-grow: 1;

    > span {
      font-weight: 300;
      font-size: 34px;
      line-height: 32px;
      letter-spacing: 0.02em;
      color: #2a2a2a;
    }
  }
}
.hm-test-header__close {
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  > svg {
    width: 28px !important;
    height: 28px !important;
  }
}

.hm-test-header__wrapper--breakpoint-sm-and-down {
  .hm-test-header {
    flex-wrap: wrap;

    &__timer {
      flex-basis: 100%;

      margin-top: 16px !important;
      order: 3;
    }

    &__title {
      > span {
        font-size: 24px;
      }
    }
  }

  .hm-test-header__close > svg {
    //width: 24px !important;
    //height: 24px !important;
  }
}
</style>
