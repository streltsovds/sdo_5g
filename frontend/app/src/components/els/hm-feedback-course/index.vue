<template>
  <div v-if="feedbacks.length > 0" class="hm-feedback">
    <h3 class="hm-feedback__title">Отзывы</h3>
    <ul class="hm-feedback__list">
      <div
        class="hm-feedback__item"
        v-for="(feedback, key) in feedbacks"
        :key="key"
      >
        <div class="hm-feedback__photo">
          <img v-if="feedback.user.photo" :src="feedback.user.photo" alt="фото пользователя" />
          <user-icon v-else />
        </div>
        <div class="hm-feedback__wrapper">
          <div class="hm-feedback__title-wrapper">
            <h4 class="hm-feedback__name">{{ feedback.user.FirstName }} {{ feedback.user.LastName }}</h4>
            <p class="hm-feedback__date">{{ feedback.date }}</p>
          </div>
          <p class="hm-feedback__text">{{ feedback.review }}</p>
        </div>
      </div>
    </ul>
  </div>
</template>

<script>
import userIcon from "@/components/icons/items/iconUser.vue";
export default {
  name: "HmFeedback",
  components: { userIcon },
  props: {
    feedbacks: {
      type: Array,
      default: () => []
    }
  },
  mounted() {
    console.log(this.feedbacks)
  }
}
</script>

<style lang="scss">
  .hm-feedback {
    padding: 16px;
    padding-top: 0;
    font-family: Roboto, sans-serif;
    font-style: normal;
    letter-spacing: 0.02em;

    &__title {
      font-weight: 500;
      font-size: 20px;
      color: #1E1E1E;
      margin: 0;
      padding: 0;
      margin-bottom: 26px;
    }

    &__list {
      padding: 0 !important;
      list-style: none;
      display: flex;
      flex-direction: column;
      width: 100%;
    }

    &__item {
      font-weight: normal;
      font-size: 14px;
      line-height: 21px;
      width: 100%;
      padding: 20px;
      padding-right: 74px;
      margin-bottom: 16px;
      display: flex;
      background: rgba(250, 243, 216, 0.4);
      border-radius: 10px;

      &:nth-child(2n) {
        background: rgba(212, 227, 251, 0.4);
      }
    }

    &__photo {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      margin-right: 20px;
      background-color: #fff;

      & img {
        width: 34px;
        height: 34px;
        border-radius: 50%;
      }
    }

    &__wrapper {
      width: 100%;
      display: flex;
      flex-direction: column;
    }

    &__title-wrapper {
      width: 100%;
      display: flex;
      margin-bottom: 3px;
    }

    &__name {
      width: 50%;
      font-weight: 500;
      color: #131313;
      margin: 0;
      padding: 0;
    }

    &__date {
      width: 50%;
      color: #3E4E6C;
      mix-blend-mode: normal;
      opacity: 0.7;
      margin: 0 !important;
      padding: 0;
    }

    &__text {
      width: 100%;
      color: #131313;
      margin: 0 !important;
      padding: 0;
    }
  }
</style>
