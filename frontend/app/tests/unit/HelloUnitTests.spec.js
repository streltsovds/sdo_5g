import { shallowMount } from "@vue/test-utils";
import HelloUnitTests from "@/components/helpers/HelloUnitTests.vue";

describe("HelloUnitTests.vue", () => {
  it("renders props.msg when passed", () => {
    const msg = "new message";
    const wrapper = shallowMount(HelloUnitTests, {
      propsData: { msg }
    });
    expect(wrapper.text()).toMatch(msg);
  });
});
