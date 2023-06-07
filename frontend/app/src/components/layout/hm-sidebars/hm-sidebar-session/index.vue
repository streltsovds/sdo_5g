<template>
  <div class="hm-sidebar-session">
    <div class="hm-sidebar-session__header">
      <div class="hm-sidebar-session__header__img">
        <default-image class="hm-sidebar-session__img-default" />
        <div class="hm-sidebar-session__header__img-info">
          <span>{{ title | truncate(100)}}</span>
        </div>
      </div>
      <div class="hm-sidebar-session__header__image-edit">
        <hm-sidebar-button-edit v-if="editUrl" :link="editUrl" />
      </div>
    </div>

    <div class="hm-sidebar-session__session-process-container">
      <div class="hm-sidebar-session__session-process">
        <slot></slot>
      </div>
      <div class="hm-sidebar-session__session-process-title">
        <icon-title />
        <h3>Отчеты</h3>
      </div>
      <div class="hm-sidebar-session__report-links">
        <a
          class="hm-sidebar-session__report-link"
          v-for="(link, index) in reportLinks"
          :key="index"
          :href="link.url"
        >
          {{ link.title }}
        </a>
      </div>
    </div>

  </div>
</template>
<script>
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
import DefaultImage from './default-image';
import IconTitle from './icon-title';
export default {
  props: ['image', 'title', 'editUrl', 'reportprocess', 'reportLinks'],
  components: {
    HmSidebarButtonEdit,
    DefaultImage,
    IconTitle
  },
  data() {
    return {
      activeStep: null
    }
  }
}
</script>
<style lang="scss">
.hm-sidebar-session {
  display: flex;
  flex-direction: column;
  width: 100%;

  &__header {
    width: 100%;
    height: 164px;
    position: relative;
    &__img {
      width: 100%;
      height: 164px;
      background: #a8c7ec;
      position: relative;
      display: flex;
      justify-content: center;
      > svg {
        margin-top: 32px;
        width: 100px;
      }

      &-edit {
        position: absolute;
        bottom: -24px;
        right: 52px;
        z-index: 100;
      }

      &-info {
        width: 100%;
        height: 70px;
        position: absolute;
        bottom: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(30, 30, 30, 0.5);
        > span {
          margin-top: 4px;
          font-size: 16px;
          line-height: 20px;
          letter-spacing: 0.02em;
          color: #ffffff;
          text-align: center;
        }
      }
    }
    &__image {
      width: 100%;
      height: 164px;
      background-position: center;
      background-size: cover;
      background-repeat: no-repeat;
      position: relative;
      display: flex;
      justify-content: center;

      &-edit {
        position: absolute;
        bottom: -24px;
        right: 52px;
        z-index: 100;
      }

      &-info {
        width: 100%;
        height: 76px;
        position: absolute;
        bottom: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(30, 30, 30, 0.5);
        > span {
          margin-top: 4px;
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #ffffff;
          text-align: center;
        }
      }
    }
  }

  &__session-process-container {
    display: flex;
    flex-direction: column;
    width: 100%;
    padding: 24px 16px;
  }
  &__session-process-title {
    display: flex;
    align-items: center;
    width: 100%;
    font-style: normal;
    font-weight: 500;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #1E1E1E;

    & h3 {
      margin-left: 12px;
      margin-top: 20px;
      margin-bottom: 16px;
    }
  }
  &__session-process {
    display: flex;
    flex-direction: column;
    margin-top: 8px;
  }
  &__session-link {
    font-style: normal;
    font-weight: 400;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #5181B8;
    margin-top: 8px;
    text-decoration: none;
    &:hover {
      color: #2574CF;
    }
  }
  & .hm-workflow-full {

    & .hm-workflow-card {
      & .hm-workflow_steps,
      & .hm-workflow-state_items {
        background-color: inherit !important;
      }
      & .hm-workflow_step {
        & .v-stepper__step {
          padding: 12px 16px !important;
        }
        & .hm-workflow-state_tabs {
          display: none;
        }
        & .v-stepper__content {
          padding: 0;
          margin: 0;
          padding-left: 52px;
          padding-right: 16px;
          padding-top: 5px;
          font-style: normal;
          font-weight: 400;
          font-size: 12px;
          line-height: 18px;
          letter-spacing: 0.15px;
          color: #3E4E6C;

          & p {
            margin-bottom: 0 !important;
          }
        }
      }
      & .v-stepper--vertical {
        padding: 0;
      }
      & .v-card__text {
        padding-bottom: 0px;
      }
    }
  }
  &__report-links {
    display: flex;
    flex-direction: column;
  }
  &__report-link {
    text-decoration: none;
    font-weight: 400;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #5181B8;
    margin-bottom: 8px;
  }
}
</style>
