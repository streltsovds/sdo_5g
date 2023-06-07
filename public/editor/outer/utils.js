export const createElementFromHTML = (htmlString) => {
	var div = document.createElement("div");
	div.innerHTML = htmlString.trim();
  
	// Change this to div.childNodes to support multiple top-level nodes
	return div.firstChild;
};

export const getElementByCls = (cls) => document.querySelector(cls);
export const getElementById = (id) => document.getElementById(id);
export const addCls = (el, cls) => el.classList.add(cls);
export const removeCls = (el, cls) => el.classList.remove(cls);