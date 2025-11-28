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

                if(result.error != 1) {
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

            this.origProcessed = true;
            this.endProcessed = false;
            this.itemsProcessed = 0;

            let productsLabel = this.$t('message.confirmProduct')+'?';
            if(this.itemsQuantity > 1) {
                productsLabel = this.$t('message.confirmProducts')+'?';
            }

            showConfirm(this.$t('message.areYouSureYouWantToDelete') + ' ' + this.itemsQuantity + ' ' + productsLabel, function(){
                self.massProcessProductsRequest(0);
            }, function () {
                self.processedElBlock = false;
            });

            // const result = await this.$store.dispatch('deleteProductMassAction', {
            //     'router': this.$router,
            //     'id': Object.keys(this.checkedItemsData).join(','),
            //     'filters': filters,
            // });
            //
            // if(result.error != 1) {
            //     showMessage(this.$t('message.productHasBeenDeleted'), false, 3000);
            //     this.closeMassAction();
            //
            //     const result = await this.$store.dispatch('getProductsGridData', {
            //         'router': this.$router,
            //         'searchData': this.filterData
            //     });
            //
            //     if (result.status === 'error') {
            //         showMessage('Please re-login', true, 3000);
            //     } else {
            //         this.$store.commit('setUpdateGridStats', Date.now());
            //     }
            // } else {
            //     showMessage(this.$t('message.canNotDeleteProduct'), true, 5000);
            // }
        },
        async massProcessProductsRequest(step)
        {
            debugger;
            let filters = toRaw(this.filterData),
                matchingFilter = 0,
                self = this;

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            if(this.matchingFilter) {
                matchingFilter = 1;
            }

            const result = await this.$store.dispatch('deleteProductMassAction', {
                'router': this.$router,
                'productIds': Object.keys(this.checkedItemsData).join(','),
                'step': step,
                'filters': filters,
                'matchingFilter': matchingFilter,
                'filterQuantity': this.itemsQuantity,
            });
            debugger;

            this.processedElBlock = true;
            this.itemsProcessed = this.itemsProcessed + result.responseText.quantity;

            if (result.error == 0) {
                this.massProcessProductsRequest(step+1);
            } else {
                this.origProcessed = false;
                this.endProcessed = true;

                showMessage(this.$t('message.productHasBeenDeleted'), false, 3000);

                const result = await this.$store.dispatch('getProductsGridData', {
                    'router': this.$router,
                    'searchData': this.filterData
                });

                if (result.status === 'error') {
                    showMessage('Please re-login', true, 3000);
                } else {
                    this.$store.commit('setUpdateGridStats', Date.now());
                }
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
