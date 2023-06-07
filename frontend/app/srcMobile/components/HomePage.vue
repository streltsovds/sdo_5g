<template>
<v-container style='height: 100%; padding:0px'>
HOME!!!
<a @click="Download">DOWNLOAD</a>
<a @click="Download">DOWNLOAD</a>
<a @click="Download">DOWNLOAD</a>
<a @click="Download">DOWNLOAD</a>
<a @click="Download">DOWNLOAD</a>

<iframe id='frame' style='height: 100%;width: 100%; -webkit-overflow-scrolling: touch;' frameborder="0"  
src='http://127.0.0.1:8888/course3/index.htm'></iframe>

</v-container>
</template>


<script>
import storage from "../services/storage";

//import { Plugins, GeolocationOptions } from "@capacitor/core";
//const { Geolocation } = Plugins;

export default {
  name: "HomePage",
  data() {
    return {
      msg: "Welcome to Your Vue.js App",
      location: {}
    };
  },

  created() {
      var user = storage['user'];
      if(user.security_token==undefined) {
        this.$router.replace('/auth-page');
      } else {
//        this.$router.replace('/home-page');//temp!!!

        var httpd = ( cordova && cordova.plugins && cordova.plugins.CorHttpd ) ? cordova.plugins.CorHttpd : null;
        var root = cordova.file.dataDirectory.substr(7) + 'courses';//cordova.file.dataDirectory + 'courses/course88/';
        httpd.startServer({
    	    	    	'www_root' : root, //'/storage/emulated/0/Android/data/com.aks.vuehw/courses',
    	    	    	'port' : 8888,
    	    	    	'localhost_only' : true,//false
    	    	    }, function( url ){alert(url);}, function( err ){alert(err);}
        );

setTimeout(function() {document.getElementById('frame').src = "http://127.0.0.1:8888"}, 10000);



      }
  },

  methods: {
    
    Download(){

      var fileURL = cordova.file.dataDirectory+'course3.zip';
      var uri = encodeURI("http://danone/course3.zip");
      var fileTransfer = new FileTransfer();

    fileTransfer.download(uri, fileURL, win, fail, false, {});

      function win(r) {
alert(cordova.file.dataDirectory);

          resolveLocalFileSystemURL(cordova.file.dataDirectory, (rootDirEntry)=>{

          rootDirEntry.getDirectory('courses', { create: true }, function (dirEntry) {


zip.unzip(fileURL, dirEntry.nativeURL);

//                  dirEntry.getDirectory('course199', { create: true }, function (subDirEntry) {

                      /*subDirEntry*/dirEntry.getFile('index.html', {create: true, exclusive: false}, function(fileEntry) {
                          writeFile(fileEntry, null);//, isAppend);
                          }, onErrorGetDir);

          //            createFile(subDirEntry, "fileInNewSubDir.txt");

//                  }, onErrorGetDir);
              }, onErrorGetDir);
          });

          alert("Code = " + r.responseCode);
          console.log("Response = " + r.response);
          console.log("Sent = " + r.bytesSent);
      }


      function onErrorGetDir(e)
      {
        alert(e);
        console.log(e);
      }
      function writeFile(fileEntry, dataObj) {
        // Create a FileWriter object for our FileEntry (log.txt).
        fileEntry.createWriter(function (fileWriter) {

            fileWriter.onerror = function (e) {
                console.log("Failed file write: " + e.toString());
            };

            // If data object is not passed in,
            // create a new Blob instead.
            if (!dataObj) {
                dataObj = new Blob(['some file data'], { type: 'text/plain' });
            }

            fileWriter.write(dataObj);
        });
      }

      function fail(error) {
          alert("An error has occurred: Code = " + error.code);
          console.log("upload error source " + error.source);
          console.log("upload error target " + error.target);
      }
    }

  }
    
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
