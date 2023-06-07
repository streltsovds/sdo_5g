<template>
  <div class="modal__category__job">
    <div class="modal__category__job-title">
      <span class="body-2">{{ _('Выберите категорию') }}</span>
    </div>
    <div class="modal__category__job-cat">
      <div v-for="(el, i) in getCategory" :key="i" @click="actionCategory(el.name)">
        <svg-icon :name="el.icon" :color="el.color" :width="15" />
        <span class="caption">{{ _(el.title) }}</span>
      </div>
    </div>
  </div>
</template>

<script>
  import SvgIcon from "../../../icons/svgIcon";
  export default {
    name: "modalCategory",
    components: {SvgIcon},
    props: {
      users:{
        type: String,
        default: 'users'
      },
      modalCategory: {
        type: Array,
        default: () => []
      },
      test: {
        type: String,
        default: 'проверка обновления'
      }
    },
    computed: {
      getCategory() {
        return this.modalCategory.filter(el => el.access.find(a => a === this.users))
      }
    },
    methods: {
      actionCategory(data) {
        this.$emit('category', data)
      }
    }
  }

</script>

<style lang="scss">
  .modal__category__job {
    min-width: 196px;
    width: auto;
    height: auto;
    display: flex;
    flex-direction: column;
    background: #FFFFFF;
    box-shadow: 0 3px 4px rgba(0, 0, 0, 0.12), 0 2px 4px rgba(0, 0, 0, 0.14);
    border-radius: 4px;
    padding: 16px 0 24px 0;
    position: absolute;
    bottom: 40px;
    z-index: 1000;
    > div {
      display: flex;
      align-items: center;
    }
    &-title {
      margin-bottom: 16px;
      padding: 0 16px;
    }
    &-cat {
      flex-direction: column;
      > div {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        height: 30px;
        width: 100%;
        cursor: pointer;
        padding: 0 16px;
        &:hover {
          background: rgba(212, 227, 251, 0.3);;
        }
        > svg {
          margin-right: 12px;
        }
        > span {
          white-space: nowrap;
        }
      }
    }
  }
</style>
