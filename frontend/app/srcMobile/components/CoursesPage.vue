<template>
<hm-my-subjects :subjects-data="view.subjectUsers" />
</template>


<script>
import hmMySubjects from "../../src/components/hm-my-subjects";
import HmSwitchCheckmark from "../../src/components/form-components/hm-switch-checkmark";

import storage from "../services/storage";

export default {
  name: "CoursesPage",
  components: { hmMySubjects, HmSwitchCheckmark
  },
  data() {
    return {
      view: {},
    };
  },
  created() {
        var account = storage['account'];
        var user = storage['user'];

        this.view = storage['courses'];

        fetch(account.host+'/subject/my/index/ajax/1', {
          method: 'post',
          headers: {'client-security-token':user.security_token},
        })
        .then(response => response.json())
        .then(data => {
            storage['courses'] = this.view = data;
console.log(data);
        });


  },
  methods: {
    appMethodSort(arr, by) {
return arr;
console.log(arr);
return {};


      // .sort меняет исходный массив. Чтобы не было бесконечного перезапуска рендеринга, копируем
      let arrCopy = JSON.parse(JSON.stringify(arr));

      let sorted = arrCopy.sort((a,b)=>{return a[by]>b[by]?1:(a[by]<b[by]?-1:0)});
      return sorted;
    },
  },
  computed: {
  }
};
</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
