import { IFRAME_URL } from "./constants.js";

export const iframeTemplate = (id) => 
	`<iframe id="${id}" class="iframe visible" src="${IFRAME_URL}" frameborder="0"></iframe>`.trim();
    
export const slideTemplate = (text, id) => `
<article id=${id} class="slider__slide">
    <button class="slider__button-delete"></button>
    <div class="slider__inner-slide">
        ${text}
    </div>
</article>`.trim();