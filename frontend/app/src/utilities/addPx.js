// based on https://github.com/maxtherocket/add-px
export default function(num){
  if (!isNaN(num) && num !== 0){
    return num.toString(10) + "px";
  }
  return num;
};
