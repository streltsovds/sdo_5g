<template>
  <v-card class="hm-matrix-progress__item" :style="{'border-color': color}">
    <h3 class="hm-matrix-progress__item-title" :title="title">
      {{ title }}
    </h3>
    <p class="hm-matrix-progress__item-description" :title="description">
      {{ description }}
    </p>
    <ul class="hm-matrix-progress__item-users">
      <li
        class="hm-matrix-progress__item-user"
        v-for="(user, index) in users"
        :key="index"
      >
        <img class="hm-matrix-progress__item-user-photo" :src="user.photo" :alt="user.fio">
        <span class="hm-matrix-progress__item-user-name">{{ user.fio }}</span>
        <div class="hm-matrix-progress__item-user-links">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn v-bind="attrs" v-on="on" class="hm-matrix-progress__item-user-link" icon small elevation="0" :href="user.userReportLink">
                <icons name="reports" width="15" height="15" />
              </v-btn>
            </template>
            <span>Индивидуальный отчет</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn v-bind="attrs" v-on="on" class="hm-matrix-progress__item-user-link" icon small elevation="0" :href="user.userDevelopmentLink">
                <icons name="openNew" width="17" height="17" />
              </v-btn>
            </template>
            <span>Развитие</span>
          </v-tooltip>
        </div>
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <span v-bind="attrs" v-on="on" class="hm-matrix-progress__item-user-date">{{ user.endDate }}</span>
          </template>
          <span>Дата последней оценки</span>
        </v-tooltip>
      </li>
    </ul>
  </v-card>
</template>
<script>
import icons from "../../icons/svgIcon";
export default {
  components: {
    icons
  },
  props: {
    users: {
      type: Array,
      default: () => []
    },
    title: {
      type: String,
      default: ""
    },
    description: {
      type: String,
      default: ""
    },
    color: {
      type: String,
      default: ""
    }
  }
}
</script>
<style lang="scss">
.hm-matrix-progress__item {
  display: flex;
  flex-direction: column;
  padding: 26px 0;
  padding-left: 26px;
  background-color: #fff;
  box-shadow: 0px 10px 30px rgba(209, 213, 223, 0.5);
  border-radius: 4px;
  width: 100%;
  border-left: 12px solid black;
  &-title {
    font-weight: 500;
    font-size: 20px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
    margin-bottom: 12px;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 16px;
  }
  &-description {
    font-weight: 400;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
    padding-bottom: 12px;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 16px;
    border-bottom: 1px solid #DADADA;
  }
  &-users {
    display: flex;
    flex-direction: column;
    width: calc(100% - 2px);
    list-style: none;
    padding: 0 !important;
    padding-right: 14px !important;
    margin-right: 2px !important;
    max-height: 360px;
    overflow: auto;
    margin-top: 12px;

    &::-webkit-scrollbar-thumb:hover {
      background: #70889E;
    }
    &::-webkit-scrollbar-track {
      -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
      border-radius: 4px;
    }
    &::-webkit-scrollbar {
      width: 4px;
      height: 4px;
    }
    &::-webkit-scrollbar-thumb {
      background-color: #706e6e;
      border-radius: 4px;
    }
  }
  &-user {
    font-weight: 400;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #70889E;
    margin-top: 12px;
    display: flex;
    align-items: center;
    &-photo {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      margin-right: 10px;
    }
    &-name {
      margin-right: 10px;
    }
    &-links {
      display: flex;
      align-items: center;
    }
    &-date {
      margin-left: auto;
    }
    &:first-child {
      margin-top: 0;
    }
  }
}
</style>
