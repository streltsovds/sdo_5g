<template>
  <div class="empty-block" :style="styleParent">
    <div :style="style">
      <span>{{ label }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: "EmptyBlock",
  props: {
    typeBlock: {
      type: String,
      default: 'block'
    },
    label: {
      type: String,
      default: ''
    }
  },
  computed: {
    style() {
      if (this.typeBlock === 'short') {
        return {
          justifyContent: 'flex-start',
          color: '#1e1e1e',
          fontSize: '16px',
          fontWeight: '500',
          lineHeight: '24px'
        }
      } else {
        return {
          justifyContent: 'center',
          height: '43px',
          display: 'flex',
          alignItems: 'center',
          margin: '0 16px',
          background: '#F5F5F5',
          borderRadius: '6px',
          color: '#3E4E6C',
          fontSize: '14px',
          fontWeight: 'normal',
          lineHeight: '21px'
        }
      }
    },
    styleParent() {
      if (this.typeBlock === 'block') {
        return {
          padding: '16px'
        }
      }
    }
  }

}
</script>

<style lang="scss">
  .empty-block {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;

    > div {
      width: 100%;
    }

    > span {
      font-weight: inherit;
      color: inherit;
      font-size: inherit;
      line-height: inherit;

    }
  }
</style>
