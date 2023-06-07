<template>
  <div class="hm-assessment-form__user">
    <h3 class="hm-assessment-form__user-title">
      Оцениваемые пользователь
    </h3>
    <p class="hm-assessment-form__user-item">
      <icons class="hm-assessment-form__user-icon" type="name" />
      <span class="hm-assessment-form__user-item-value">{{ `${data.user.LastName} ${data.user.FirstName} ${data.user.Patronymic}` }}</span>
    </p>
    <p class="hm-assessment-form__user-item">
      <icons class="hm-assessment-form__user-icon" type="department" />
      <span>Подразделение: <span class="hm-assessment-form__user-item-value">{{ data.department.name }}</span></span>
    </p>
    <p class="hm-assessment-form__user-item">
      <icons class="hm-assessment-form__user-icon" type="position" />
      <span>Должность: <span class="hm-assessment-form__user-item-value">{{ data.position.name }}</span></span>
    </p>
    <p class="hm-assessment-form__user-item">
      <icons class="hm-assessment-form__user-icon" type="profile" />
      <span>Профиль должности: <span class="hm-assessment-form__user-item-value">{{ data.profile.name }}</span></span>
    </p>
  </div>
</template>
<script>
import icons from './icons.vue';
export default {
  components: {
    icons
  },
  props: {
    data: {
      type: Object,
      default: () => {}
    }
  }
}
</script>
<style lang="scss">
.hm-assessment-form__user {
  margin-bottom: 34px;
  &-title {
    font-weight: 300;
    font-size: 24px;
    line-height: 32px;
    letter-spacing: 0.02em;
    color: #000000;
    margin-bottom: 20px;
  }
  &-item {
    font-weight: 300;
    font-size: 18px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #000000;
    &-value {
      font-weight: 500;
    }
  }
  &-icon {
    margin-right: 26px;
  }
}
</style>
