<template>
  <div class="empty-search" >
    <div class="empty-search__icon" v-if="icon">
      <svg-icon name="search" color="#FFFFFF" width="48" height="48" stroke-width=".5"/>
    </div>
    <div class="empty-search__label" v-if="label && label !== ''">
      <span>{{ label }}</span>
    </div>
    <div class="empty-search__sub-label" v-if="subLabel && subLabel !== '' ">
      <span v-html="subLabel"></span>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
export default {
  name: "emptySearch",
    components: {SvgIcon},
    props: {
    label: {
      type:String,
      default: ''
    },
    subLabel: {
      type: String,
      default: ''
    },
    icon: {
      type: Boolean,
      default: true
    }
  }
}
</script>

<style lang="scss">
.empty-search {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  padding: 16px 24px;
  &__icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    /* на старых мониторах, решили с дизайнером пока убрать прозрачность */
    /* background: rgba(212, 227, 251, 0.5); */
    background: rgba(212, 227, 251);
    margin-bottom: 16px;
  }
  &__label {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 8px;
    > span {
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #000000;
    }
  }
  &__sub-label {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    > span {
      font-weight: normal;
      font-size: 14px;
      line-height: 21px;
      text-align: center;
      letter-spacing: 0.02em;
      color: #000000;

    }
  }
}
</style>
