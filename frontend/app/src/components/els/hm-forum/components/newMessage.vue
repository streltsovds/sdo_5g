<template>
  <div class="new-message-form">
    <hm-tiny-mce
      :key="status"
      :attribs="attribs"
      :errors="errors"
      name="text"
      stylePath="/frontend/app/css/app.css?contenthash=9c2afbda"
      :value="value"
      @getValue="getValue"
      :disabled="disabled"
    />
    <div class="new-message-form__buttons-wrapper">
      <v-btn @click="sendMessage" elevation="0" class="new-message-form__button">{{textButton}}</v-btn>
    </div>
  </div>
</template>
<script>
import HmTinyMce from '@/components/forms/hm-tiny-mce'
export default {
  props: ['text', 'textButton', 'disabled', 'targetHash'],
  components: {
    HmTinyMce
  },
  data() {
    return {
      attribs: {
        "height":130,
        "id":"text",
        "required":true,
        "label":"",
        "description":"",
        "formId":null,
        "disabled":null,
        "lang":"ru",
        "target_hash": this.targetHash
      },
      errors: [],
      value: this.text,
      status: 1
    }
  },
  methods: {
    getValue(value) {
      this.value = value;
    },
    sendMessage() {
      if(this.value) {
        this.$emit('sendMessage', this.value);
        this.value = '';
        this.status++ // обновляет tinymce для удаления текста из редактора
      }
    }
  }
}
</script>
<style lang="scss">
.new-message-form {
  display: flex;
  flex-direction: column;
  &__buttons-wrapper {
    display: flex;
    align-items: center;
    margin-top: 16px;
  }
  &__button {
    margin-right: 16px;
    background-color: #F5F6F9;
    color: #1E1E1E;
    &:last-child {
      margin-right: 0;
    }
  }
}
</style>
