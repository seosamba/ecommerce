import {mapGetters} from 'vuex';
import lodash from 'lodash';
import localeMapping from '../../../localizationLanguages';
import pagination from '../../pagination';
import massactiongridassignbrand from '../../massactiongridassignbrand';
import massactiongridassigntemplate from '../../massactiongridassigntemplate';
import massactiongridassigntax from '../../massactiongridassigntax';
import massactiongridassignshipping from '../../massactiongridassignshipping';
import massactiongridassigntag from '../../massactiongridassigntag';
import massactiongridassigncompany from '../../massactiongridassigncompany';
import massactiongriddeleteproduct from '../../massactiongriddeleteproduct';
import massactiongridtoggleproduct from '../../massactiongridtoggleproduct';
import massactiongridproductquantity from '../../massactiongridproductquantity';
import massactiongridassignnegativestock from '../../massactiongridassignnegativestock';
import massactiongridassignpromo from '../../massactiongridassignpromo';
import massactiongridproductoptionprice from '../../massactiongridproductoptionprice';

import { isProxy, toRaw } from 'vue';
export default {
    data () {
        return {
            loadedScreen: false,
            websiteUrl: $('#website_url').val(),
            searchData:'',
            planId:0,
            localeMapping: localeMapping,
            locale: $('#dashboard-system-language').val(),
            perPageDataValues : {
                '10':10,
                '20':20,
                '50':50,
                '100':100
            },
            urlPredefinedFilterParams:[],
            initialScrollBarSize:'',
            salesSpinner:false,
            suppliersSpinner:false,
        }
    },
    components: {
        pagination: pagination,
        massactiongridassignbrand:massactiongridassignbrand,
        massactiongridassigntemplate:massactiongridassigntemplate,
        massactiongridassigntax:massactiongridassigntax,
        massactiongridassignshipping:massactiongridassignshipping,
        massactiongridassigntag:massactiongridassigntag,
        massactiongridassigncompany:massactiongridassigncompany,
        massactiongriddeleteproduct:massactiongriddeleteproduct,
        massactiongridtoggleproduct:massactiongridtoggleproduct,
        massactiongridproductquantity:massactiongridproductquantity,
        massactiongridassignnegativestock:massactiongridassignnegativestock,
        massactiongridassignpromo:massactiongridassignpromo,
        massactiongridproductoptionprice:massactiongridproductoptionprice,

    },
    computed: {
        ...mapGetters({
            formatDate: 'formatDate',
            formatOnlyDate: 'formatOnlyDate',
            formatTimeOnly: 'formatTimeOnly',
            configDataInfo:'getConfigDataInfo',
            gridInfo:'getGridInfo',
            filterData:'getFilterData',
            truncateText: 'truncateText',
            changeFilter: 'getChangeFilter',
            unescapeValue:'unescapeValue',
            ProductsGridInfoData: 'getProductsGridInfo',
            checkedItems:'getCheckedItems',
            activeMassAction:'getActiveMassAction',
            massActionActive:'getIsMassActionActive',
            salesStats:'getSalesStats',
            suppliersCompanies:'getSuppliersCompanies',
            toCurrency:'toCurrency',
            currencyOnly:'currencyOnly',
            updateGridStats:'getUpdateGridStats',
            pagerState: 'getPagerState',
            currencyInfo: 'getCurrencyInfo',
        })
    },
    watch: {
        applyFilterRemote()
        {
            this.applyFilter();
        },
        changeFilter (newData, originalData) {
            this.$store.commit('setPaginationData',
            {
                productsGrid: {
                    currentPage: 1,
                    itemsPerPage: this.pagerState('productsGrid').itemsPerPage,
                    totalItems: 0,
                    visiblePages: 4
                }
            });

            if (typeof newData.searchData !== 'undefined') {
                if (isProxy(newData.searchData)) {
                    let filterDataObject = {};
                    if (newData.searchData.filterByBrands !== 'undefined') {
                        filterDataObject['fbrand'] = toRaw(newData.searchData.filterByBrands);
                    }

                    if (newData.searchData.filterByTags !== 'undefined') {
                        filterDataObject['ftag'] = toRaw(newData.searchData.filterByTags);
                    }

                    if (newData.searchData.filterByStock !== 'undefined') {
                        filterDataObject['fqty'] = toRaw(newData.searchData.filterByStock);
                    }

                    if (newData.searchData.searchTerm !== 'undefined') {
                        filterDataObject['searchTerm'] = toRaw(newData.searchData.searchTerm);
                    }

                    this.searchData = filterDataObject;
                } else {
                    this.searchData = newData.searchData;
                }
            } else {
                this.searchData = '';
            }

            this.$store.commit('setFilterData', toRaw(this.searchData));

            this.applyFilter();
        },
        async updateGridStats (newData, originalData) {
            if (typeof newData !== 'undefined') {
                this.suppliersSpinner = true;
                this.salesSpinner = true;

                let productIdsString = '';

                _.each(this.ProductsGridInfoData, function(productInfo, index) {
                    productIdsString = productIdsString.concat(productInfo.id, ",");
                });

                const resultSuppliersCompanies = await this.$store.dispatch('getSuppliersCompaniesGridData', {'router':this.$router, 'productIdsString' : productIdsString, 'groupByCompany': 0});
                this.suppliersSpinner = false;
                const resultSalesStats = await this.$store.dispatch('getSalesStatsGridData', {'router':this.$router, 'productIdsString' : productIdsString});
                this.salesSpinner = false;
            }
        },
        activeMassAction  (newData, originalData) {
            if (newData == '0') {
                this.$store.commit('setIsMassActionActive', false);
            } else {
                this.$store.commit('setIsMassActionActive', true);
            }
        },

    },
    methods: {
        async applyFilter(type) {
            this.suppliersSpinner = true;
            this.salesSpinner = true;

            const result = await this.$store.dispatch('getProductsGridData', {
                'router': this.$router,
                'searchData': this.filterData
            });

            if (result.status === 'error') {
                showMessage('Please re-login', true, 3000);
            } else {
                this.loadedScreen = true;
                this.$store.commit('setUpdateGridStats', Date.now());
            }
        },
        async goToProductDetailsScreen(id, tabName, subTabName)
        {
            let openTabName = tabName || '',
                openSubTabName = subTabName || '';

            if (openTabName !== '') {
                this.$router.push({ name: 'product', params: {'id': id}, query:{'tabName': openTabName}});
            } else if (openSubTabName !== '') {
                this.$router.push({ name: 'product', params: {'id': id}, query:{'subTabName': openSubTabName}});
            } else {
                this.$router.push({name: 'product', params: {'id': id}, query: {'tabName': 'timeline'}});
            }
        },
        addRemoveItem: function(e, itemKey) {
            if (e.target.checked) {
                this.addCheckedItem(itemKey);
            } else {
                this.removeCheckedItem(itemKey);
            }

            this.forceUpdate();
        },
        addCheckedItem: function(itemKey) {
            let currentItems = this.checkedItems;
            if (_.isNull(currentItems)) {
                currentItems = {};
            }
            currentItems[itemKey] = itemKey;
            this.$store.commit('setCheckedItems', currentItems);
            this.$store.commit('setAllCheckedItemsTracking', {'items': currentItems});
        },
        removeCheckedItem: function(itemKey){
            let currentItems = this.checkedItems;
            if (currentItems !== null && currentItems.hasOwnProperty(itemKey)) {
                delete currentItems[itemKey];
                this.$store.commit('setCheckedItems', currentItems);
                this.$store.commit('setAllCheckedItemsTracking', {'items': currentItems});
            }
        },
        isCheckedItem: function(itemKey){
            let currentItems = this.checkedItems;

            if (currentItems !== null && currentItems.hasOwnProperty(itemKey)) {
                return true;
            }
            return false;
        },
        checkRemoveAllItemsOnPage: function(e)
        {
            let currentCheckedItems = this.checkedItems,
                productsGridInfo = this.ProductsGridInfoData;

            if (e.target.checked && productsGridInfo.length > 0) {
                productsGridInfo.forEach(function(productsGridInfo) {
                    currentCheckedItems[productsGridInfo.id] = productsGridInfo.id;
                });

            } else {
                productsGridInfo.forEach(function(productsGridInfo) {
                    delete currentCheckedItems[productsGridInfo.id];
                });
            }

            this.$store.commit('setCheckedItems', currentCheckedItems);
            this.$store.commit('setAllCheckedItemsTracking', {'items': currentCheckedItems});
            this.forceUpdate();
        },
        isAllChecked() {
            let currentCheckedItems = this.checkedItems,
                productsGridInfo = toRaw(this.ProductsGridInfoData),
                allKeyExists = true;

            productsGridInfo.forEach(function(productsGridInfo) {
                if (!currentCheckedItems.hasOwnProperty(productsGridInfo.id)) {
                    allKeyExists = false;
                }
            });

            return allKeyExists;
        },
        forceUpdate() {
            // ...
            this.$forceUpdate();  // Notice we have to use a $ here
            // ...
        },
        getParams(pathParams) {
            let result = {},
                tmpData = [];

            pathParams
                .split("&")
                .forEach(function (item) {
                    tmpData = item.split("=");
                    if (tmpData[0] !== '') {
                        result[decodeURIComponent(tmpData[0])] = decodeURIComponent(tmpData[1]);
                    }
                });
            return result;
        },
        getTotal(productId) {
            let total = this.salesStats.filter(s => s.product_id == productId && s.status !== 'new').reduce((sum, s) => sum + Number(s.count), 0);
            return total > 0 ? this.$t('message.totalSales') +': '+ total : '';
        },
        async updateProp(event, index, elementName){
            let newValue = event.target.value;

            if (typeof this.ProductsGridInfoData[index][elementName] ===  'undefined') {
                return false;
            }

            let data = toRaw(this.ProductsGridInfoData[index]);
            let oldValue = data[elementName];
            let productId = data['id'];
            let currencySymbol = this.currencyInfo.currencySymbol;
            let denyEdit = false;

            if (elementName === 'inventory' && oldValue === newValue) {
                denyEdit = true;
            } else {
                if(elementName === 'price') {
                    newValue = newValue.replace(currencySymbol, '');
                }

                if (oldValue === newValue) {
                    denyEdit = true;
                }
            }

            if(denyEdit) {
                this.ProductsGridInfoData[index][elementName] = oldValue;
                data = toRaw(this.ProductsGridInfoData);
                this.$store.commit('setProductsGridInfo', data);
                return false;
            }

            data[elementName] = newValue;

            const result = await this.$store.dispatch('updateParam', {'router':this.$router, 'id':productId, 'data':data});

            if (parseInt(result.error) === 1) {
                this.ProductsGridInfoData[index][elementName] = oldValue;
                showMessage(result.message, true, 3000);
                return false;
            } else {
                this.ProductsGridInfoData[index][elementName] = newValue;
                showMessage(this.$t('message.updated'), false, 2000);
            }

            data = toRaw(this.ProductsGridInfoData);
            this.$store.commit('setProductsGridInfo', data);
        },
        changeMassAction: function(e) {
            let activeMassAction = e.target.value;
            if (activeMassAction != 0 && Object.keys(this.checkedItems).length === 0) {
                showMessage(this.$t('message.pleaseChooseAtLeastOneProduct'), true, 3000);
                this.$store.commit('setActiveMassAction', '0');
                e.target.value = '0';
            } else {
                this.$store.commit('setActiveMassAction', activeMassAction);
            }
        },

    },
    async created(){
        if (typeof this.localeMapping[this.locale] !== 'undefined') {
            this.$i18n.locale = this.localeMapping[this.locale];
        }

        let urlParamsString = window.location.search;

        if (urlParamsString !== '' && urlParamsString.indexOf('?') > -1) {
            this.loadedScreen = true;
        } else {
            this.applyFilter('firstLoad');
        }


    },
    async updated() {
        this.$nextTick(function () {
            if (typeof checkboxRadioStyle !== 'undefined' && typeof checkboxRadioStyle() === "function") {
                checkboxRadioStyle();
            }
        })
    }
}
