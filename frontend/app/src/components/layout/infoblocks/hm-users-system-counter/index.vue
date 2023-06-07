<template>
  <div class="INFOBLOCK infoblock-users-system-counter">
    <v-card-text class="infoblock-users-system-counter_filter pb-0">
      <v-layout wrap>
        <v-flex xs12 sm6>
          <hm-date-picker
            :label="datePickerFromLabel"
            :disabled="isLoading"
            :loading="isLoading"
            :value="datePickerFromValue"
            name="infoblock-users-system-counter_from"
            @input="
              beginDate = $event;
              loadData();
            "
          ></hm-date-picker>
        </v-flex>
        <v-flex xs12 sm6>
          <hm-date-picker
            :label="datePickerToLabel"
            :disabled="isLoading"
            :loading="isLoading"
            :value="datePickerToValue"
            name="infoblock-users-system-counter_to"
            @input="
              endDate = $event;
              loadData();
            "
          ></hm-date-picker>
        </v-flex>
      </v-layout>
    </v-card-text>
    <div class="infoblock-users-system-counter_list">
      <v-list>
        <v-list-item v-for="item in datas" :key="item.name">
          <v-list-item-content>
            <v-list-item-title class="title secondary--text"
              >{{ item.name }} {{ item.value }}</v-list-item-title
            >
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </div>
  </div>
</template>
<script>
import HmDatePicker from "../../../forms/hm-date-picker/index";
import { mapActions } from "vuex";
export default {
  components: { HmDatePicker },
  props: {
    datePickerFromLabel: {
      type: String,
      default: "За период c"
    },
    datePickerFromValue: {
      type: String,
      default: null
    },
    datePickerToLabel: {
      type: String,
      default: "по"
    },
    datePickerToValue: {
      type: String,
      default: null
    },
    items: {
      type: Array,
      default: () => []
    },
    url: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      isLoading: false,
      beginDate: null,
      endDate: null,
      datas: this.items
    };
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    loadData() {
      if (!this.beginDate || !this.endDate) return;
      if (this.isLoading) return;
      this.isLoading = true;
      const data = new FormData();
      data.set("from", this.beginDate);
      data.set("to", this.endDate);

      this.$axios
        .post(this.url, data)
        .then(r => {
          if (r.status !== 200 || !r.data) throw new Error();
          this.datas = r.data;
        })
        .catch(e => {
          this.addErrorAlert("Произашла ошибка при загрузке данных!");
          console.error(e);
        })
        .finally(() => (this.isLoading = false));
    }
  }
};
</script>
<style lang="scss">
  .infoblock-users-system-counter_filter {
    .hm-form-element {
      margin: 0 10px 0 0;
    }
  }
</style>
