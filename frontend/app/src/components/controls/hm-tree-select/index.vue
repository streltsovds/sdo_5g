<template>
  <v-menu
      :close-on-content-click="false"
      content-class="select-treeview"
      eager
      min-width="300px"
      nudge-bottom="2"
      offset-y
      v-model="statusMenu"
  >
      <template v-slot:activator="{ on: onMenuActivate }">
          <v-btn
              class="select-treeview__button"
              :class="{'select-treeview__button_selected': activeItemOrgstructure.length > 0}"
              v-on="{...onMenuActivate}"
              elevation="0"
              outlined
          >
            <svg-icon
              name="org-structure"
              :color="activeItemOrgstructure.length > 0 ? 'rgb(74, 144, 226)' : '#1E1E1E'"
              width="16"
              height="16"
              style="margin-right: 6px;"
            ></svg-icon>
            {{activeItemOrgstructure.length > 0 ? activeItemOrgstructure[0].title : "Выбор подразделения"}}
          </v-btn>
      </template>
      <div class="select-treeview__popup">
          <v-treeview
              @update:active="changeOrgstructureFilter"
              @input="changeOrgstructureFilter"
              :active.sync="activeItemOrgstructure"
              class="select-treeview__list"
              :items="itemsOrgstructure"
              :open.sync="open"
              item-text="title"
              item-key="key"
              :load-children="fetch"
              open-all
              return-object
              activatable
          ></v-treeview>
      </div>
  </v-menu>
</template>
<script>
import svgIcon from "@/components/icons/svgIcon";
export default {
    components: {svgIcon},
    props: ['orgstructureFilterData', 'initObject'],
    data() {
        return {
            statusMenu: false,
            itemsOrgstructure: [],
            open: [],
            activeItemOrgstructure: this.initObject && this.initObject.key ? [this.initObject] : [],
            indexedItems: [],
        }
    },
    created() {
        this.itemsOrgstructure = this.formatData(this.orgstructureFilterData);
        this.indexedItems = this.indexItems(this.orgstructureFilterData);
    },
    methods: {
        changeOrgstructureFilter(val) {
          if(val.length > 0) {
            this.$emit("changeDepartment", val[0].key)
          } else {
            this.$emit("changeDepartment", null)
          }
          this.statusMenu = false;
        },
        formatData(items, parentKey = null) {
            let formattedData = [];
            items.forEach(item => {
                if (!Array.isArray(item)) {
                // item is parent
                let formattedItem = { ...item };

                if (parentKey) {
                    formattedItem.parentKey = parentKey
                }

                // TODO переделать на isLazy. +можем ли заранее узнать, есть ли вложенные элементы?
                if (item.isFolder) {
                    // if (item.isLazy) {
                    formattedItem["children"] = [];
                }
                formattedData.push(formattedItem);

                // if (item.expand) this.open.push(item.key);
                if (item.expand) {
                    this.open.push(formattedItem);
                }
                } else if (item.length > 0) {
                // item is children
                let parentItem = formattedData[formattedData.length - 1];
                parentItem["children"] = this.formatData(item, parentItem.key);
                }
            });

            return formattedData;
        },
        fetch(item) {
            return fetch(`/orgstructure/list/get-tree-branch?key=${item.key}`, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    },
                })
                .then(response => response.json())
                .then(response => {
                    if (!response)
                        throw new Error(
                            "Wrong response for fetch method in hm-rubricator component"
                        );
                    return response;
                })
                .then(children => {
                    if (children.length == 0) {
                        // item.isFolder = false;

                        /** скрываем иконку разворачивания */
                        this.$delete(item, "children");
                        item.expand = false;
                        return;
                    }

                    this.$set(item, "children", this.formatData(children, item.key));
                    let childrenIndexed = this.indexItems(item["children"])

                    /** На случай, когда в начальных данных не оказалось выделенного элемента, а сейчас он пришёл */
                    // if (
                    //   this.elementWithUnknownParent &&
                    //   childrenIndexed[this.elementWithUnknownParent.key]
                    // ) {
                    //   this.$delete(this.items[0].children, this.elementWithUnknownParent.key)
                    // }

                    /** для реактивности создаём новый объект */
                    this.indexedItems = Object.assign({}, this.indexedItems, childrenIndexed)
                })
                .catch(error => {
                    console.error(error);
                });
        },
        indexItems(items) {
            let result = {}

            items.forEach(item => {
                let itemCopy = {...item}
                delete itemCopy.children
                let key = item.key
                result[key] = itemCopy

                if (item.children) {
                let childrenResult = this.indexItems(item.children)
                Object.assign(result, childrenResult)
                }
            });

            return result;
        }
    }
}
</script>
<style lang="scss">
  .select-treeview {
      &__button {
          & .v-btn__content {
              color: rgba(0, 0, 0, 0.87) !important;
              font-style: normal !important;
              font-weight: normal !important;
              font-size: 14px !important;
              line-height: 21px !important;
              text-transform: none;
              letter-spacing: normal;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
              display: inline;
              width: 100%;
          }
          width: 100%;
          max-width: 230px !important;
          height: min-content !important;
          // margin-right: 10px !important;
          // margin-bottom: 19px;
          border-color: #B9C3CB !important;
          padding: 8px 24px !important;
          &_selected {
            & .v-btn__content {
              color: rgb(74, 144, 226) !important;
            }
            background-color: rgba(74, 144, 226, 0.3);
            border-color: rgba(185, 195, 210, 0.3) !important;
          }
      }
      &__popup {
          background-color: #fff;
          max-height: 300px;
          max-width: 550px;
          overflow: auto;
          .v-treeview-node {
              &__root {
                  cursor: pointer;
              }
          }
      }
  }
</style>
