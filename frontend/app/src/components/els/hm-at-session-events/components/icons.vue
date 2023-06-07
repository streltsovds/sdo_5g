<template>
  <svg v-if="type === 'status'" xmlns="http://www.w3.org/2000/svg" width="24" height="19" viewBox="0 0 24 19" :fill="color">
    <path d="M20.1626 0.715177L8.84043 13.0957L3.9271 6.95539C3.20271 6.04847 1.83696 5.86866 0.874067 6.54572C-0.0910516 7.22278 -0.287042 8.5053 0.437105 9.40804L6.98236 17.5898C7.37854 18.087 7.99444 18.3866 8.65705 18.4068C8.68052 18.4091 8.704 18.4091 8.72748 18.4091C9.36241 18.4091 9.96966 18.1493 10.3829 17.694L23.4734 3.37591C24.2596 2.51882 24.1531 1.22865 23.2369 0.493663C22.3249 -0.241556 20.9468 -0.145629 20.1626 0.715177Z" :fill="color"/>
  </svg>
  <svg v-else-if="type === 'tag'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
    <path d="M7 13.5C10.5899 13.5 13.5 10.5899 13.5 7C13.5 3.41015 10.5899 0.5 7 0.5C3.41015 0.5 0.5 3.41015 0.5 7C0.5 10.5899 3.41015 13.5 7 13.5Z" :stroke="color" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M7 9.66667V7" :stroke="color" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M7 4.3335H7.00667" :stroke="color" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      default: ''
    },
    color: {
      type: String,
      default: '#DADADA'
    }
  }
}
</script>
