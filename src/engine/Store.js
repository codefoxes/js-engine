let STORE = {};
let COUNTER = 0;

export class Store {
	constructor(initialState, name) {
		this.name = '';
		this._listeners = [];
		if (name)
			this.name = name;
		this.idx = COUNTER++;
		STORE[this.idx] = initialState;
		this.initialState = initialState;
	}
	get() {
		return STORE[this.idx];
	}
	set(state, info) {
		if (this.condition) {
			const newState = this.condition(Object.assign(Object.assign({}, STORE[this.idx]), state(STORE[this.idx])), info);
			if (newState)
				STORE[this.idx] = newState;
		}
		else {
			STORE[this.idx] = Object.assign(Object.assign({}, STORE[this.idx]), state(STORE[this.idx]));
		}
		this._listeners.forEach(fn => fn());
	}
	setCondition(func) {
		this.condition = func;
	}
	reset() {
		STORE[this.idx] = this.initialState;
		this._listeners.forEach(fn => fn());
	}
	subscribe(fn) {
		this._listeners.push(fn);
	}
	unsubscribe(fn) {
		this._listeners = this._listeners.filter(f => f !== fn);
	}
}

// React Specific.
export function useStore(store) {
	const [state, setState] = React.useState(store.get());
	function updateState() {
		setState(store.get());
	}
	React.useEffect(() => {
		store.subscribe(updateState);
		return () => store.unsubscribe(updateState);
	});
	return state;
}

const initialState = {
	engineOptions: { ...JSEngine.options },
	saving: false,
}

class EngineStoreClass extends Store {
	setOption( key, value ) {
		this.set( ( prev ) => {
			const engineOptions = Object.assign( {}, prev.engineOptions, { [key]: value } )
			return { engineOptions }
		} )
	}

	setSaving( saving ) {
		this.set( () => ( { saving } ) )
	}
}

export const EngineStore = new EngineStoreClass( initialState )
