<template>
  <transition name="fade">
    <v-flex v-if="!isDelete" xs12 sm6 md3 pl-2 pr-2 class="material-card">
      <v-card :href="url ? url : null">
        <div class="material-card_resource primary">
          <slot name="resource" />
        </div>
        <v-card-text>
          <div class="material-card_header">
            <h3 class="title mb-2">
              <span v-text="title"></span>
              <hm-rating :rating="rating"></hm-rating>
            </h3>
            <v-btn v-if="statsUrl" :href="statsUrl" icon text color="info">
              <v-icon>sort</v-icon>
            </v-btn>
          </div>
          <div v-if="description" class="material-card_description">
            {{ description | truncate(100) }}
          </div>
          <v-list
            v-if="classifiers && Object.keys(classifiers).length > 0"
            class="material-card_classifiers"
            two-line
          >
            <v-list-item
              v-for="(classifier, key) in classifiers"
              :key="key"
              class="caption"
            >
              <v-list-item-avatar
                ><v-icon color="primary">label</v-icon></v-list-item-avatar
              >
              <v-list-item-content>
                <span class="caption" :title="classifier">{{
                  classifier | truncate(40)
                }}</span>
              </v-list-item-content>
            </v-list-item>
          </v-list>
          <div v-if="tags && tags.length > 0" class="material-card_chips">
            <v-chip v-for="(tag, key) in tags" :key="key">{{ tag }}</v-chip>
          </div>
        </v-card-text>

        <v-spacer></v-spacer>
        <template v-if="actions.edit || actions.delete">
          <v-divider light />
          <v-card-actions>
            <v-spacer />
            <v-tooltip v-if="actions.edit" bottom>
              <v-btn
                slot="activator"
                :href="actions.edit"
                icon
                text
                color="orange"
              >
                <v-icon>edit</v-icon>
              </v-btn>
              <span>Редактировать</span>
            </v-tooltip>
            <v-tooltip v-if="actions.delete" bottom>
              <v-btn
                slot="activator"
                icon
                text
                color="error"
                @click.native.stop.prevent="del(actions.delete)"
              >
                <v-icon>delete</v-icon>
              </v-btn>
              <span>Удалить</span>
            </v-tooltip>
          </v-card-actions>
        </template>
      </v-card>
    </v-flex>
  </transition>
</template>
<script>
import { mapActions } from "vuex";
import HmRating from "@/components/els/hm-rating";
export default {
  components: { HmRating },
  props: {
    url: {
      type: String,
      default: null
    },
    iconClass: {
      type: String,
      default: null
    },
    title: {
      type: String,
      default: null
    },
    statsUrl: {
      type: String,
      default: null
    },
    serverUrl: {
      type: String,
      default: null
    },
    description: {
      type: String,
      default: null
    },
    actions: {
      type: Object,
      default: () => {}
    },
    rating: {
      type: Number,
      default: null
    },
    classifiers: {
      type: Object,
      default: () => {}
    },
    tags: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      isDelete: false
    };
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert", "addSuccessAlert"]),
    del(deleteUrl) {
      this.$confirmModal({ text: "Вы уверены, что хотите удалить материал?" })
        .then(() => {
          this.$axios
            .delete(deleteUrl)
            .then(r => {
              if (r.status === 200) {
                this.isDelete = true;
                this.addSuccessAlert("Материал успешно удален!");
              }
            })
            .catch(() =>
              this.addErrorAlert("Произошла ошибка! Материал не удален.")
            );
        })
        .catch(() => console.log("Не удалять материал!"));
    }
  }
};
</script>

<style lang="scss">
.material-card {
  margin-bottom: 15px;
  &_classifiers {
    .v-list__tile {
      padding: 0;
    }
    .v-list__tile__avatar {
      min-width: 25px;
    }
    .v-avatar {
      width: 20px !important;
      height: 20px !important;
      i {
        font-size: 17px;
      }
    }
  }
  .v-card {
    height: 100%;
    transition: opacity 0.3s;
    opacity: 0.9;
    display: flex;
    flex-direction: column;
    &:hover {
      opacity: 1;
    }
    h3 {
      font-size: 16px;
      line-height: 22px;
      font-weight: normal;
    }
  }
  .material-card_header {
    display: flex;
    justify-content: space-between;
    flex: 1 0 auto;
    align-items: center;
    max-width: 100%;
    a {
      margin: 0;
      height: auto;
      align-self: flex-start;
      &:before {
        height: 36px;
        top: -5px;
      }
    }
  }
}
.material-card_resource {
  height: 100px;
  background: rgba(blue, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  .material-card_btn {
    width: 56px;
    height: 56px;
    opacity: 1;
    transition: opacity 0.3s;
    i {
      font-size: 40px;
    }
    &:hover {
      opacity: 0.9;
    }
  }
  .icon-resource {
    color: #fff;
  }
}
</style>
