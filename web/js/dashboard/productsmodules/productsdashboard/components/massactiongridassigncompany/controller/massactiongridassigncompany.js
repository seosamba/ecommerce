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
            allCompanies:[],
            usedCompanyIds:[],
            filter:'',
            itemsQuantity:0,
            checkedCompanies:[],
            matchingFilter:false,
            processedElBlock:false,
            origProcessed:true,
            endProcessed:false,
            itemsProcessed:0,
            allFilterProducts:0,
            filteredProductIds:[],
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
            let filters = toRaw(this.filterData);
            let self = this;

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            if (this.formProcessing === true) {
                return false;
            }

            this.formProcessing = true;

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
                self.formProcessing = false;
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

            const result = await this.$store.dispatch('changeProductSuppliersMassAction', {
                'router': this.$router,
                'companies': this.checkedCompanies,
                'productIds': Object.keys(this.checkedItemsData).join(','),
                'filters': filters,
                'step': step,
                'matchingFilter': matchingFilter,
                'filterQuantity': this.itemsQuantity,
            });

            this.processedElBlock = true;
            this.itemsProcessed = this.itemsProcessed + result.responseText.quantity;

            if (parseInt(result.error) === 0) {
                this.massProcessProductsRequest(step+1);
            } else {
                this.usedCompanyIds = [];
                this.checkedCompanies = [];
                this.origProcessed = false;
                this.endProcessed = true;

                showMessage(this.$t('message.done'), false, 3000);
                //this.closeMassAction();

                let productIdsString = '';

                _.each(this.ProductsGridInfoData, function(productInfo, index) {
                    productIdsString = productIdsString.concat(productInfo.id, ",");
                });
                const resultSuppliersCompanies = await this.$store.dispatch('getSuppliersCompaniesGridData', {'router':this.$router, 'productIdsString' : productIdsString, 'groupByCompany': 0});
                this.formProcessing = false;
            }
        },
        async getProductCompanies()
        {
            let checkedProductsIds = Object.keys(this.checkedItemsData);
            let data = toRaw(this.ProductsGridInfoData);
            let self = this;

            let productIdsString = '';
            _.each(checkedProductsIds, function(productId, index) {
                productIdsString = productIdsString.concat(productId, ",");
            });

            const supCompanies = await this.$store.dispatch('getSuppliersCompaniesGridData', {'router':this.$router, 'productIdsString' : productIdsString, 'groupByCompany': 1});

            if(typeof supCompanies !== 'undefined') {
                $.each(supCompanies, function(key, val) {
                    self.usedCompanyIds[val.company_id] = 1;
                    self.checkedCompanies.push(parseInt(val.company_id));
                });
            }

            const resultGetAllCompaniesData = await this.$store.dispatch('getAllCompaniesDataMassAction', {'router':this.$router});

            this.allCompanies = resultGetAllCompaniesData;
            this.itemsQuantity = parseInt(Object.keys(this.checkedItemsData).length);
        },
        async getInitialData()
        {
            if (Object.keys(this.checkedItemsData).length === 0) {
                showMessage(this.$t('message.pleaseChooseAtLeastOneProduct'), true, 3000);
                this.closeMassAction();
            }

            await this.getProductCompanies();

            if (this.itemsQuantity > 0) {
                this.loadedScreen = true;
            }
        },
        async markCompany(event, companyId)
        {
            let isChecked = event.target.checked,
                self = this;

            if (isChecked === true) {
                self.checkedCompanies.push(parseInt(companyId));
            } else {
                const index = self.checkedCompanies.indexOf(companyId);
                if (index !== -1) {
                    self.checkedCompanies.splice(index, 1);
                }
            }
        }
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
