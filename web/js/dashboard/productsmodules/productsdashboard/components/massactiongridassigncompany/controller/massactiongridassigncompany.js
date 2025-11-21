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
        async submitRegularForm()
        {
            let filters = toRaw(this.filterData);
            let self = this;

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            const result = await this.$store.dispatch('changeProductSuppliersMassAction', {
                'router': this.$router,
                'companies': this.checkedCompanies,
                'productIds': Object.keys(this.checkedItemsData),
                'filters': filters,
            });

            this.usedCompanyIds = [];
            this.checkedCompanies = [];
            if(result.error != 1) {
                showMessage(this.$t('message.done'), false, 3000);
                this.closeMassAction();

                let productIdsString = '';

                _.each(this.ProductsGridInfoData, function(productInfo, index) {
                    productIdsString = productIdsString.concat(productInfo.id, ",");
                });
                const resultSuppliersCompanies = await this.$store.dispatch('getSuppliersCompaniesGridData', {'router':this.$router, 'productIdsString' : productIdsString, 'groupByCompany': 0});


            } else {
                showMessage(this.$t('message.canNotAssignTemplate'), true, 5000);
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
