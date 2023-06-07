<template>
  <div v-if="countPage > 1" class="hm-pagination">
    <v-pagination
      v-model="page"
      :length="countPage"
      :total-visible="totalVisible"
    ></v-pagination>
  </div>
</template>
<script>
export default {
  props: {
    currentPage: {
      type: Number,
      default: 1
    },
    countPage: {
      type: Number,
      default: 0
    },
    totalVisible: {
      type: Number,
      default: 5
    }
  },
  data() {
    return {
      page: 1
    };
  },
  watch: {
    currentPage(v) {
      this.page = v;
    },
    page(v) {
      this.$emit("paginate", v);
    }
  }
};
</script>
