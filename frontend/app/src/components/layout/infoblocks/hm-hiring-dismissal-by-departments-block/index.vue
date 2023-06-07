<template>
  <div class="INFOBLOCK infoblock-hiring-dismissal-by-departments-block">
    <v-card-text class="infoblock-hiring-dismissal-by-departments-block_filter pb-0">
      <v-layout wrap>
        <v-flex xs12 sm6>
          <hm-date-picker
            :label="datePickerFromLabel"
            :disabled="isLoading"
            :loading="isLoading"
            :value="datePickerFromValue"
            name="infoblock-hiring-dismissal-by-departments-block_from"
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
            name="infoblock-hiring-dismissal-by-departments-block_to"
            @input="
              endDate = $event;
              loadData();
            "
          ></hm-date-picker>
        </v-flex>
      </v-layout>
    </v-card-text>
    <v-flex sm12 md12 class="infoblock-hiring-dismissal-by-departments-block_chart-container">
      <hm-chart
        :data="chartData"
        type="apexbar"
        :options="chartOptions"
      />
    </v-flex>
  </div>
</template>
<script>
import HmDatePicker from "../../../forms/hm-date-picker/index";
import HmChart from "@/components/media/hm-chart/index";
import { mapActions } from "vuex";
export default {
  components: { HmDatePicker, HmChart },
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
    parentDepartments: {
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
      beginDate: this.datePickerFromValue,
      endDate: this.datePickerToValue,
      datas: this.parentDepartments,
      chartData: [],
      chartOptions: {
        dataLabel: "title",
        height: 300,
        width: 550,
        type: "apexbar",
        horizontal: true
      }
    };
  },
  mounted() {
    this.loadData();
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
          this.chartData = r.data;
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
  .infoblock-hiring-dismissal-by-departments-block {
    .apexcharts-toolbar {
      display: none;
    }
    .apexcharts-yaxistooltip {
      display: none;
    }
  }
  .infoblock-hiring-dismissal-by-departments-block_filter {
    .hm-form-element {
      margin: 0 10px 0 0;
    }
  }
</style>
