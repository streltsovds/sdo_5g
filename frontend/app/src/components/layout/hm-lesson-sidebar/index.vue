<template>
  <div class="lesson-sidebar">
    <v-container fluid class="lesson-sidebar__lesson-icon">
      <v-layout>
        <slot></slot>
        <v-tooltip left>
          <template v-slot:activator="{ on }">
            <a :href="editLink"
              ><v-icon medium color="white" v-on="on">edit</v-icon></a
            >
          </template>
          <span>Редактировать занятие</span>
        </v-tooltip>
      </v-layout>
    </v-container>
    <v-container class="lesson-sidebar__lesson-documents">
      <v-layout>
        <v-list subheader class="documents-block">
          <v-subheader class="documents-block__header"
            ><h2>Документы</h2></v-subheader
          >
          <v-list-item
            v-for="document in documents"
            :key="document.title"
            color="primary"
            class="documents-block__document"
          >
            <v-list-item-content>
              <v-list-item-title
                ><a :href="document.link">{{
                  document.title
                }}</a></v-list-item-title
              >
              <v-list-item-subtitle>{{
                document.subTitle
              }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-layout>
    </v-container>
  </div>
</template>

<script>
export default {
  name: "Index",
  props: {
    attendanceLink: {
      type: String,
      default: ""
    },
    protocolLink: {
      type: String,
      default: ""
    },
    editLink: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      documents: [
        {
          title: "Журнал учёта посещаемости и успеваемости",
          subTitle: "Скачать .docx файл с журналом учёта",
          link: this.$props.attendanceLink
        },
        {
          title: "Итоговый протокол",
          subTitle: "Скачать .docx файл с итоговым протоколом",
          link: this.$props.protocolLink
        }
      ]
    };
  }
};
</script>

<style scoped lang="scss">
.lesson-sidebar {
  &__lesson-icon {
    padding: 0;
    position: relative;
    i {
      padding: 8px;
      border: 3px solid white;
      border-radius: 50%;
      background-color: #1976d2;
      position: absolute;
      bottom: -20px;
      right: 25px;
    }
  }
  &__lesson-documents {
    height: 100%;
    .documents-block {
      &__document {
        padding-bottom: 20px;
      }
      &__header {
        padding-bottom: 20px;
      }
    }
  }
}
</style>
