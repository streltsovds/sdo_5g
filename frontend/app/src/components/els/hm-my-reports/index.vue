<template>
  <v-card class="hm-my-reports">
    <HmGridNoData v-if="!totalReports" subLabel=""/>
    <ul v-if="!!reports.length">
      <li class="hm-my-reports__reports-group" v-for="(reportGroup, index) in reports" :key="index">
        <div v-if="reportGroup.reports.length" class="hm-my-reports__reports-group-body">
          <p class="hm-my-reports__reports-group-title">
            {{ reportGroup.title }}
          </p>
          <ul class="hm-my-reports__reports">
            <li class="hm-my-reports__report" v-for="report in reportGroup.reports" :key="report.id">
              <a class="hm-my-reports__report-body"
                 :href="report.url"
                 target="_blank"
                 rel="noopener noreferrer"
              >
                <div class="hm-my-reports__report-icon">
                  <img src="/images/icons/report.svg" alt="Отчет">
                </div>
                <p class="hm-my-reports__report-title">{{ report.title }}</p>
              </a>
            </li>
          </ul>
        </div>
      </li>
    </ul>
  </v-card>
</template>
<script>
import SvgIcon from "@/components/icons/svgIcon";
import HmGridNoData from "@/components/hm-grid/components/HmGridNoData";
export default {
  components:{
    SvgIcon,
    HmGridNoData
  },
  props: {
    reportsData:{
      type: Array,
      default: () => []
    }
  },
  computed: {
    totalReports() {
      let total = 0;

      this.reportsData.forEach(function (element) {
        total+= element.reports.length;
      });

      return total;
    }
  },
  data() {
    return {
      reports: this.reportsData,
    };
  },
};

</script>
<style lang="scss">
.hm-my-reports{
    padding: 10px;
    &__reports-group{
        list-style-type: none;
        margin-top: 20px;
        margin-bottom: 40px;
    }
    &__reports-group-title{
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 10px!important;
        margin-top: 10px;
        letter-spacing: 0.02em;
    }
    &__report-icon{
        margin-right: 10px;
        display: flex;
        img{
          width: 24px;
        }
    }
    &__report-body{
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #3E4E6C;
    }
    &__report-title{
        margin-bottom: 0!important;
        font-size: 14px;
        letter-spacing: 0.02em;
    }
    &__report{
        list-style-type: none;
        padding: 15px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.12);
    }
    &__reports{
        padding-left: 0!important;
    }
}

</style>