var page = new WebPage(),
    url = phantom.args[0];
page.viewportSize = {
  width: 900,
  height: 700
};

page.open(url, function (status) {
    setTimeout(function() {
        console.log(page.content);
        phantom.exit();
    }, 1);
});
