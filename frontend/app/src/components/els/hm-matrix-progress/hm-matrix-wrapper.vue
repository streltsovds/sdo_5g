<template>
  <div class="hm-matrix-progress">
    <h2 class="hm-matrix-progress__title">
      {{ department }}
    </h2>
    <div class="hm-matrix-progress__wrapper">
      <hm-matrix-item title="Эксперты" description="Удерживать" color="#05C985" :users="users.experts" />
      <hm-matrix-item title="Лидеры" description="Продвигать и пересматривать зарплату" color="#4CB648" :users="users.leaders" />
      <hm-matrix-item title="Старательные" description="На своем месте" color="#FF9800" :users="users.diligents" />
      <hm-matrix-item title="Перспективные" description="Развивать" color="#00ACED" :users="users.perspective" />
      <hm-matrix-item title="Группа риска" description="Кандидаты на выход" color="#E57373" :users="users.risk" />
      <hm-matrix-item title="Группа разбора" description="Анализировать причины низкой результативности" color="#7F31F1" :users="users.analysis" />
    </div>
  </div>
</template>
<script>
import HmMatrixItem from "./hm-matrix-item";
export default {
  components: {
    HmMatrixItem
  },
  props: {
    department: {
      type: String,
      default: ""
    },
    users: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    formattedUsers(id) {
      this.users
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
