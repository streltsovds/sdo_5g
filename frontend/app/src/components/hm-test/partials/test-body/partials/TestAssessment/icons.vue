<template>
  <svg v-if="type === 'name'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.6155 5.61538C15.6155 8.16462 13.5494 10.2308 11.0002 10.2308C8.45092 10.2308 6.38477 8.16462 6.38477 5.61538C6.38477 3.06615 8.45092 1 11.0002 1C13.5494 1 15.6155 3.06615 15.6155 5.61538Z" stroke="#2960A0" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"/>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M11 13.3086C7.82308 13.3086 4.93231 14.4655 3.08462 15.424C1.80769 16.0871 1 17.4055 1 18.8547V19.5578C1 20.3547 1.64 21.0009 2.42923 21.0009H19.5708C20.36 21.0009 21 20.3547 21 19.5578V18.8547C21 17.4055 20.1923 16.0871 18.9154 15.424C17.0677 14.4655 14.1769 13.3086 11 13.3086Z" stroke="#2960A0" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <svg v-else-if="type === 'department'" xmlns="http://www.w3.org/2000/svg" width="25" height="19" viewBox="0 0 25 19" fill="none">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.5 18H2.5C1.671 18 1 17.329 1 16.5V2.5C1 1.671 1.671 1 2.5 1H22.5C23.329 1 24 1.671 24 2.5V16.5C24 17.329 23.329 18 22.5 18Z" stroke="#2960A0" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"/>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M17 9H21V4H17V9Z" stroke="#2960A0" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M4 15H12" stroke="#2960A0" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M4 12H8" stroke="#2960A0" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <svg v-else-if="type === 'position'" xmlns="http://www.w3.org/2000/svg" width="25" height="21" viewBox="0 0 25 21" fill="none">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M22.5 20H2.5C1.671 20 1 19.329 1 18.5V5.5C1 4.671 1.671 4 2.5 4H22.5C23.329 4 24 4.671 24 5.5V18.5C24 19.329 23.329 20 22.5 20Z" stroke="#2960A0" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M17 4H8V2.5C8 1.671 8.671 1 9.5 1H15.5C16.329 1 17 1.671 17 2.5V4Z" stroke="#2960A0" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M1 6.94141L11.688 13.8144C11.922 13.9314 12.199 14.0024 12.499 14.0024C12.798 14.0024 13.075 13.9314 13.309 13.8144H13.312L24 6.94141" stroke="#2960A0" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M13 10.5C13 10.776 12.776 11 12.5 11C12.224 11 12 10.776 12 10.5C12 10.224 12.224 10 12.5 10C12.776 10 13 10.224 13 10.5Z" stroke="#2960A0" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <svg v-else-if="type === 'profile'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M2.74707 22H19.2527C20.7675 22 22 20.7675 22 19.2529V7.46289C22 5.94831 20.7675 4.71607 19.2529 4.71607H11.9431C11.5686 4.71607 11.0934 4.42249 10.9262 4.08784L9.41184 1.05893C9.11509 0.465178 8.36244 0 7.69878 0H2.74707C1.23249 0 0 1.23249 0 2.74707V19.2527C0 20.7675 1.23249 22 2.74707 22ZM0.77832 2.74634C0.77832 1.66077 1.66174 0.777344 2.74732 0.777344H7.69903C8.07352 0.777344 8.54896 1.07141 8.71592 1.40605L10.2303 4.43448C10.527 5.02823 11.2794 5.49341 11.9433 5.49341H19.2529C20.3388 5.49341 21.2219 6.37659 21.2219 7.46217V9.19528H0.77832V2.74634ZM21.2219 9.97266H0.77832V19.251C0.77832 20.3369 1.66174 21.22 2.74732 21.22H19.2529C20.339 21.22 21.2219 20.3366 21.2219 19.251V9.97266Z" fill="#2960A0"/>
  </svg>
</template>
<script>
export default {
  props: {
    type: {
      type: String,
      default: ""
    }
  }
}
</script>
