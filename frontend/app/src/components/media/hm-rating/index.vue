<template>
  <div class="hm-rating">
    <div v-for="(el, i) in total" :key="i">
      <star-fill v-if="count > i" />
      <star v-else />
    </div>
  </div>
</template>

<script>
  import Star from "./elements/star";
  import StarFill from "./elements/starFill";
  export default {
    components: {StarFill, Star},
    props: {
      type: {
        type: String,
        default: 'star'
      },
      total: {
        type: [String, Number],
        default: 0
      },
      count: {
        type: [String, Number],
        default: 0
      }
    }
  }
</script>

<style lang="scss">
  .hm-rating {
    display: flex;
    flex-wrap: nowrap;
    justify-content: flex-end;
  }
</style>
