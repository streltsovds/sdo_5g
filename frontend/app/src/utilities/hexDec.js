export default function(h, alpha = 0.3) {
  if(h.indexOf('#') !== -1) {
    let r = parseInt(h.slice(1,3), 16),
      g = parseInt(h.slice(3,5), 16),
      b = parseInt(h.slice(5,7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
  }
};
