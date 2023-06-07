<template>
  <div class="hm-at-session-events">
    <p class="hm-at-session-events__description" v-if="description" v-html="description"></p>
    <v-card class="hm-at-session-events__list">
      <hm-type-group v-for="(group, index) in groups" :key="index" :group="group" />
    </v-card>
  </div>
</template>
<script>
import HmTypeGroup from "./components/type-group";
export default {
  components: { HmTypeGroup },
  props: {
    description: {
      type: String,
      default: "",
    },
    groups: {
      type: Object,
      default: () => {},
    }
  }
}
</script>
<style lang="scss">
.hm-at-session-events {
  display: flex;
  flex-direction: column;
  width: 100%;
  &__description {
    padding: 16px;
    background: #FFFAE5;
    border-radius: 4px;
    width: 100%;
    font-style: normal;
    font-weight: 300;
    font-size: 14px;
    line-height: 18px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
  }
}
</style>
