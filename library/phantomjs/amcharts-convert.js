var page = new WebPage(),
    url = phantom.args[0];
    output = phantom.args[1];
page.paperSize = 
{
  format: 'A4',
  orientation: 'portrait',
  border: '1cm'
};
page.open(url, function (status) {
    setTimeout(function() {
        page.render(output);
        phantom.exit();
    }, 1000);
});
