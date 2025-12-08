import merge from 'lodash/merge';
const state = {
    productsGrid: {
        currentPage: 1,
        itemsPerPage: 20,
        totalItems: 0,
        visiblePages: 4
    },

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
        merge(state, payload);
    }
};

export default {
    state,
    getters,
    actions,
    mutations
};
