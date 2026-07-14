import {mapGetters} from 'vuex';
import lodash from 'lodash';
import localeMapping from '../../../localizationLanguages';
import pagination from '../../pagination';
import moment from 'moment';
import { isProxy, toRaw } from 'vue';

export default {
    data () {
        return {
            loadedScreen: false,
            websiteUrl: $('#website_url').val(),
            localeMapping: localeMapping,
            locale: $('#dashboard-system-language').val(),
            filter:'',
            itemsQuantity:0,
            priceToChange:'',
            productOptionSwitcher:'product',
            priceModifierSign:[
                {'name': '+', 'value': '+'},
                {'name': '-', 'value': '-'},
            ],
            selectedPriceSign:'+',
            priceModifierType:[{'name': '%', 'value': 'percent'}],
            selectedPriceType:'percent',
            allFilterProducts:0,
            filteredProductIds:[],
            minusOptionProcess:false,
            matchingFilter:false,
            processedElBlock:false,
            origProcessed:true,
            endProcessed:false,
            itemsProcessed:0,
            formProcessing:false

        }
    },
    components: {

    },
    computed: {
        ...mapGetters({
            configDataInfo:'getConfigDataInfo',
            additionalInfo:'getAdditionalInfo',
            truncateText: 'truncateText',
            unescapeValue:'unescapeValue',
            checkedItemsData:'getCheckedItems',
            totalFoundItems:'getTotalItemsFound',
            filterData:'getFilterData',
            ucFirstAllText:'ucFirstAllText',
            ProductsGridInfoData: 'getProductsGridInfo',
            currencyInfo: 'getCurrencyInfo',
        })
    },
    watch: {

    },
    methods: {
        closeMassAction() {
            this.$store.commit('setActiveMassAction', 0);
        },
        async submitRegularForm()
        {
            let self = this,
                productsLabel = this.$t('message.confirmProduct')+'?';
            if(this.itemsQuantity > 1) {
                productsLabel = this.$t('message.confirmProducts')+'?';
            }

            let price = Number(this.priceToChange);
            if (typeof price !== 'number' || isNaN(price) || price === 0) {
                if(price == '') {
                    showMessage(this.$t('message.priceEmpty'), true, 5000);
                } else {
                    showMessage(this.$t('message.priceMustBeNumeric'), true, 5000);
                }

                return false;
            }

            if (this.formProcessing === true) {
                return false;
            }

            this.formProcessing = true;

            this.origProcessed = true;
            this.endProcessed = false;
            this.itemsProcessed = 0;

            showConfirm(this.$t('message.areYouSureYouWantToProcess') + ' ' + this.itemsQuantity + ' ' + productsLabel, function(){
                self.massProcessProductsRequest(0);
            }, function () {
                self.processedElBlock = false;
                self.formProcessing = false;
            });
        },
        async massProcessProductsRequest(step)
        {
            let filters = toRaw(this.filterData),
                matchingFilter = 0,
                minusOptionProcess = 0;

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            if(this.matchingFilter) {
                matchingFilter = 1;
            }

            if (this.productOptionSwitcher === 'option' && this.minusOptionProcess) {
                minusOptionProcess = 1;
            }

            const result = await this.$store.dispatch('processMassProductsPriceMassAction', {
                'router': this.$router,
                'productIds': Object.keys(this.checkedItemsData).join(','),
                'step': step,
                'filters': filters,
                'matchingFilter': matchingFilter,
                'filterQuantity': this.itemsQuantity,
                'productOptionSwitcher': this.productOptionSwitcher,
                'minusOptionProcess': minusOptionProcess,
                'priceToChange': this.priceToChange,
                'selectedPriceSign': this.selectedPriceSign,
                'selectedPriceType': this.selectedPriceType,
            });

            this.processedElBlock = true;
            this.itemsProcessed = this.itemsProcessed + result.responseText.quantity;
            if (isNaN(this.itemsProcessed)) {
                this.itemsProcessed = 0;
            }

            if (parseInt(result.error) === 0) {
                this.massProcessProductsRequest(step+1);
            } else {
                this.origProcessed = false;
                this.endProcessed = true;

                if(this.productOptionSwitcher === 'product') {
                    let productIds = Object.keys(this.checkedItemsData);
                    if(this.allFilterProducts) {
                        productIds = this.filteredProductIds;
                    }

                    if (typeof result.responseText.message !== 'undefined'){
                        showMessage(result.responseText.message, false, 5000);
                    } else {
                        showMessage(result.responseText, true, 5000);
                    }

                    let data = toRaw(this.ProductsGridInfoData);
                    if(productIds && typeof result.responseText.productPrices !== 'undefined') {
                        _.each(productIds, function(prodId, ind) {
                            _.each(data, function(prodData, index) {
                                if(parseInt(prodData.id) === parseInt(prodId)) {
                                    if(typeof result.responseText.productPrices[prodId] !== 'undefined') {
                                        data[index]['price'] = result.responseText.productPrices[prodId];
                                    }
                                }
                            });
                        });
                    }

                    this.formProcessing = false;
                    this.$store.commit('setProductsGridInfo', data);
                }
            }
        },
        async countProducts(event)
        {
            let isChecked = event.target.checked;

            this.processedElBlock = false;

            if (isChecked === true) {
                this.allFilterProducts = 1;
                let filters = toRaw(this.filterData);

                if (Object.keys(filters).length === 0) {
                    filters = {};
                }

                const result = await this.$store.dispatch('countProductsMassAction', {
                    'router': this.$router,
                    'filters': filters,
                });

                if(parseInt(result.error) !== 1) {
                    this.filteredProductIds = result.responseText.filteredProductIds;
                    this.itemsQuantity = parseInt(result.responseText.quantity);
                } else {
                    this.allFilterProducts = 0;
                    showMessage(this.$t('message.noProductsFound'), true, 5000);
                }
            } else {
                this.allFilterProducts = 0;
                this.itemsQuantity = parseInt(Object.keys(this.checkedItemsData).length);
            }

        },
        async getInitialData()
        {
            if (Object.keys(this.checkedItemsData).length === 0) {
                showMessage(this.$t('message.pleaseChooseAtLeastOneProduct'), true, 3000);
                this.closeMassAction();
            }

            this.itemsQuantity = parseInt(Object.keys(this.checkedItemsData).length);
            this.priceModifierType.push({'name': this.currencyInfo.currency, 'value': 'unit'});

            if (this.itemsQuantity > 0) {
                this.loadedScreen = true;
            }
        },
    },
    async created(){
        if (typeof this.localeMapping[this.locale] !== 'undefined') {
            this.$i18n.locale = this.localeMapping[this.locale];
        }

        this.getInitialData();
    },
    async updated() {
        this.$nextTick(function () {
            if (typeof checkboxRadioStyle !== 'undefined' && typeof checkboxRadioStyle() === "function") {
                checkboxRadioStyle();
            }
        })
    }
}
