import merge from 'lodash/merge';
import lodash from 'lodash';
import unescape from "lodash/unescape";

let defaultState = {
    configDataInfo: [],
    additionalInfo: [],
    currencyInfo :[],
    totalItemsFound:0,
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
    setAdditionalInfo : (state, payload) => {
        state.additionalInfo = payload;
    },
    setCurrencyInfo: (state, payload) => {
        state.currencyInfo = payload
    },
    setTotalItemsFound: (state, payload) => {
        state.totalItemsFound = payload
    },
    setFilterData: (state, payload) => {
        state.filterData = payload
    }

};

const getters = {
    getConfigDataInfo : (state) => {
        return state.configDataInfo
    },
    getAdditionalInfo : (state) => {
        return state.additionalInfo
    },
    getCurrencyInfo : (state) => {
        return state.currencyInfo
    },
    getTotalItemsFound : (state) => {
        return state.totalItemsFound
    },
    getFilterData : (state) => {
        return state.filterData
    },
    sortByColumn : (state) => {
        return (data, columnName, reverse) => {
            if (typeof reverse !== 'undefined') {
                return _.orderBy(data, columnName).reverse();
            }

            return _.orderBy(data, columnName);
        }
    },
    truncateText : (state) => {
        return (text, limit) => {
            if (text.length > limit) {
                text = text.substring(0, (limit - 3)) + '...';
            }

            return text;
        }
    },
    cleanText : (state) => {
        return (text, replaceTo) => {
            let replaceToSymbol = '';

            if (typeof replaceTo !== 'undefined') {
                replaceToSymbol = replaceTo;
            }

            text = text.replace(/(<([^>]+)>)/gi, replaceToSymbol);

            return text;
        }
    },
    countOnlySymbols : (state) => {
        return (text) => {
            let replaceToSymbol = text.replaceAll(/[.,?!;:\-—\[\]{}() ]/g, "").length;

            return replaceToSymbol;
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
    currencyOnly: (state) => {
        return (value) => {
            let result ='';

            result = (0).toLocaleString(state.currencyInfo.locale, { style: 'currency', currency:state.currencyInfo.currency, minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/\d/g, '').trim()

            return result;
        }
    },
    unescapeValue: (state) => {
        return (value) => {
            return unescape(value);
        }
    }
};
export default {
    state,
    actions,
    getters,
    mutations
};
