<template>
  <div class="INFOBLOCK infoblock-feedback">
    <v-card-text
      v-if="!feedback || (Array.isArray(feedback) && !feedback.length)"
    >
      <v-alert type="info" value="true" outlined>
        <span>Отсутствуют данные для отображения</span>
      </v-alert>
    </v-card-text>
    <template v-else>
      <v-list subheader>
        <div
          v-for="(feedbackData, key, i) in feedback"
          :key="key"
          class="infoblock-feedback_course"
        >
          <v-subheader class="infoblock-feedback_course-title">
            <span>{{ feedbackData.name }}</span>
          </v-subheader>

          <v-list-item
            v-for="(feedbackItem, key2) in feedbackData.feedbacks"
            :key="key2"
            :href="feedbackItem.url"
            class="infoblock-feedback_item"
          >
            <v-list-item-content>
              <v-list-item-title class="infoblock-feedback_item-title">
                <span>{{ feedbackItem.name }}</span>
              </v-list-item-title>
              <v-list-item-subtitle v-if="feedbackItem.p_mid">
                для {{ feedbackItem.s_name }}
              </v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-divider class="infoblock-feedback__separator" v-if="i !== Object.keys(feedback).length - 1" />
        </div>
      </v-list>
    </template>
  </div>
</template>
<script>
export default {
  props: {
    feedback: {
      type: Object,
      default: () => {}
    }
  }
};
</script>
<style lang="scss">
.infoblock-feedback {
  a {
    font-size: 13px;
    /*height: auto;*/
    height: 37px;
    /*min-height: auto !important;*/
    margin-top: 2px;
    margin-bottom: 2px;
    padding-left: 26px !important;
  }
  .infoblock-feedback_course-title {
    height: auto;
    margin: 16px 0;
    padding-left: 26px!important;
    > span {
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #70889e;
    }
  }
  .infoblock-feedback_item {
    &-title {
      > span {
        font-size: 14px;
        line-height: 21px;
        letter-spacing: 0.02em;
        color: #1e1e1e;
      }
    }
  }
  &__separator {
    margin-left: 26px;
  }
}
</style>
