<template>
  <v-app style="margin-top: 0;">
  <!--v-navigation-drawer app>
  </v-navigation-drawer-->
  <v-app-bar v-if="$route.name!='AuthPage'" app>
      <v-app-bar-nav-icon></v-app-bar-nav-icon>
      <v-toolbar-title>{{ $route.meta.title }}</v-toolbar-title>
  </v-app-bar>
  <!-- Sizes your content based upon application components -->
  <v-main style="height:50vh; margin-top:56px"> 
    <!-- Provides the application the proper gutter -->
    <v-container fluid>
      <!-- If using vue-router -->
      <router-view></router-view>
    </v-container>
  </v-main>
  <v-bottom-navigation color='#3E4E6C' v-if="$route.name!='AuthPage'" v-model="mainMenu">
      <v-btn value="/">
        <span>Главная</span>
        <svg-icon name="main" color="#3E4E6C"></svg-icon>
      </v-btn>
      <v-btn value="/courses-page">
        <span>Мои курсы</span>
        <svg-icon name="learning" color="#3E4E6C"></svg-icon>
      </v-btn>
      <v-btn value="/kbase-page">
        <span>База знаний</span>
        <svg-icon name="KnowledgeBase" color="#3E4E6C"></svg-icon>
      </v-btn>
  </v-bottom-navigation>

  <!--v-footer app>
  </v-footer-->
  </v-app>
</template>

<script>
import svgIcon from '../src/components/icons/svgIcon'

export default {
  name: "MobileApp",
  components: {
    svgIcon,
  },
  data() {
    return {
      todos: [],
      mainMenu: '/'
    };
  },
  created() {
//    setTimeout(() => this.getDataFromAPI(), 1000);
  },
  methods: {
  },
  watch: {
    mainMenu(route) {
        this.$router.replace(route);
    }
  }
};

/*
    async getDataFromAPI() {
      try {
        const req = await fetch("https://jsonplaceholder.typicode.com/todos");
        this.todos = await req.json();

        let testText = "";

        if (window.device) {
          testText = window.device.model + " " + window.device.uuid;
        }
        // TODO document.addEventListener("deviceready", onDeviceReady, false);

        this.todos[0] = {
          id: 1,
          title: "Testing native plugin: " + testText,
        };
      } catch (e) {
        window.console.error(`Error: ${e}`);
      }
    },
*/
</script>

<style>
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}

.v-bottom-navigation .svg-icon {
    margin-bottom:4px;
}

.v-btn--active svg path {
    fill:#4A90E2;
}
.v-btn--active span {
    color:#4A90E2;
}


.v-main { overflow:auto
}

.v-main__wrap { /*overflow:auto*/
width:100%;
}


</style>
