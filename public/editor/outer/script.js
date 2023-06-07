import { addBtnEl, mainEl, sliderEl, submitBtnEl } from "./dom.js";

import { createElementFromHTML, getElementById, addCls, removeCls } from "./utils.js";

import { iframeTemplate, slideTemplate } from "./templates.js";

import { cls, text } from "./constants.js";

import {store, types} from "./state.js";

window.iframeMarkup = {};

const uuid = window.uuid;

const urlParams = new URLSearchParams(window.location.search);
const resourceId = urlParams.get("id");

const getIFrameID = (id) => `iframe-${id}`;
const getSlideID = (id) => `slide-${id}`;

let hideSubmitBtn = false;

// iframe-элемент, в который в родительском документе встроен этот редактор
let parentIrameEl = window.frameElement;

if (parentIrameEl) {
	hideSubmitBtn = parentIrameEl.getAttribute('data-slider-editor-no-save-button');
}

if (hideSubmitBtn) {
	submitBtnEl.style.display = "none";
}

console.log(window.frameElement);

const updateIframeVisibility = () => {
	const state = store.getState();
	if (state.action === types.UPDATE_IFRAMES) {
		for (const { id } of state.slides) {
			const iFrameEl = getElementById(getIFrameID(id));
			if (id !== state.current) {
				addCls(iFrameEl, cls.HIDDEN);
			} else {
				removeCls(iFrameEl, cls.HIDDEN);
			}
		}
	}
};
const changeSlide = (id) => {
	store.dispatch({type: types.CHANGE_SLIDE, payload: id});
};

const deleteSlide = (element, id) => {
	const iframe = document.querySelector(`#iframe-${id}`);
	store.dispatch({type: types.DELETE_SLIDE, payload: id});
	element.remove();
	iframe.remove();
	const state = store.getState();
	if(state.slides.length === 0) {
		store.dispatch({
			type: types.INIT,
			payload: {
				id: uuid(),
				html: ""
			}
		});
	}
	let count = 1;
	const slides = document.querySelectorAll('.slider__slide');
	slides.forEach((slide) => {
		if(!slide.classList.contains('slider__slide--add')) {
			console.log(slide)
			const text = slide.querySelector('.slider__inner-slide')
			text.innerHTML = `Слайд ${count}`;
			count++
		}
	})
}

const updateSlidesVisibility = () => {
	const state = store.getState();
	if (state.action === types.UPDATE_SLIDES) {
		for (const { id } of state.slides) {
			const slideEl = getElementById(getSlideID(id));
			if(slideEl) {
				if (id !== state.current) {
					removeCls(slideEl, cls.CURRENT_SLIDE);
					slideEl.onclick = () => changeSlide(id);
				} else {
					addCls(slideEl, cls.CURRENT_SLIDE);
					slideEl.onclick = null;
				}
			}
		}
	}
};

const storeDataForPassingToIframe = (id, html) => {
	window.iframeMarkup[id] = html;
};

const appendIFrame = (id, html = "") => {
	storeDataForPassingToIframe(id, html);

	const iframeHTML = iframeTemplate(getIFrameID(id));
	const iframeEl = createElementFromHTML(iframeHTML);
	mainEl.appendChild(iframeEl);
};

const appendSlide = (givenId) => {
	const slideNum = store.getState().slides.findIndex(({id}) => id === givenId) + 1;
	const slideHTML = slideTemplate(`Слайд ${slideNum}`, getSlideID(givenId));
	const slideEl = createElementFromHTML(slideHTML);
	const buttonDelete = slideEl.querySelector('.slider__button-delete');
	buttonDelete.addEventListener('click', () => {
		deleteSlide(slideEl, givenId);

	});
	sliderEl.appendChild(slideEl);
};

const handleInit = () => {
	const state = store.getState();
	const id = state.current;
	if (state.action === types.INIT) {
		appendIFrame(id);
		appendSlide(id);
		store.dispatch({type: types.UPDATE_IFRAMES});
		store.dispatch({type: types.UPDATE_SLIDES});
	}
};

const handleAddSlide = () => {
	const state = store.getState();
	const id = state.current;
	if (state.action === types.ADD_SLIDE) {
		appendIFrame(id);
		appendSlide(id);
		store.dispatch({type: types.UPDATE_IFRAMES});
		store.dispatch({type: types.UPDATE_SLIDES});
	}
};

