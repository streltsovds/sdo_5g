<template>
<hm-kbase/>
</template>


<script>

import HmKbase from "../../src/components/kbase";

//import storage from "../services/storage";

export default {
  name: "KbasePage",
  components: { HmKbase
  },
  data() {
    return {
      view: {},
    };
  },
  created() {
/*
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
*/

  },
  methods: {
  },
  computed: {
  }
};
</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
