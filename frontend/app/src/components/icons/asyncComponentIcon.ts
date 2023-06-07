import {capitalize} from "@/components/icons/strings";
import Vue from "vue";

async function generateComponent(name: string): Promise<any> {
  const nameFull: string =
    "icon" + name.split('-').map((i) => capitalize(i)).join('');
  await Vue.component('icon', import(`./items/${nameFull}`)
    .then(m => {
      if (m) {
       return m.default;
      }
      return null;
    })
  );
};

export default generateComponent;
