<template>
  <div class="INFOBLOCK infoblock-random-subjects">
    <div class="subject-card-course" v-if="subject">
      <div class="subject-card-course__header">
        <div class="subject-card-course__header-image"
             :style="{background: !subject.userIcon ? 'rgba(74, 144, 226, 0.5)' : '', backgroundImage: subject.userIcon ? `url(${subject.userIcon})` : ''}">

          <div v-if="!subject.userIcon" class="subject-card-course__header-image-default">
            <a style="width: 76px; height: 71px"><svg width="76" height="71" viewBox="0 0 76 71" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M57.3229 28.8191V48.3988C61.0585 45.3073 62.4754 41.9581 62.4754 41.9581V26.8869L62.3466 26.7581L75.3568 21.4767V16.3242C75.3568 16.3242 64.1501 20.9615 57.3229 23.7953V28.8191ZM38.0009 54.8393C18.6789 54.8393 13.5264 41.9581 13.5264 41.9581V27.0156L13.6551 26.758L38.0009 36.8054L57.3229 28.8189V48.3986C54.4891 50.7173 52.1704 51.8766 52.1704 51.8766C48.6924 53.5513 44.0552 54.8393 38.0009 54.8393Z" fill="#AAB1BA"/>
              <path d="M0.644531 16.5943L38.0005 1.13672L75.3564 16.5943C75.3564 16.5943 64.1497 21.2316 57.3225 24.0655V29.0892L38.0005 37.0757L13.6547 27.0282L0.644531 21.747V16.5943ZM41.0919 18.1402C41.6071 17.7538 41.8647 17.2386 41.8647 16.5945C41.8647 15.1775 40.1902 14.0182 38.0003 14.0182C35.8105 14.0182 34.1359 15.1775 34.1359 16.5945C34.1359 18.0115 35.8105 19.1708 38.0003 19.1708C39.2886 19.1707 40.4479 18.7842 41.0919 18.1402Z" fill="#AAB1BA"/>
              <path d="M41.8655 16.1935C41.8655 16.7087 41.6079 17.2241 41.0926 17.7393C40.4485 18.3834 39.2892 18.7698 38.0011 18.7698C35.8112 18.7698 34.1367 17.6105 34.1367 16.1935C34.1367 14.7765 35.8112 13.6172 38.0011 13.6172C40.191 13.6173 41.8655 14.7767 41.8655 16.1935Z" fill="#F2EDDA"/>
              <path d="M57.3234 57.4141C58.7404 57.4141 59.8997 58.5734 59.8997 59.9904C59.8997 61.4074 58.7404 62.5667 57.3234 62.5667C55.9064 62.5667 54.7471 61.4074 54.7471 59.9904C54.7471 58.5734 55.9065 57.4141 57.3234 57.4141Z" fill="#FF7474"/>
              <path d="M62.4746 70.9441C62.2169 70.9441 61.9594 70.6864 61.8305 70.4289L60.5424 65.2764V65.1476C60.5424 64.89 60.8001 64.6324 61.0576 64.5035C61.444 64.3747 61.7017 64.6323 61.8305 65.0187L63.1186 70.1712V70.3C63.1186 70.5576 62.8609 70.8152 62.6034 70.9441H62.4746ZM52.1695 70.9441H52.0407C51.783 70.8153 51.5255 70.5576 51.5255 70.3V70.1712L52.8136 65.0187C52.9423 64.6323 53.2 64.5035 53.5865 64.5035C53.8441 64.6323 54.1017 64.89 54.1017 65.1476V65.2764L52.8136 70.4289C52.6847 70.6864 52.4271 70.9441 52.1695 70.9441ZM57.3219 70.9441C56.9355 70.9441 56.6778 70.6864 56.6778 70.3V66.4356C56.6778 66.0491 56.9355 65.7914 57.3219 65.7914C57.7084 65.7914 57.9661 66.0491 57.9661 66.4356V70.3C57.9661 70.6864 57.7084 70.9441 57.3219 70.9441ZM57.3219 63.2153C55.5185 63.2153 54.1017 61.7983 54.1017 59.995C54.1017 58.4492 55.261 57.1611 56.678 56.7747V48.4018V28.8221V24.7L51.783 26.761L38.2576 32.2999C38.1288 32.4287 37.8712 32.4287 37.7424 32.2999L0.38644 16.8424C0.128763 16.7135 0 16.456 0 16.1983C0 15.9406 0.128763 15.6831 0.38644 15.5542L37.7424 0.0965724C37.8712 -0.0321908 38.1288 -0.0321908 38.2576 0.0965724L75.6136 15.5542C75.8712 15.683 76 15.9406 76 16.1983C76 16.456 75.8712 16.7135 75.6136 16.8424L57.9661 24.1848V27.9204L74.7118 20.9645V20.0629C74.7118 19.6764 74.9694 19.4187 75.3559 19.4187C75.7423 19.4187 76 19.6764 76 20.0629V21.3509C76 21.6086 75.8712 21.8661 75.6136 21.9951L57.9661 29.2085V47.1135C60.5424 44.9237 61.5728 42.3475 61.8305 41.8321V32.944C61.8305 32.5576 62.0881 32.2999 62.4746 32.2999C62.861 32.2999 63.1187 32.5576 63.1187 32.944V41.9609C63.1187 42.0897 63.1187 42.0897 63.1187 42.2186C63.1187 42.3473 61.7017 46.083 57.9662 48.7881V56.9033C59.3832 57.161 60.5425 58.4491 60.5425 60.1236C60.5424 61.7983 59.1254 63.2153 57.3219 63.2153ZM57.3219 58.0628C56.2914 58.0628 55.3897 58.9644 55.3897 59.995C55.3897 61.0256 56.2914 61.9272 57.3219 61.9272C58.3525 61.9272 59.2541 61.0256 59.2541 59.995C59.2541 58.9644 58.3525 58.0628 57.3219 58.0628ZM2.31864 16.1983L37.9999 31.0118L55.5187 23.7984L41.3491 18.5169C40.5762 19.1611 39.4169 19.5475 38.1288 19.5475C35.5525 19.5475 33.6203 18.1305 33.6203 16.3272C33.6203 14.5239 35.5525 13.1069 38.1288 13.1069C40.7052 13.1069 42.6374 14.5239 42.6374 16.3272C42.6374 16.7137 42.5086 17.1001 42.3797 17.4865L57.4509 23.1544L73.939 16.3272L38.2576 1.51357L2.31864 16.1983ZM37.9999 14.2661C36.1965 14.2661 34.7796 15.1677 34.7796 16.1983C34.7796 17.2289 36.1966 18.1305 37.9999 18.1305C39.0305 18.1305 40.0609 17.7441 40.5762 17.2289L40.705 17.1001C40.9627 16.8424 41.0914 16.456 41.0914 16.0695C41.2204 15.1679 39.8034 14.2661 37.9999 14.2661ZM37.9999 55.4865C18.4202 55.4865 13.0101 42.3475 12.8813 42.2187C12.8813 42.09 12.8813 42.09 12.8813 41.9611V32.9442C12.8813 32.5577 13.139 32.3 13.5254 32.3C13.9119 32.3 14.1695 32.5577 14.1695 32.9442V41.8323C14.6847 42.9916 20.2238 53.9407 37.356 54.1984V41.9611C37.356 41.5746 37.6136 41.3169 38.0001 41.3169C38.3865 41.3169 38.6442 41.5746 38.6442 41.9611V54.1984C44.956 54.0696 49.3358 52.5239 51.9119 51.2356C52.1696 51.1069 52.556 51.2356 52.8136 51.4933C53.0713 51.751 52.8136 52.1374 52.5559 52.3949C49.5931 53.8119 44.8271 55.4865 37.9999 55.4865ZM37.9999 37.4525C37.8712 37.4525 37.8712 37.4525 37.7422 37.4525L0.38644 21.9949C0.128763 21.8662 0 21.6086 0 21.3509V20.0629C0 19.6764 0.257677 19.4187 0.644118 19.4187C1.03056 19.4187 1.28824 19.6764 1.28824 20.0629V20.9645L38.0001 36.1644L51.9119 30.3678C52.2984 30.2391 52.556 30.3678 52.8136 30.7543C52.9423 31.1407 52.8136 31.3984 52.4271 31.6559L38.2576 37.5813C38.1288 37.4525 38.1288 37.4525 37.9999 37.4525Z" fill="#51565F"/>
            </svg></a>
          </div>
          <div class="subject-card-course__header-image-photo" v-if="subject.teacherUrl" :style="{borderWidth: '1.3px'}">
            <a :href="applyUrl"></a>
            <hm-card-link
              title="Карточка пользователя"
              :url="subject.teacherUrl"
              rel="pcard"
              float=" "
              @click.native.stop
            >
              <img v-if="subject.teacherPhoto" :src="subject.teacherPhoto" alt="тьютор">
              <svg v-else width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M17.9982 0C8.06362 0 0 8.06422 0 17.9995C0 27.9358 8.06362 36 17.9982 36C27.9337 36 35.9973 27.9358 35.9973 17.9995C35.9973 8.06422 27.9337 0 17.9982 0V0ZM17.9998 5.40234C20.9879 5.40234 23.3999 7.81449 23.3999 10.8028C23.3999 13.7903 20.9879 16.2024 17.9998 16.2024C15.0125 16.2024 12.6006 13.7903 12.6006 10.8028C12.6006 7.81449 15.0125 5.40234 17.9998 5.40234V5.40234ZM17.9975 30.9574C13.4982 30.9574 9.52004 28.6526 7.19824 25.1611C7.25234 21.5785 14.3981 19.6172 17.9975 19.6172C21.5798 19.6172 28.7427 21.5785 28.7968 25.1611C26.475 28.6526 22.4977 30.9574 17.9975 30.9574V30.9574Z" fill="#70889E"/>
              </svg>
            </hm-card-link>
          </div>
          <div class="subject-card-course__header-image-date">
            <div class="subject-card-course__header-image-date-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.48214 8.25H20.5179C20.783 8.25 21 8.03906 21 7.78125V6.375C21 5.33984 20.1362 4.5 19.0714 4.5H17.1429V2.46875C17.1429 2.21094 16.9259 2 16.6607 2H15.0536C14.7884 2 14.5714 2.21094 14.5714 2.46875V4.5H9.42857V2.46875C9.42857 2.21094 9.21161 2 8.94643 2H7.33929C7.07411 2 6.85714 2.21094 6.85714 2.46875V4.5H4.92857C3.86384 4.5 3 5.33984 3 6.375V7.78125C3 8.03906 3.21696 8.25 3.48214 8.25ZM20.5179 9.5H3.48214C3.21696 9.5 3 9.71094 3 9.96875V20.125C3 21.1602 3.86384 22 4.92857 22H19.0714C20.1362 22 21 21.1602 21 20.125V9.96875C21 9.71094 20.783 9.5 20.5179 9.5ZM8.14286 17.4688C8.14286 17.2109 7.92589 17 7.66071 17H6.05357C5.78839 17 5.57143 17.2109 5.57143 17.4688V19.0312C5.57143 19.2891 5.78839 19.5 6.05357 19.5H7.66071C7.92589 19.5 8.14286 19.2891 8.14286 19.0312V17.4688ZM7.66071 12C7.92589 12 8.14286 12.2109 8.14286 12.4688V14.0312C8.14286 14.2891 7.92589 14.5 7.66071 14.5H6.05357C5.78839 14.5 5.57143 14.2891 5.57143 14.0312V12.4688C5.57143 12.2109 5.78839 12 6.05357 12H7.66071ZM13.2857 17.4688C13.2857 17.2109 13.0688 17 12.8036 17H11.1964C10.9313 17 10.7143 17.2109 10.7143 17.4688V19.0312C10.7143 19.2891 10.9313 19.5 11.1964 19.5H12.8036C13.0688 19.5 13.2857 19.2891 13.2857 19.0312V17.4688ZM12.8036 12C13.0688 12 13.2857 12.2109 13.2857 12.4688V14.0312C13.2857 14.2891 13.0688 14.5 12.8036 14.5H11.1964C10.9313 14.5 10.7143 14.2891 10.7143 14.0312V12.4688C10.7143 12.2109 10.9313 12 11.1964 12H12.8036ZM18.4286 17.4688C18.4286 17.2109 18.2116 17 17.9464 17H16.3393C16.0741 17 15.8571 17.2109 15.8571 17.4688V19.0312C15.8571 19.2891 16.0741 19.5 16.3393 19.5H17.9464C18.2116 19.5 18.4286 19.2891 18.4286 19.0312V17.4688ZM17.9464 12C18.2116 12 18.4286 12.2109 18.4286 12.4688V14.0312C18.4286 14.2891 18.2116 14.5 17.9464 14.5H16.3393C16.0741 14.5 15.8571 14.2891 15.8571 14.0312V12.4688C15.8571 12.2109 16.0741 12 16.3393 12H17.9464Z" fill="#FFFFFF"/>
              </svg>
            </div>
            <div class="subject-card-course__header-image-date-dates">
              <div v-if="subject.date && subject.date.begin" class="subject-card-course__header-image-date-dates__start">
                <svg width="18" height="18" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8.10492 3.6214V7.44069L11.3786 9.38444L10.833 10.3052L7.0137 7.9863V3.6214H8.10492ZM3.60361 11.3964C4.76304 12.5331 6.13844 13.1014 7.72981 13.1014C9.32119 13.1014 10.6852 12.5331 11.8219 11.3964C12.9813 10.237 13.5611 8.86156 13.5611 7.27019C13.5611 5.67882 12.9813 4.31478 11.8219 3.17809C10.6852 2.01866 9.32119 1.43895 7.72981 1.43895C6.13844 1.43895 4.76304 2.01866 3.60361 3.17809C2.46692 4.31478 1.89857 5.67882 1.89857 7.27019C1.89857 8.86156 2.46692 10.237 3.60361 11.3964ZM2.58059 2.15506C4.01282 0.722828 5.72923 0.00671047 7.72981 0.00671047C9.7304 0.00671047 11.4354 0.722828 12.8449 2.15506C14.2772 3.56456 14.9933 5.26961 14.9933 7.27019C14.9933 9.27077 14.2772 10.9872 12.8449 12.4194C11.4354 13.8289 9.7304 14.5337 7.72981 14.5337C5.72923 14.5337 4.01282 13.8289 2.58059 12.4194C1.17109 10.9872 0.466338 9.27077 0.466338 7.27019C0.466338 5.26961 1.17109 3.56456 2.58059 2.15506Z" fill="#FFC850"/>
                </svg>
                <span>{{ subject.date.begin }}</span>
              </div>
              <div v-if="subject.date && subject.date.end" class="subject-card-course__header-image-date-dates__end">
                <svg width="18" height="18" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8.10492 3.6214V7.44069L11.3786 9.38444L10.833 10.3052L7.0137 7.9863V3.6214H8.10492ZM3.60361 11.3964C4.76304 12.5331 6.13844 13.1014 7.72981 13.1014C9.32119 13.1014 10.6852 12.5331 11.8219 11.3964C12.9813 10.237 13.5611 8.86156 13.5611 7.27019C13.5611 5.67882 12.9813 4.31478 11.8219 3.17809C10.6852 2.01866 9.32119 1.43895 7.72981 1.43895C6.13844 1.43895 4.76304 2.01866 3.60361 3.17809C2.46692 4.31478 1.89857 5.67882 1.89857 7.27019C1.89857 8.86156 2.46692 10.237 3.60361 11.3964ZM2.58059 2.15506C4.01282 0.722828 5.72923 0.00671047 7.72981 0.00671047C9.7304 0.00671047 11.4354 0.722828 12.8449 2.15506C14.2772 3.56456 14.9933 5.26961 14.9933 7.27019C14.9933 9.27077 14.2772 10.9872 12.8449 12.4194C11.4354 13.8289 9.7304 14.5337 7.72981 14.5337C5.72923 14.5337 4.01282 13.8289 2.58059 12.4194C1.17109 10.9872 0.466338 9.27077 0.466338 7.27019C0.466338 5.26961 1.17109 3.56456 2.58059 2.15506Z" fill="#FFC850"/>
                </svg>
                <span>{{ subject.date.end  }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="subject-card-course__header-title">
          <span>{{ regTitle }}</span>
        </div>
        <div v-if="subject.longtime" class="subject-card-course__longtime">
          Объём, час.: <span>{{subject.longtime}}</span>
        </div>
      </div>
      <div v-if="!_isEmpty(subject.classifiers)" class="subject-card-course__classifiers" ref="slider">
        <swiper :options="swiperOptions">
          <swiper-slide v-for="(classifier, i) in subject.classifiers" :key="i"><span># {{ classifier }}</span></swiper-slide>
        </swiper>
      </div>
      <div v-if="subject.description" class="subject-card-course__description">
        <span v-html="subject.description"></span>
      </div>
      <div class="subject-card-course__actions">
        <a v-if="subject.url && subject.applyLabel || applyUrl && applyLabel "
           :disabled="isLoading"
           :href="subject.url || applyUrl">
          <span>{{ subject.applyLabel || applyLabel}}</span>
        </a>
      </div>
      <div class="subject-card-course__refresh" @click="refresh" :class="animRefresh">
        <v-tooltip left>
          <template v-slot:activator="{ on: onTooltip }">
            <svg v-on="onTooltip" width="26" height="24" viewBox="0 0 26 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.8685 13.8412C22.6986 13.7687 22.5106 13.7665 22.3402 13.8348L18.0016 15.5665C17.7811 15.6548 17.6736 15.9049 17.7616 16.1249C17.8496 16.346 18.1 16.4532 18.3203 16.3652L21.989 14.9007C21.2834 17.1859 19.869 19.1374 17.94 20.4563C15.7835 21.9312 13.2422 22.4408 10.7844 21.8901C7.25377 21.1003 4.52928 18.2182 3.74369 14.4879C3.69436 14.2556 3.47356 14.0977 3.23943 14.1372C3.0053 14.1767 2.84637 14.3988 2.89464 14.6316C3.74023 18.7078 6.72364 21.8635 10.5966 22.7299C11.3057 22.8883 12.0206 22.9667 12.7323 22.9667C14.7243 22.9667 16.6902 22.3528 18.4256 21.1662C20.4658 19.7715 21.9754 17.727 22.7557 15.3326L24.1706 18.8779C24.2586 19.0985 24.5087 19.2057 24.7292 19.1177C24.9498 19.0294 25.0572 18.7793 24.9692 18.559L23.238 14.2199C23.1692 14.0484 23.038 13.914 22.8685 13.8412Z" fill="#1E1E1E" stroke="#1E1E1E" stroke-width="0.1"/>
              <path d="M2.76243 9.74525C2.83096 9.91644 2.9619 10.0508 3.13176 10.1236C3.21949 10.1612 3.31229 10.1802 3.40482 10.1802C3.49095 10.1802 3.57709 10.1636 3.66028 10.1306L7.99835 8.39887C8.21914 8.3106 8.32634 8.06047 8.23834 7.83994C8.15035 7.61942 7.89968 7.51222 7.67942 7.60022L4.01148 9.06446C4.71733 6.78023 6.13171 4.82959 8.06075 3.51041C10.2172 2.0355 12.7583 1.52565 15.2161 2.0763C18.7462 2.86589 21.4709 5.74771 22.2563 9.47698C22.3053 9.70925 22.5261 9.86738 22.7602 9.82791C22.9944 9.78845 23.1536 9.56632 23.105 9.33352C22.2603 5.25812 19.2766 2.10324 15.4038 1.23685C12.7105 0.634461 9.93085 1.19019 7.57542 2.80056C5.53519 4.19574 4.02561 6.2397 3.24536 8.63353L1.82938 5.08799C1.74138 4.86746 1.49099 4.75999 1.27072 4.84799C1.05019 4.93626 0.942728 5.18639 1.03073 5.40692L2.76243 9.74525Z" fill="#1E1E1E" stroke="#1E1E1E" stroke-width="0.1"/>
            </svg>
          </template>
          <span>Обновить</span>
        </v-tooltip>

      </div>
    </div>
    <hm-empty v-else
    >
      {{ _('Нет данных для отображения') }}
    </hm-empty>
  </div>
</template>
<script>
import { mapActions } from "vuex";
import hmCardLink from "@/components/els/hm-card-link/index";
import { swiper, swiperSlide } from "vue-awesome-swiper";
import {isEmpty} from 'lodash';
import HmEmpty from "@/components/helpers/hm-empty";
export default {
  components: {hmCardLink,swiper, swiperSlide, HmEmpty},
  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: null
    },
    applyUrl: {
      type: String,
      default: null
    },
    applyLabel: {
      type: String,
      default: "Подать заявку"
    },
    refreshUrl: {
      type: String,
      default: null
    },
    classifierType: {
      type: String,
      default: null
    },
    classifiers: {
      type: [Array, Object, String],
      default: () => []
    },
    teacherUrl: {
      type: String,
      default: ''
    },
    teacherPhoto: {
      type: String,
      default: ''
    },
    begin: {
      type: String,
      default: ''
    },
    end: {
      type: String,
      default: ''
    },
    url: {
      type: String,
      default: ''
    },
    userIcon: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      isLoading: false,
      subject: {
        title: this.title,
        description: this.description,
        url: this.url,
        classifiers: this.classifiers,
        teacherUrl: this.teacherUrl,
        teacherPhoto: this.teacherPhoto,
        applyLabel: this.applyLabel,
        date: {
          begin: this.begin,
          end: this.end
        },
        userIcon: this.userIcon
      },
      swiperOptions: {
        slidesPerView: 'auto',
        spaceBetween: 30,
      }
    };
  },
  computed: {
    animRefresh() {
      return this.isLoading ? 'refresh__animation' : ''
    },
    regTitle() {
     return this.subject.title.length > 80 ? `${this.subject.title.substr(0,80)}...` : this.subject.title
    }
  },
  methods: {
    ...mapActions("alerts", ["addErrorAlert"]),
    refresh() {
      if (this.isLoading) return;

      this.isLoading = true;
      this.$axios
        .get(this.refreshUrl, {
          params: { classifier_type: this.classifierType }
        })
        .then(r => {
          if (r.status !== 200 || !r.data) throw new Error();
          return r.data;
        })
        .then(res => {
          this.subject = {
            title: res.title,
            description: res.description,
            url: res.url,
            classifiers: res.classifiers,
            teacherUrl: res.teacherUrl,
            teacherPhoto: res.teacherPhoto,
            applyLabel: res.applyLabel,
            date: {
              begin: res.begin,
              end: res.end
            },
            userIcon: res.userIcon
          }
        })
        .catch(e => {
          this.addErrorAlert("Произошла ошибка загрузки курса");
          console.error(e);
        })
        .finally(() => (this.isLoading = false));
    },
    _isEmpty(data) {
      return isEmpty(data)
    },
    domMoving() {
      let newRefresh = document.getElementsByClassName('subject-card-course__refresh');
      // let newRefresh = document.getElementsByClassName('infoblock-random-subjects');
      for(let i =0; i < newRefresh.length; i++) {
        let test = newRefresh[i];
        newRefresh[i].remove();
        document.getElementsByClassName('infoblock-random-subjects')[0].parentNode.parentNode.children[0].style.position = 'relative';
        document.getElementsByClassName('infoblock-random-subjects')[0].parentNode.parentNode.children[0].append(test);
      }
    }
  },
  mounted() {
    this.domMoving()
  }
};
</script>
<style lang="scss">
.infoblock-random-subjects {
  .subject-card-course {
    width: 100%;
    height: 100%;
    position: relative;
    &__header {
      width: 100%;
      height: 180px;
      display: flex;
      &-image {
        min-width: 280px;
        max-width: 400px;
        width: 50%;
        height: 180px;
        position: relative;
        border-radius: 4px 4px 0 0;
        background-size: cover;
        background-position: center;
        &-photo {
          position: absolute;
          top: 20px;
          right: 20px;
          width: 40px;
          height: 40px;
          z-index: 1;
          border-style: solid;
          border-color: #FFFFFF;
          border-radius: 50%;
          display: flex;
          justify-content: center;
          align-items: center;
          overflow: hidden;
          background: #FFFFFF;
          .hm-card-link {
            > a {
              display: flex;
              justify-content: center;
              align-items: center;
              width: 100%;
              height: 100%;
              > img {
                width: 48px;
              }
            }
          }
          &-nouser {
            height: 100%;
          }
          > img {
            width: 48px;
          }
        }
        &-default {
          width: 100%;
          height: 132px;
          display: flex;
          justify-content: center;
          align-items: center;
        }
        &-date {
          width: 100%;
          height: 48px;
          background: rgba(68, 85, 107, 0.85);
          padding: 0 21px 0 23px;
          display: flex;
          justify-content: flex-start;
          align-items: center;
          position: absolute;
          bottom: 0;
          @media only screen and (min-width: 1264px) and (max-width: 1520px) {
            padding: 0 14px 0 0;
          }
          &-icon {
            width: 56px;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background:  #70889E;
          }
          &-dates {
            width: calc(100% - 56px - 18px);
            height: 100%;
            margin-left: 16px;
            display: flex;
            > div {
              width: 50%;
              height: 100%;
              display: flex;
              justify-content: center;
              align-items: center;
              > svg {
                margin-right: 8.5px;
                @media only screen and (min-width: 1264px) and (max-width: 1520px) {
                  display: none;
                }
              }
              > span {
                font-weight: 500;
                font-size: 14px;
                line-height: 21px;
                letter-spacing: 0.02em;
                color: #FFFFFF;
              }
            }
          }
        }
      }
      &-title {
        width: 50%;
        height: 100%;
        margin-left: 20px;
        margin-top: 10px;
        > span {
          font-weight: 400;
          font-size: 22px;
          line-height: 30px;
          letter-spacing: 0.02em;
          color: #5181B8;
        }
      }
    }


    &__classifiers {
        width: 100%;
        height: 26px;
        margin-top: 20px;
        display: flex;
        overflow: hidden;
        > div {
          display: flex;
          flex-wrap: wrap;
          margin: 0 !important;
          .swiper-wrapper {
            display: flex;
            height: 26px;
            width: 100%;
            > div {
              background: rgba(230, 230, 230, 0.5);
              border-radius: 30px;
              width: auto;
              display: flex;
              justify-content: flex-start;
              align-items: center;
              cursor: grab;
              > span {
                padding: 4px 12px;
                font-weight: normal;
                font-size: 12px;
                line-height: 18px;
                letter-spacing: 0.15px;
                color: #3E4E6C;
              }
            }
          }
        }
      }

    &__description {
      margin-top: 20px;
      width: 100%;
      max-height: 126px;
      overflow: hidden;
      > span {
        font-weight: normal;
        font-size: 14px;
        line-height: 21px;
        letter-spacing: 0.02em;
        color: #000000;

      }
    }

    &__longtime {
      margin-top: 10px;
      width: 100%;
      overflow: hidden;
      > span {
        font-weight: 500;
      }
    }

    &__actions {
      margin-top: 26px;
      margin-bottom: 16px;
      width: 166px;
      height: 36px;
      background: #FF9800;
      border-radius: 4px;
      cursor: pointer;
      > a {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        > span {
          font-weight: normal;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #FFFFFF;
        }
      }
    }
  }

  .subject-card-course-new:before {
    display: flex;
    justify-content: center;
    align-items: center;
    content: 'NEW';
    position: absolute;
    top: 36px;
    left: -2px;
    width: 78px;
    height: 32px;
    background: #05C985;
    color: #FFFFFF;
    border-radius: 4px 16px 16px 4px;
    z-index: 1;
    padding-top: 2px;
  }
}

