<template>
<v-container>
    <div style='display: flex; width:100%; flex-direction:row; flex-wrap:wrap; justify-content:space-between; align-items:stretch; align-content:stretch'>

        <v-card  style='margin-bottom:20px; text-align:left; width:300px;' v-for="idea in ideas" :key="idea.idea_id">
          <v-img class="white--text" height="150px" :src="idea.image">
            <v-container fill-height fluid>
              <v-layout fill-height>
                <v-flex xs12 align-end flexbox>
                </v-flex>
              </v-layout>
            </v-container>
          </v-img>
          <v-card-title>
        <v-item-group multiple>
          <v-item v-for="tag in idea.tags.concat(idea.classifiers)" :key="tag">
            <v-chip slot-scope="{ active, toggle }" :selected="active">
              #{{ tag }}
            </v-chip>
          </v-item>
        </v-item-group>
            <div>
              <span class="headline">{{idea.name}}</span>
            </div>
          </v-card-title>
          <v-card-actions>
            <div style='width:100%; display: flex; align-items:center; justify-content:space-between'>
                <v-btn dark color="#5181B8">Читать далее</v-btn>

                <div style='display: flex; align-items:center'>
                    <div class="score">{{idea.likes_down}}</div>
                    <svg-icon style='margin-top:6px;margin-left:2px' name="like-down" color="#70889E"></svg-icon> 
                                        
                    <div class="score" style='margin-left:10px;color:#FF9800'>{{idea.likes_up}}</div>
                    <svg-icon style='margin-bottom:6px;margin-left:2px' name="like-up" color="#70889E"></svg-icon> 
                </div>
            </div>
          </v-card-actions>
        </v-card>

    </div>



    <v-fab-transition>
        <v-btn color="#FF9800" fab dark fixed bottom right>
            <v-icon>add</v-icon>
        </v-btn>
    </v-fab-transition>

</v-container>

</template>


<script>
import storage from "../services/storage";

import svgIcon from '../../src/components/icons/svgIcon'

export default {
  name: "IdeaPage",
  components: {
    svgIcon,
  },
  data() {
    return {
      ideas: {},
      location: {}
    };
  },
  created() {
        var account = storage['account'];
        var user = storage['user'];

        fetch(account.host+'/mobile/idea/json-list', {
          method: 'post',  
          headers: {'client-security-token':user.security_token},
        })
        .then(response => response.json())
        .then(data => {
            for(let i in data) {
                data[i].chpis = [];
                data[i].chpis = data[i].chpis.concat(data[i].tags, data[i].classifiers);
                data[i].image = account.host + data[i].image;
            }
            storage['ideas'] = this.ideas = data;
        });
  },
  methods: {
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.score {
    font-size:11pt;
    font-weight:bold;
    color:#70889E;
}
</style>
