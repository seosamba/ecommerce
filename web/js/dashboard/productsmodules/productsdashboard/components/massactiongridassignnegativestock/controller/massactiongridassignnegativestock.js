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
            productNegativeStock:[
                {'name': 'disable negative stock', 'value': '0'},
                {'name': 'enable negative stock', 'value': '1'},
            ],
            selectedStock:'',
            filter:'',
            itemsQuantity:0,
            matchingFilter:false,
            processedElBlock:false,
            origProcessed:true,
            endProcessed:false,
            itemsProcessed:0,
            allFilterProducts:0,
            filteredProductIds:[],
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
        })
    },
    watch: {

    },
    methods: {
        closeMassAction() {
            this.$store.commit('setActiveMassAction', 0);
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
        async submitRegularForm()
        {
            let filters = toRaw(this.filterData),
                self = this;

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            if(this.selectedStock === '') {
                showMessage(this.$t('message.pleaseChooseOption'), true, 5000);
                return false;
            }

            this.origProcessed = true;
            this.endProcessed = false;
            this.itemsProcessed = 0;

            let productsLabel = this.$t('message.confirmProduct')+'?';
            if(this.itemsQuantity > 1) {
                productsLabel = this.$t('message.confirmProducts')+'?';
            }

            showConfirm(this.$t('message.areYouSureYouWantToProcess') + ' ' + this.itemsQuantity + ' ' + productsLabel, function(){
                self.massProcessProductsRequest(0);
            }, function () {
                self.processedElBlock = false;
            });
        },
        async massProcessProductsRequest(step)
        {
            let filters = toRaw(this.filterData),
                matchingFilter = 0,
                self = this;

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            if(this.matchingFilter) {
                matchingFilter = 1;
            }

            const result = await this.$store.dispatch('assignProductParamsMassAction', {
                'router': this.$router,
                'data': {'negativeStock': this.selectedStock},
                'productIds': Object.keys(this.checkedItemsData).join(','),
                'step': step,
                'filters': filters,
                'matchingFilter': matchingFilter,
                'filterQuantity': this.itemsQuantity,
                'productChangedType': 'negativeStock',
            });

            this.processedElBlock = true;
            this.itemsProcessed = this.itemsProcessed + result.responseText.quantity;

            if (parseInt(result.error) === 0) {
                this.massProcessProductsRequest(step+1);
            } else {
                this.origProcessed = false;
                this.endProcessed = true;

                showMessage(this.$t('message.negativeStockHasBeenChanged'), false, 3000);

                let productIds = Object.keys(this.checkedItemsData);
                if(this.allFilterProducts) {
                    productIds = this.filteredProductIds;
                }
                let data = toRaw(this.ProductsGridInfoData);
                if(productIds) {
                    _.each(productIds, function(prodId, ind) {
                        _.each(data, function(prodData, index) {
                            if (parseInt(prodData.id) === parseInt(prodId)) {
                                data[index]['negative_stock'] = self.selectedStock;
                                //data[index]['negative_stock'] = result.responseText.productChangedParams[prodId];
                            }
                        });
                    });
                }

                this.$store.commit('setProductsGridInfo', data);
                //this.closeMassAction();
            }
        },
        async getInitialData()
        {
            if (Object.keys(this.checkedItemsData).length === 0) {
                showMessage(this.$t('message.pleaseChooseAtLeastOneProduct'), true, 3000);
                this.closeMassAction();
            }

            this.itemsQuantity = parseInt(Object.keys(this.checkedItemsData).length);

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
