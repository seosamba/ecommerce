import merge from 'lodash/merge';
import moment from 'moment';
import lodash from 'lodash';
import unescape from "lodash/unescape";
import {toRaw} from "vue";

let defaultState = {
    configDataInfo: [],
    additionalInfo: [],
    checkedItems:{},
    allCheckedItemsTracking:{},
    activeMassAction:0,
    isMassActionActive: false,
    productsGridInfo :[],

};

let state = {};
merge(state, defaultState);

const actions = {

};

const mutations = {
    setConfigDataInfo : (state, payload) => {
        state.configDataInfo = payload;
    },
    setAdditionalInfo : (state, payload) => {
        state.additionalInfo = payload;
    },
    setActiveMassAction : (state, payload) => {
        state.activeMassAction = payload;
    },
    setIsMassActionActive: (state, payload) => {
        state.isMassActionActive = payload
    },
    setProductsGridInfo:(state, payload) => {
        state.productsGridInfo = payload;
    },
    setTotalItemsFound: (state, payload) => {
        state.totalItemsFound = payload
    },
    setLeadGridAdditionalInfo:(state, payload) => {
        state.leadGridAdditionalInfo = payload;
    },
    setCurrencyInfo: (state, payload) => {
        state.currencyInfo = payload
    },
    setCheckedItems : (state, payload) => {
        state.checkedItems = payload;
    },
    setAllCheckedItemsTracking: (state, payload) => {
        state.allCheckedItemsTracking = payload
    },


};

const getters = {
    getConfigDataInfo : (state) => {
        return state.configDataInfo
    },
    getAdditionalInfo : (state) => {
        return state.additionalInfo
    },
    getActiveMassAction : (state) => {
        return state.activeMassAction
    },
    getIsMassActionActive : (state) => {
        return state.isMassActionActive
    },
    getRequestsData : (state) => {
        return state.requestsData
    },
    getCheckedItems : (state) => {
        return state.checkedItems
    },
    getAllCheckedItemsTracking : (state) => {
        return state.allCheckedItemsTracking
    },
    getProductsGridInfo:(state, payload) => {
        return state.productsGridInfo;
    },
    sortByColumn : (state) => {
        return (data, columnName, reverse, numerical) => {
            if (reverse) {
                return _.orderBy(data, [info => info[columnName].toLowerCase()]).reverse();
            }

            if (numerical) {
                return   _.orderBy(data,  [info => parseInt(info[columnName])]);
            }

            return _.orderBy(data,  [info => info[columnName].toLowerCase()]);
        }
    },
    toCurrency : (state) => {
        return (value, decimals) => {
            let result = parseFloat(value),
                minDecimals = 2;

            if (typeof decimals !== 'undefined') {
                minDecimals = decimals;
            }

            if (isNaN(result)) {
                return '';
            }

            if (state.currencyInfo) {
                result = result.toLocaleString(state.currencyInfo.locale, { style: 'currency', currency: state.currencyInfo.currency, minimumFractionDigits: minDecimals, maximumFractionDigits: minDecimals });
            }

            return result;
        }
    },






};
export default {
    state,
    actions,
    getters,
    mutations
};
