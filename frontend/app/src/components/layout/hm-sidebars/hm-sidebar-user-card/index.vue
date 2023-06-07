<template>
  <div id="sidebarUserCard">
    <div class="sidebar-user-card">
      <div class="sidebar-user-card__image">
        <div
          class="sidebar-user-card__image-icon"
          :style="{
            backgroundImage: `url(${
              dataSidebar.photo && dataSidebar.photo !== '' ? dataSidebar.photo: ''})`,}">
          <svg-icon
            v-if="!dataSidebar.photo || dataSidebar.photo === ''"
            style="width: 160px; height: 160px"
            name="user-course"
            color="#4A90E2"
          />
        </div>
        <div v-if="dataSidebar.editUrl" class="sidebar-user-card__image-edit">
          <hm-sidebar-button-edit :link="dataSidebar.editUrl"/>
        </div>
      </div>
      <div class="sidebar-user-card__info">
        <div class="sidebar-user-card__info-FIO">
          <span>{{ dataSidebar.FIOUser }}</span>
        </div>
        <div class="sidebar-user-card__info-actions">
          <div
            v-for="(item, i) in dataSidebar.actions"
            :key="i"
            class="sidebar-user-card__info-actions-action">
            <a :href="item.url">
              <span>{{ item.title }}</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
export default {
  components: {HmSidebarButtonEdit, SvgIcon },
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {
        return {
            // http://5g.loc/upload/photo/27/21685.jpg?_=
          photo: "",
          editUrl: "//adasdasd",
          FIOUser: "Имя Фамилия Отчество",
          actions: [
            {
              url: "1121212//213123/45asd.asd",
              title: "войти от имени пользователя",
            },
            {
              url: "1121212//213123/45asd.asd",
              title: "Назначить область ответственности",
            },
          ],
        };
      },
    },
  },
  mounted() {
    console.log("зашли сюды");
    console.log(this.dataSidebar);
  },
};
</script>

<style lang="scss">
#sidebarUserCard {
  width: 100%;
  height: 100%;
  .sidebar-user-card {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    &__image {
      width: 100%;
      height: 210px;
      position: relative;
      display: flex;
      &::before {
        content: "";
        width: 100%;
        height: 60%;
        background: #d4e3fb;
      }
      &-icon {
        position: absolute;
        width: 160px;
        height: 160px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 50%;
        left: calc(50% - 80px);
        top: calc(60% - 80px);
      }
      &-edit {
        position: absolute;
        right: 16px;
        bottom: 10px;
      }
    }

    &__info {
      width: 100%;
      padding: 0 16px;
      margin-top: 26px;
      display: flex;
      flex-direction: column;
      &-FIO {
        width: 100%;
        height: auto;
        > span {
          font-weight: 500;
          font-size: 20px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1e1e1e;
        }
      }
      &-actions {
        width: 100%;
        height: auto;
        display: flex;
        flex-direction: column;
        margin-top: 26px;
        &-action {
          width: 100%;
          height: 36px;
          border: 1px solid #4a90e2;
          border-radius: 4px;
          > a {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            cursor: pointer;
            > span {
              font-weight: 500;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #1e1e1e;
            }
          }
        }
        &-action:not(:last-child) {
          margin-bottom: 26px;
        }
      }
    }
  }
}
</style>
