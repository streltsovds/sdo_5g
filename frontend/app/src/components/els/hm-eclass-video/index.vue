<template>
<div class="player__wrapper">
    <div class="player__video-block">
        <div class="player__video-wrapper">
            <p class="player__video-title">{{ title ? title : 'Запись' }}</p>
            <div>
                <video :src="videoData.src" controlsList="nodownload" disablePictureInPicture controls ></video>
            </div>
        </div>
        <div class="player__playlist-wrapper">
            <p class="player__playlist-title">{{ titlePlaylist ? titlePlaylist : 'Плейлист' }}</p>
            <ul class="player__playlist">
              <li
                @click="changeVideoClick(item.id)"
                v-for="(item, index) in playlistData"
                :key="index"
                :class="{active: videoData.id === item.id}"
              >
                <p>{{ index + 1 }}</p>
                <div>
                  <p>{{ item.fileName || '' }}</p>
                </div>
                <p>{{ item.duration }}</p>
              </li>
            </ul>
        </div>
    </div>
</div>
</template>
<script>
export default {
  props: ['data', 'title', 'titlePlaylist'],
  data() {
    return {
      playlistData: this.data || [],
      currentVideo: null,
      videoData: {
        name: '-',
        duration: '-/-',
        src: '',
        id: 0
      }
    }
  },
  mounted() {
    this.init()
  },
  methods: {
        setActiveVideoElements(changedVideo = null) {
          if(this.currentVideo) {
            Object.keys(this.currentVideo).forEach(playlist => {
              if(!!this.currentVideo && ((changedVideo === playlist) || !changedVideo )){
                const current = this.currentVideo;
                this.videoData = {
                  name: current.fio || current.fileName,
                  duration: current.duration,
                  src: current.src,
                  id: current.id
                }
              }
            })
          }
        },
        setDuration() {
          this.playlistData.forEach((vidInfo, index) => {
            const hidenVideo = document.createElement('video');
            let duration;
            hidenVideo.src = vidInfo.src;
            hidenVideo.oncanplay = () => {
              duration = new Date(1000 * Math.trunc(hidenVideo.duration)).toISOString().substr(11, 8);
              this.playlistData[index].duration = duration;
            }
          })
        },
        changeVideoClick(id) {
          // const videoElement = $(`.player__video-block video`).first();
          const newVideo = this.playlistData.find(item => +item.id === +id);
          this.currentVideo = newVideo;

          this.setActiveVideoElements();
        },
        init() {
          this.setDuration();
          setTimeout(()=>{
            this.currentVideo = this.playlistData[0] || null;
            this.setActiveVideoElements();
          },4000)
        }
  }
}
</script>
<style scoped>
.player__wrapper{
    flex-grow: 1;
    background-color: #3D3D3D;
    display: flex;
    justify-content: center;
    color: #fff;
    padding: 25px 12px;
    border-radius: 4px;

}
.player__video-block{
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    flex: 18;
}
.player__video-title{
    margin-bottom: 14px;
    font-size: 14px;
}
.player__video-wrapper{
    padding: 0 12px;
    flex: 3;
    min-width: 400px;
}
.player__video-wrapper video{
    width: 100%;
    border-radius: 4px;
    background-color: #000;
    min-width: 350px;
    height: 456px;

}
.player__btn:not(:first-child){
    margin-top: 25px;
}
.player__btn{
    display: block;
    width: 35px;
    height: 35px;
    cursor: pointer;
}
.player__playlist-wrapper{
    padding: 0 12px;
    flex: 2;
    display: flex;
    flex-direction: column;
    min-width: 300px;
}
.player__playlist{
    background-color: #303030;
    list-style: none;
    color: #C1C0C8;
    border-radius: 4px;
    height: 100%;
    max-height: 500px;
    padding: 20px 0px;
    padding-right: 0;
}
.player__playlist-title{
    margin: 16px 0 8px 0;
    font-size: 14px;
}
.player__playlist {
    margin: 0;
    overflow-y: scroll;
}
.player__playlist li{
    padding: 10px 0px;
    cursor: pointer;
    display: flex;
}
.player__playlist li.active{
    background: rgba(255, 255, 255, 0.1);
}
.player__playlist li>p{
    flex: 1;
    padding: 0 8px;
    text-align: center;
    font-size: 12px;
}
.player__playlist li div{
    display: flex;
    flex-direction: column;
    flex: 16;
}
.player__playlist li div>p{
    font-size: 12px;
    color: #757575;
    margin-bottom: 0;
}
.player__playlist li div>p:first-child{
    font-weight: bold;
    color: #C1C0C8;
}
.player__video-info{
    display: flex;
    justify-content: space-between;
    margin-top: 16px;
    color: #C1C0C8;
    font-size: 14px;
}

.player__playlist::-webkit-scrollbar {
  width: 5px;
}
.player__playlist::-webkit-scrollbar-thumb {
  background-color:#666666;
  border-radius: 6px;
}

.player__header {
    margin-bottom: 20px;
}

.player__back {
    font-size: 14px;
    padding-right: 16px;
}

.player__username {
    font-weight: bold;
    font-size: 25px;
}

@media (max-width: 1200px){
    .player__controls{
        padding: 5px 12.5px;
    }
}
</style>