.subject-card-course__refresh {
  position: absolute;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  top: 8px;
  right: 12px;
  border-radius: 50%;
  &:hover {
    background-color: rgba(30, 30, 30, 0.08);
  }
  &:active {
    background-color: rgba(30, 30, 30, 0.24);
  }
  cursor: pointer;
  user-select: none;
  &:focus{
    outline: none;
    border: none;
  }
  > svg {
    user-select: none;
    &:focus{
      outline: none;
      border: none;
    }
  }
}
.refresh__animation {
  animation: refreshRandomSubject .8s linear infinite;
}

@keyframes refreshRandomSubject {
  0%{
    transform: rotate(0);
  }
  100%{
    transform: rotate(360deg);
  }
}

@media screen and (max-width: 900px) {
  .infoblock-random-subjects .subject-card-course__header {
    flex-direction: column;
    height: auto;
  }
  .infoblock-random-subjects .subject-card-course__header-image-default {
    height: 180px;
  }
  .infoblock-random-subjects .subject-card-course__header-title {
    margin-left: 0;
    margin-bottom: 10px;
    width: 100%;
  }
  .infoblock-random-subjects .subject-card-course__header-image {
    width: 100%;
    max-width: 100%;
    height: 180px;
  }
}
@media(max-width: 768px) {
  .infoblock-random-subjects {
    padding: 0 16px;
    & .subject-card-course__header-image {
      min-width: auto;
    }
  }
}
@media(max-width: 500px) {
  .infoblock-random-subjects {
    & .subject-card-course {
      &__header-title {
        margin-bottom: 0;
        & > span {
          font-size: 20px;
          line-height: 26px;
        }
      }
      &__classifiers {
        margin-top: 10px;
      }
      &__description {
        & > span {
          font-weight: 300;
          font-size: 13px;
        }
        margin-top: 10px;
      }
      &__actions {
        width: 100%;
        margin-top: 16px;
      }
      &__header-image {
        border-radius: 0;
        &-date {
          height: 45px;
          &-icon {
            width: 53px;
          }
          &-dates > div > span {
            font-size: 13px;
            line-height: 15px;
          }
        }
      }
    }
  }
}

</style>
