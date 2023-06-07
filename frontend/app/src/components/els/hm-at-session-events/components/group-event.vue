<template>
  <div class="hm-at-type-group__event hm-at-event">
    <div class="hm-at-event__image-wrapper">
      <img class="hm-at-event__image" v-if="data.icon" :src="data.icon">
      <img class="hm-at-event__image" v-else src="/images/content-modules/nophoto.png">
    </div>
    <div class="hm-at-event__info-wrapper">
      <a class="hm-at-event__title hm-at-event__title_link" v-if="data.executeLink" :href="data.executeLink">
        {{ data.name }}
      </a>
      <h4 class="hm-at-event__title" v-else>
        {{ data.name }}
      </h4>
      <ul class="hm-at-event__messages">
        <li class="hm-at-event__message" v-for="(message, index) in data.messages" :key="index">
          {{ message }}
        </li>
      </ul>
      <ul class="hm-at-event__tags">
        <li class="hm-at-event__tag" v-if="data.programmEvent.name">
          <hm-icons-status class="hm-at-event__tag-icon" color="#212121" type="tag" />
          <span>
            {{ data.programmEvent.name }}
          </span>
        </li>
      </ul>
    </div>
    <div class="hm-at-event__status-wrapper">
      <hm-icons-status :color="data.statusCompleted ? '#05C985' : '#DADADA'" type="status" />
      <a class="hm-at-event__more" v-if="data.isReportAvailable" :href="data.reportLink">
        Подробнее
      </a>
    </div>
  </div>
</template>

<script>
import HmIconsStatus from './icons';
export default {
  components: { HmIconsStatus },
  props: {
    data: {
      type: Object,
      default: () => {}
    }
  }
}
</script>

<style lang="scss">
.hm-at-event {
  display: flex;
  align-items: center;
  padding: 26px 0;
  border-bottom: 1px solid rgba(184, 197, 211, 0.3);

  & a {
    text-decoration: none;
  }

  &__image-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 134px;
  }
  &__image {
    width: 64px;
    height: 64px;
  }

  &__info-wrapper {
    display: flex;
    flex-direction: column;
    width: calc(100% - 134px - 168px)
  }
  &__title {
    font-weight: 500;
    font-size: 20px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #000000 !important;
    margin-bottom: 6px;
    &_link:hover {
      color: #2574CF !important;
    }
  }
  &__messages {
    list-style: none;
    padding: 0 !important;
    display: flex;
    flex-direction: column;
    margin-bottom: 6px;
  }
  &__message {
    font-weight: 300;
    font-size: 16px;
    line-height: 20px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
    margin-bottom: 6px;
  }
  &__tags {
    list-style: none;
    padding: 0 !important;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
  }
  &__tag {
    font-weight: 500;
    font-size: 12px;
    line-height: 24px;
    letter-spacing: -0.1px;
    color: #212121;
    background: rgba(33, 33, 33, 0.12);
    border-radius: 20px;
    margin-right: 12px;
    padding: 0 7px;
    display: flex;
    align-items: center;
    &-icon {
      margin-right: 5px;
    }
  }

  &__status-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    width: 168px;
  }
  &__more {
    font-weight: 300;
    font-size: 12px;
    line-height: 14px;
    letter-spacing: 0.15px;
    color: #979797;
    margin-top: 12px;
    &:hover {
      color: #2574CF;
    }
  }
}
</style>
