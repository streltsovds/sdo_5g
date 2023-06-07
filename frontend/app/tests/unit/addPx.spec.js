import addPx from "@/utilities/addPx";

describe("addPx()", () => {
  it("adds px to numbers", () => {
    expect(addPx(4)).toMatch("4px");
    expect(addPx("4")).toMatch("4px");
  });

  it("doesn't add px to 0", () => {
    expect(addPx(0)).toStrictEqual(0);
    expect(addPx("0")).toMatch("0");
  });

  it("doesn't add px to empty string", () => {
    expect(addPx("")).toMatch("");
  });

  it("doesn't add px to string with units of management", () => {
    expect(addPx("1em")).toMatch("1em");
    expect(addPx("2%")).toMatch("2%");
  });

  it("doesn't add px to random string", () => {
    expect(addPx("some text")).toMatch("some text");
  });
});
