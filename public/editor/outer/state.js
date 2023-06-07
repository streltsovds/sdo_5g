const createStore = window.Redux.createStore;
export const types = {
	ADD_SLIDE: "ADD_SLIDE",
	CHANGE_SLIDE: "CHANGE_SLIDE",
	INIT: "INIT",
	UPDATE_IFRAMES: "UPDATE_IFRAMES",
	UPDATE_SLIDES: "UPDATE_SLIDES",
	POPULATE_FROM_API: "POPULATE_FROM_API",
	API_ERROR: "API_ERROR",
	SAVE_SLIDES: "SAVE_SLIDES",
	SAVE_SLIDE: "SAVE_SLIDE",
	DELETE_SLIDE: "DELETE_SLIDE"

};

const reducer = (state = { current: null, slides: [], action: null, error: null }, action) => {
	const newState = Object.assign({}, state);
	newState.action = action.type;
	switch (action.type) {
	case types.ADD_SLIDE: {
		newState.slides.push(action.payload);
		newState.current = action.payload.id;
		return newState;
	}
	case types.DELETE_SLIDE: {
		const idx = newState.slides.findIndex(({id}) => id === action.payload);
		newState.slides.splice(idx, 1);
		return newState;
	}
	case types.CHANGE_SLIDE: {
		newState.current = action.payload;
		return newState;
	}
	case types.INIT: {
		newState.slides.push(action.payload);
		newState.current = action.payload.id;
		return newState;
	}
	case types.API_ERROR: {
		newState.error = action.payload;
		return newState;
	}
	case types.POPULATE_FROM_API: {
		newState.slides = action.payload;
		newState.current = newState.slides[newState.slides.length - 1].id;
		return newState;
	}
	case types.SAVE_SLIDE: {
		const idx = newState.slides.findIndex(({id}) => id === action.payload.id);
		newState.slides[idx].html = action.payload.html;
		newState.slides[idx]["compiled"] = action.payload.compiled;
		return newState;
	}
	default:
		return newState;
	}
};

export const store = createStore(reducer, window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__());