<template>
  <div class="modal-comment">
    <div class="modal-comment__block">
      <div class="modal-comment__block-header">
        <span>{{ _(headerComment) }}</span>
      </div>
      <div class="modal-comment__block-body">
        <textarea ref="textareaForm" :placeholder="_('')" v-model="textForm" resize="none"></textarea>
      </div>
      <div class="modal-comment__block-footer">
        <div class="modal-comment__block-footer__save" @click="eventClick(true)"><span>{{ _('Сохранить') }}</span></div>
        <div class="modal-comment__block-footer__close" @click="eventClick(false)"><span>{{ _('Отмена') }}</span></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "modalComment",
  props: {
    comment: {
      type: String,
      default: ''
    },
    headerComment: {
      type: String,
      default: 'Добавить комментарий'
    }
  },
  data() {
    return {
      textForm: this.comment
    }
  },
  methods: {
    eventClick(type) {
      if(!type) {
        this.$emit('closeModal', {save: false})
      }else {
        this.$emit('closeModal', {save: true, comment: this.textForm})
      }
    }
  }
}
</script>

<style lang="scss">
.modal-comment {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 101;
  &__block {
    width: 360px;
    height: auto;
    padding: 12px 16px;
    background: #FFFFFF;
    box-shadow: 0 8px 10px rgba(0, 0, 0, 0.2), 0 6px 30px rgba(0, 0, 0, 0.12);
    border-radius: 4px;
    display: flex;
    flex-direction: column;
    &-header {
      width: 100%;
      height: 40px;
      display: flex;
      justify-content: flex-start;
      align-items: center;
      > span {
        font-weight: 500;
        font-size: 20px;
        line-height: 24px;
        letter-spacing: 0.02em;
        color: #1E1E1E;
      }
    }
    &-body {
      > textarea {
        width: 100%;
        height: 100px;
        border: 1px solid #eee;
        padding: 4px;
      }
      > textarea:focus, > textarea:active, textarea:hover {
        border: 1px solid #d5d5d5;
        outline: none;
      }
    }
    &-footer {
      display: flex;
      flex-wrap: nowrap;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      height: auto;
      padding: 6px 0;
      margin-top: 12px;
      > div {
        width: 150px;
        height: 36px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        > span {
          font-size: 16px;
          line-height: 24px;
        }
      }
      &__close {
        border: 1px solid #485B70;
        border-radius: 4px;
        > span {
          color: #1E1E1E;
        }
      }
      &__save {
        background: #FF9800;
        border-radius: 4px;
        > span {
          color: #FFFFFF;
        }
      }
    }
  }
}
</style>
