<template>
  <div class="hm-matrix-progress">
    <hm-matrix-wrapper v-for="(department, index) in departments" :key="index" :department="department" :users="formattedUsers(index)"  />
  </div>
</template>
<script>
import HmMatrixWrapper from "./hm-matrix-wrapper";
export default {
  components: {
    HmMatrixWrapper
  },
  props: {
    departments: {
      type: Object,
      default: () => {}
    },
    users: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    formattedUsers(id) {
      const category = {
        risk: [],
        analysis: [],
        diligents: [],
        perspective: [],
        experts: [],
        leaders: [],
      };
      this.users[id].forEach(item => {
        if(item.matrixBlock === 1) category['risk'].push(item)
        else if(item.matrixBlock === 2) category['analysis'].push(item)
        else if(item.matrixBlock === 3) category['diligents'].push(item)
        else if(item.matrixBlock === 4) category['perspective'].push(item)
        else if(item.matrixBlock === 5) category['experts'].push(item)
        else if(item.matrixBlock === 6) category['leaders'].push(item)
      });
      return category;
    }
  }
}
</script>
<style lang="scss">
.hm-matrix-progress {
  display: flex;
  flex-direction: column;
  width: 100%;
  margin-bottom: 26px;
  &__title {
    font-weight: 500;
    font-size: 20px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
    margin-bottom: 22px;
  }
  &__wrapper {
    display: grid;
    grid-template-columns: repeat(2, minmax(200px, 1fr));
    grid-column-gap: 25px;
    grid-row-gap: 25px;
  }
}
@media(max-width: 1600px) {
  .hm-matrix-progress {
    &__wrapper {
      grid-column-gap: 16px;
      grid-row-gap: 16px;
    }
  }
}
@media(max-width: 768px) {
  .hm-matrix-progress {
    &__wrapper {
      grid-column-gap: 8px;
      grid-row-gap: 8px;
    }
  }
}
</style>
