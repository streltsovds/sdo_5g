<template>
  <div class="congratulations">
    <div class="congratulations__title">
      {{ _('Автоматическое завершение курса') }}
    </div>
    <img class="congratulations__img" src="/images/autofinish.png" alt="">
    <p class="congratulations__text">
      <slot />
    </p>
    <button class="congratulations__button" @click="redirect">
      {{ _('Продолжить') }}
    </button>
  </div>
</template>

<script>

export default {
  name: "HmCongratulations",
  props:{
    redirectUrl: {
      type: String,
      required: true,
    }
  },
  methods:{
    redirect(){
      window.location.href = this.redirectUrl;
    }
  }
};
</script>

<style lang="scss">
    .congratulations{
      width: 100%;
      height: 100%;
      background: rgba(212, 227, 251, 0.6);
      box-shadow: 0px 10px 30px rgba(209, 213, 223, 0.5);
      border-radius: 4px;
      text-align: center;
      padding: 50px;
        &__title{
          font-weight: normal;
          font-size: 24px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
        }
        &__img{
          margin-top: 75px;
        }
        &__text{
          font-weight: normal;
          margin-top: 25px;
          font-size: 16px;
          line-height: 24px;
          text-align: center;
          letter-spacing: 0.02em;
          color: #1E1E1E;
        }
        &__button{
          font-weight: 500;
          font-size: 14px;
          line-height: 24px;
          letter-spacing: 0.16px;
          text-transform: uppercase;
          color: #FFFFFF;
          background: #FF9800;
          border-radius: 4px;
          padding: 5px 10px;
          margin-top: 25px;
        }
    }
</style>
