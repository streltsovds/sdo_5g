<template>
  <div class="hm-at-session-events__type-group hm-at-type-group">
    <h3 class="hm-at-type-group__name">
      {{ group.name }}
    </h3>
    <ul class="hm-at-type-group__events-list">
      <li class="hm-at-type-group__event" v-for="(event, index) in group.events" :key="index">
        <hm-group-event :data="event" />
      </li>
    </ul>
  </div>
</template>

<script>
import HmGroupEvent from './group-event';
export default {
  components: { HmGroupEvent },
  props: {
    group: {
      type: Object,
      default: () => {},
    }
  }
}
</script>

<style lang="scss">
.hm-at-type-group {
  display: flex;
  flex-direction: column;

  &__name {
    padding: 26px;
    font-weight: 300;
    font-size: 24px;
    line-height: 24px;
    letter-spacing: 0.01em;
    color: #000000;
    border-bottom: 1px solid rgba(184, 197, 211, 0.3);
  }

  &__events-list {
    display: flex;
    flex-direction: column;
    list-style: none;
    padding: 0 !important;
  }
}
</style>
