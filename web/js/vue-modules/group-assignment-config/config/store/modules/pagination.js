import merge from 'lodash/merge';
const state = {
    rulesConfig: {
        currentPage: 1,
        itemsPerPage: 7,
        totalItems: 0,
        visiblePages: 4
    }
};
const getters = {
    getPagerState: (state) => {
        return (sectionName) => {
            return state[sectionName];
        }
    }
};
const actions = {};

const mutations = {
    setPaginationData: (state, payload) => {
        console.log('mutation');
        merge(state, payload);
    }
};

export default {
    state,
    getters,
    actions,
    mutations
};