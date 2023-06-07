<template>
  <div class="hm-login-form" :is-modal="isModal">
    <v-card
      class="infoblock-card login-card"
      :light="true"
    > 
      <hm-login-logo 
        :logo="data.logo"
        :description="data.index_description"
      >
        {{ data.index_description }}
      </hm-login-logo>
      <div class="login-card__wrapper">
        <v-card-title class="login-card__title title grey lighten-4">
          {{ _('Авторизация') }} 
        </v-card-title>
        <slot />
      </div>
    </v-card>
  </div>
</template>

<script>
import HmLoginLogo from './hm-login-logo';
export default {
  components: { HmLoginLogo },
  props: {
    isModal: {
      type: Boolean,
      default: false,
    },
    data: {
      type: Object,
      default: () => ({}),
      require: true
    }
  }
}
</script>

<style lang="scss">
.hm-login-form{
    &:not([is-modal]){
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    &[is-modal]{
        width: 100%;
        max-width: 850px;
    }
}
</style>