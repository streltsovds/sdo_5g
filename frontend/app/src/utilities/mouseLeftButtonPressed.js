/** https://stackoverflow.com/a/3944291 */
function mouseLeftButtonPressed(evt) {
  evt = evt || window.event;
  if ("buttons" in evt) {
    return evt.buttons == 1;
  }
  var button = evt.which || evt.button;
  return button == 1;
}

export default mouseLeftButtonPressed;
