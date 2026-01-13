import merge from 'lodash/merge';

let defaultState = {
    locationsData: [],
    savedLocations: [],
};

let state = {};
merge(state, defaultState);

const actions = {

};

const mutations = {
    setLocationsData : (state, payload) => {
        state.locationsData = payload;
    },
    setSavedLocations : (state, payload) => {
        state.savedLocations = payload;
    },
}

const getters = {
    getLocationsData : (state) => {
        return state.locationsData
    },
    getSavedLocations : (state) => {
        return state.savedLocations
    },
};
export default {
    state,
    actions,
    getters,
    mutations
};
