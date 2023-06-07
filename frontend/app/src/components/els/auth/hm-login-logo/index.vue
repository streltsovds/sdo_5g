<template>
  <div class="hm-login-logo">
    <logo v-if="!logo" class="hm-login-logo__image" />
    <img  v-else :src="logo" class="hm-login-logo__image"/>
    <div v-if="description" v-html="description"></div>
    <div  v-else class="hm-login-logo__wrapper">
      <h2
        class="hm-login-logo__title"
        :class="{ 'hm-login-logo__title_margin': logo }"
      >eLearning Server 5G</h2>
      <p class="hm-login-logo__text">Платформа предлагает самые современные инструменты для формирования ценностей сотрудников и слушателей, позволяя каждому стать заметным через здоровое стремление к признанию.</p>
    </div>

  </div>
</template>
<script>
export default {
  props: ["logo", "description"],
  components: {
    'logo': () => import('./imageLogo.vue')
  },
}
</script>

<style lang="scss">
  .hm-login-logo {
    position: relative;
    text-align: start;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    width: 360px;
    padding: 0 30px;
    padding-top: 46px;
    padding-bottom: 30px;
    background: rgba(45, 107, 177, 0.52);
    backdrop-filter: blur(24px);
    border-radius: 16px 0px 0px 16px !important;

    & h1,
    & h2,
    & h3,
    & h4,
    & h5,
    & h6 {
      font-family: Roboto, sans-serif;
      font-style: normal;
      font-weight: 500;
      font-size: 30px;
      line-height: 32px;
      letter-spacing: 0.04em;
      color:#FFFFFF;
      margin-top: 10px;
      margin-bottom: 10px;
    }

    & p {
      font-family: Roboto, sans-serif;
      font-style: normal;
      font-weight: 300;
      font-size: 16px;
      line-height: 22px;
      letter-spacing: 0.04em;
      color:#FFFFFF
    }

    // &__image-svg {
    //   position: absolute;
    //   top: 46px;
    //   left: 50%;
    //   transform: translate(-50% , 0);
    //   width: calc(100% - 60px);
    // }

    &__image {
      width: 100%;
      margin-bottom: 10px;
      max-height: 142px;
      object-fit: contain;
    }

    &__wrapper {
      display: flex;
      flex-direction: column;
    }

    &__title {
      font-family: Roboto, sans-serif;
      font-style: normal;
      font-weight: 500;
      font-size: 30px;
      line-height: 32px;
      letter-spacing: 0.04em;
      color:#FFFFFF;
      // margin-top: 10px !important;
      margin-bottom: 10px;

      // &_margin {
      //   margin-top: 10px !important;
      // }
    }

    &__text {
      font-family: Roboto, sans-serif;
      font-style: normal;
      font-weight: 300;
      font-size: 16px;
      line-height: 22px;
      letter-spacing: 0.04em;
      color:#FFFFFF
    }
  }

  @media screen and (max-width: 768px) {
    .hm-login-logo {
      max-width: 343px;
      width: 100%;
      border-radius: 16px 16px 0px 0px !important;
      padding: 0 20px;
      padding-top: 26px;
      padding-bottom: 36px;

      &__image-svg {
        max-width: 229px;
        width: calc(100% - 40px);
        top: 26px;
      }

      &__title {
        margin-top: 98px;
      }

      &__text {
        font-size: 15px;
        line-height: 19px;
      }
    }
  }
  @media screen and (max-width: 440px) {
    .hm-login-logo {
      & h1,
      & h2,
      & h3,
      & h4,
      & h5,
      & h6 {
        font-size: 25px;
      }
      &__title {
        font-size: 25px;
      }
    }
  }
</style>
