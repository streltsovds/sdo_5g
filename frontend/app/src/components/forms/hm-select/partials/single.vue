<template>
  <v-select
    class="hm-select-single"
    v-model="activeItem"
    :items="itemsFormatted"
    :label="label"
    :class="{ required }"
    :hint="description"
    :multiple="multiple"
    :disabled="disabled"
    persistent-hint
    @change="update"
  ></v-select>
</template>
<script>
import { mapMutations } from 'vuex';
export default {
  props: {
    items: {
      type: [Array, Object],
      default: () => {}
    },
    label: {
      type: String,
      default: null
    },
    description: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    multiple: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    selected: {
      type: String,
      default: null
    },
    name: {
      type: String,
      default: null
    },
    refresh: {
      type: Object,
      default: () => {}
    },
    keyList: Array
  },
  data() {
    return {
      active: this.selected,
      elements: this.items || {}
    };
  },
  computed: {
    itemsFormatted() {
      if (!this.elements) return [];
      let items = [];
      if(this.keyList) {
          this.keyList.forEach(el=> {
              for(let key in this.elements) {
                  if(el == key && this.elements.hasOwnProperty(key)) {
                      items.push({
                          value: key,
                          text: this.elements[key]
                      });
                  }
              }
          })
      } else {
          for (let key in this.elements) {
              if (this.elements.hasOwnProperty(key)) {
                  items.push({
                      value: key,
                      text: this.elements[key]
                  });
              }
          }
      }
      return items;
    },
    activeItem: {
      get() {
        if(Array.isArray(this.selected)) {
          const items = [];
          this.selected.forEach(selectItem => {
            this.itemsFormatted.forEach(item => {
              if(item.value === selectItem) items.push(item);
            });
          });
          return items;
        }
        else return this.itemsFormatted.find((item) => item.value === this.selected);
      },
      set(val) {
        return this.active = val;
      },
    },
  },
  methods: {
    ...mapMutations(['proctoring/SET_TYPE_MATERIAL']),
    update(value) {
      if(this.name === 'material_type') this['proctoring/SET_TYPE_MATERIAL'](value);
      if(this.refresh && this.refresh.enabled === true) {
        const result = confirm(this.refresh.description);
        if(result) {
          const name = `${this.name}`;
          const param = `${name}/${value}/`;

          const getNewURL = () => {

            let url = location.href.split('?')[0];
            let arrUrl = url.split('/');
            let paramIndex = arrUrl.indexOf(this.name);$

            let newURL;

            if(paramIndex > 0) {
              arrUrl[paramIndex + 1] = value;
              newURL = arrUrl.join('/');
            } else {
              if (url.slice(-1) === "/") {
                newURL = `${url}${param}`;
              } else {
                newURL = `${url}/${param}`;
              }
            }
            return newURL;
          };

          location.href = getNewURL();
          this.active = value;
          return this.$emit("update", value);
        } else {
          this.$nextTick(() => this.active = this.selected);
        }
      } else {
        return this.$emit("update", value);
      }
    }
  }
};
</script>
