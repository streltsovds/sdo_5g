<template>
  <a class="hm-actions-sidebar-edit"
     :href="link"
     :title="title"
  >
    <svg width="26"
         height="26"
         viewBox="0 0 26 26"
         fill="none"
         xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M22.296 8.46231L8.32168 22.4367L1 25.0011L3.56444 17.6794L17.5388 3.70508L22.296 8.46231Z" fill="white" />
      <path d="M17.5503 3.72217L3.56934 17.7031L8.32601 22.4598L22.307 8.47885L17.5503 3.72217Z" fill="white" />
      <path opacity="0.2"
            d="M20.9028 7.08155L6.92188 21.0625L8.31471 22.4553L22.2957 8.47439L20.9028 7.08155Z"
            fill="white"
      />
      <path d="M2.11498 21.8027L1 24.9247L4.12193 23.8097L2.11498 21.8027Z" fill="#2960A0" />
      <path d="M22.296 8.46231L8.32168 22.4367L1 25.0011L3.56444 17.6794L17.5388 3.70508L22.296 8.46231Z"
            stroke="#2960A0"
            stroke-width="0.668215"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
      />
      <path
        d="M22.2963 8.46105L24.5262 6.23109C25.1581 5.59928 25.1581 4.5958 24.5262 4.00114L21.999 1.47386C21.3671 0.842045 20.3637 0.842045 19.769 1.47386L17.5391 3.70382L22.2963 8.46105Z"
        fill="#2960A0"
      />
      <path
        d="M22.2963 8.46105L24.5262 6.23109C25.1581 5.59928 25.1581 4.5958 24.5262 4.00114L21.999 1.47386C21.3671 0.842045 20.3637 0.842045 19.769 1.47386L17.5391 3.70382L22.2963 8.46105Z"
        stroke="#2960A0"
        stroke-width="0.668215"
        stroke-miterlimit="10"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path d="M10.8486 17.1203L20.6233 7.3457"
            stroke="#2960A0"
            stroke-width="0.668215"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
      />
      <path d="M6.90918 21.0601L9.1763 18.793"
            stroke="#2960A0"
            stroke-width="0.668215"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
      />
      <path d="M18.3822 2.90684L16.4375 4.85156L21.1942 9.60824L23.1389 7.66352L18.3822 2.90684Z" fill="white" />
      <path opacity="0.2"
            d="M18.3822 2.90684L16.4375 4.85156L21.1942 9.60824L23.1389 7.66352L18.3822 2.90684Z"
            fill="white"
      />
      <path d="M18.3822 2.90684L16.4375 4.85156L21.1942 9.60824L23.1389 7.66352L18.3822 2.90684Z"
            stroke="#2960A0"
            stroke-width="0.668215"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
      />
      <path d="M7 25L21 25"
            stroke="#2960A0"
            stroke-width="0.7"
            stroke-linecap="round"
      />
    </svg>
  </a>
</template>

<script>
export default {
  name: "hmSidebarButtonEdit",
  props: {
    link: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      default: ''
    }
  }
}
</script>

<style lang="scss">
  .hm-actions-sidebar-edit {
    text-decoration: none;
    width: 48px;
    height: 48px;
    background: #FFFFFF;
    border: 2px solid #A8C7EC;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;

    &:hover {
      background: #EDF4FC;
    }

    &:active {
      background: #CDDBF2;
    }
  }
</style>
