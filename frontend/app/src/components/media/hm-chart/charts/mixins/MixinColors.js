import { interpolateSpectral, scaleSequential } from "d3";

export const Colors = {
  methods: {
    getColors(countColor, mode = 'sequential') {

      if (mode == 'sequential') {
        return (
          scaleSequential(interpolateSpectral)
            .domain([-1, countColor])
        );
      } else if(mode === 'bin'){ 
        return (
          (i) => (['rgb(190, 229, 160)', 'rgb(240, 112, 74)'][i])
        );
      }else {
        
      }
    }
  }
};
