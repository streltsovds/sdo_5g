/** https://stackoverflow.com/a/62069687 */
function touchInside(el, evt) {
  // stop default behavior
  evt.stopPropagation();
  evt.preventDefault();
  evt = evt || window.event;

  // get box properties
  let a = el.getBoundingClientRect();
  // get current width
  let w = a.width;
  // get current height
  let h = a.height;

  // get values on x axis
  const x = evt.touches[0].pageX - a.left; // to start the value from 0 (remove offsetleft)
  // get values on y axis
  const y = evt.touches[0].pageY - a.top; // to start the value from 0 (remove offsettop)

  //! INFO
  // the box width range starts from [0 : w]
  // the box height range starts from [0 : h]

  // if X values between [0 , w] and Y values between [0 , h] then we inside the box | other than that then we left the box
  return ((x >= 0 && x <= w) && (y >= 0 && y <= h));
}

export default touchInside;