const handleSlideChange = () => {
	const state = store.getState();
	if (state.action === types.CHANGE_SLIDE) {
		store.dispatch({type: types.UPDATE_IFRAMES});
		store.dispatch({type: types.UPDATE_SLIDES});
	}
};

const handlePopulateFromApi = () => {
	const { action, slides } = store.getState();
	if (action === types.POPULATE_FROM_API) {
		for (const { id, html } of slides) {
			appendIFrame(id, html);
			appendSlide(id);
		}
		store.dispatch({type: types.UPDATE_IFRAMES});
		store.dispatch({type: types.UPDATE_SLIDES});
	}
};

const handleApiError = () => {
	const { action, error } = store.getState();
	if (action === types.API_ERROR) {
		document.body.innerHTML = `<pre>${error}</pre>`.trim();
	}
};

const handleSlideSave = () => {
	const { action } = store.getState();
	if (action === types.SAVE_SLIDE) {
		removeCls(submitBtnEl, cls.SUBMIT_BTN_DISABLED);
	}
};

window.saveSlidesRequestPromise = () => {
	return new Promise((resolve, reject) => {
		const {action, slides} = store.getState();

		addCls(submitBtnEl, cls.SUBMIT_BTN_DISABLED);
		submitBtnEl.textContent = text.BTN_SAVING;
		const body = {
			"content": slides
		};
		fetch(`/resource/index/editor/id/${resourceId}`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X_REQUESTED_WITH": "XMLHttpRequest"
			},
			body: JSON.stringify(body)
		})
			.then((response) => response.json())
			.then((response) => {
				console.log(response); // eslint-disable-line
				if (response.result === "success") {
					addCls(submitBtnEl, cls.SUBMIT_BTN_DISABLED);
					submitBtnEl.textContent = text.BTN_DEFAULT;
				} else {
					submitBtnEl.textContent = text.BTN_ERROR;
					removeCls(submitBtnEl, cls.SUBMIT_BTN_DISABLED);
				};
				resolve()
			})
			.catch(error => {
				console.error(error); // eslint-disable-line
				store.dispatch({
					type: types.API_ERROR,
					payload:
						`При сохранении ресурса возникла ошибка:
						` +
						`${error.message}`.trim()
				});

				reject();
			});
	});
};

const handleSlidesSave = () => {
	const { action, slides } = store.getState();
	if (action === types.SAVE_SLIDES) {
		window.saveSlidesRequestPromise();
	}
};


store.subscribe(handleInit);
store.subscribe(handleAddSlide);
store.subscribe(updateSlidesVisibility);
store.subscribe(updateIframeVisibility);
store.subscribe(handleSlideChange);
store.subscribe(handlePopulateFromApi);
store.subscribe(handleApiError);
store.subscribe(handleSlideSave);
store.subscribe(handleSlidesSave);


addBtnEl.addEventListener("click", () => {
	store.dispatch({
		type: types.ADD_SLIDE,
		payload: {
			id: uuid(),
			html: ""
		}
	});

});




const getInfoFromApi = () => {
	if (resourceId) {
		fetch(`/resource/index/editor/id/${resourceId}`)
			.then((response) => response.json())
			.then(({result, content}) => {
				if (result === "success") {
					if (Array.isArray(content)) {
						store.dispatch({
							type: types.POPULATE_FROM_API,
							payload: content
						});
					} else {
						store.dispatch({
							type: types.INIT,
							payload: {
								id: uuid(),
								html: ""
							}
						});
					}
				} else if (result !== "success") {
					store.dispatch({
						type: types.API_ERROR,
						payload: result
					});
				} else {
					store.dispatch({
						type: types.API_ERROR,
						payload: "Неизвестная ошибка"
					});
				}
			})
			.catch(error => {
				console.error(error); // eslint-disable-line
				store.dispatch({
					type: types.API_ERROR,
					payload:
						`При загрузке ресурса возникла ошибка:
						` +
						`${error.message}`.trim()
				});
			});
	} else {
		store.dispatch({
			type: types.API_ERROR,
			payload: "Не найден идентификатор ресурса"
		});
	}
	window.addEventListener("message",(e) => {
		if (e.data.id) {
			store.dispatch({
				type: types.SAVE_SLIDE,
				payload: e.data
			});
		}
	});

	window.saveSlidesDispatch = () => {
		store.dispatch({
			type: types.SAVE_SLIDES
		});
	};

	submitBtnEl.addEventListener("click", window.saveSlidesDispatch);
};


document.addEventListener("DOMContentLoaded", getInfoFromApi);


