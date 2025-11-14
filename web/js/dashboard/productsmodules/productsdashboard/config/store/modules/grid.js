import merge from 'lodash/merge';
import moment from 'moment';
import lodash from 'lodash';
import unescape from "lodash/unescape";
import {toRaw} from "vue";

let defaultState = {
    configDataInfo: [],
    gridInfo: [],
    checkedItems:{},
    allCheckedItemsTracking:{},
    activeMassAction:0,
    isMassActionActive: false,
    productsGridInfo :[],
    currencyInfo :[],
    updateGridStats:'',
    suppliersCompanies :[],
    salesStats :[],
    filterInfo:[],
    filterData:{},

};

let state = {};
merge(state, defaultState);

const actions = {

};

const mutations = {
    setConfigDataInfo : (state, payload) => {
        state.configDataInfo = payload;
    },
    setGridInfo : (state, payload) => {
        state.gridInfo = payload;
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
    // setLeadGridAdditionalInfo:(state, payload) => {
    //     state.leadGridAdditionalInfo = payload;
    // },
    setCurrencyInfo: (state, payload) => {
        state.currencyInfo = payload
    },
    setCheckedItems : (state, payload) => {
        state.checkedItems = payload;
    },
    setAllCheckedItemsTracking: (state, payload) => {
        state.allCheckedItemsTracking = payload
    },
    setUpdateGridStats: (state, payload) => {
        state.updateGridStats = payload
    },
    setSuppliersCompanies: (state, payload) => {
        state.suppliersCompanies = payload
    },
    setSalesStats: (state, payload) => {
        state.salesStats = payload
    },
    setChangeFilter : (state, payload) => {
        state.filterInfo = payload;
    },
    setFilterData: (state, payload) => {
        state.filterData = payload
    },


};

const getters = {
    getConfigDataInfo : (state) => {
        return state.configDataInfo
    },
    getGridInfo : (state) => {
        return state.gridInfo
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
    getCurrencyInfo : (state) => {
        return state.currencyInfo
    },
    getUpdateGridStats: (state) => {
        return state.updateGridStats;
    },
    getSuppliersCompanies : (state) => {
        return state.suppliersCompanies
    },
    getSalesStats : (state) => {
        return state.salesStats
    },
    getChangeFilter : (state) => {
        return state.filterInfo
    },
    getFilterData : (state) => {
        return state.filterData
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
    unescapeValue: (state) => {
        return (value) => {
            return unescape(value);
        }
    },
    ucFirstAllText: (state) => {
        return (str) => {
            let result = '';
            for (let i = 0; i < str.length; i += 1) {
                let shouldBeBig = str[i] !== ' ' && (i === 0 || str[i - 1] === ' ');
                result += shouldBeBig ? str[i].toUpperCase() : str[i];
            }
            return result;
        };
    },






};
export default {
    state,
    actions,
    getters,
    mutations
};
