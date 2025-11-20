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
            productBrands:[],
            selectedBrand:0,
            newBrand:'',
            filter:'',
            itemsQuantity:0,
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
            gridInfo:'getGridInfo',
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

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            if(this.selectedBrand == 0 && this.newBrand == '') {
                showMessage(this.$t('message.pleaseChooseProductBrandOrAddNew'), true, 5000);
                return false;
            }
            let requestedBrand = '';
            let brand = this.newBrand;
            let selectedBrand = this.selectedBrand;

            if(brand) {
                requestedBrand = brand;
            } else if(selectedBrand) {
                requestedBrand = selectedBrand;
            }
            let brandValidation = new RegExp(/[^\u0080-\uFFFF\w\s-]+/gi);

            if(brandValidation.test(requestedBrand)){
                showMessage(this.$t('message.notValidBrand'), true, 3000);
                return false;
            } else {
                if(requestedBrand == '' || requestedBrand == 0 || typeof requestedBrand === 'undefined') {
                    showMessage(this.$t('message.pleaseChooseProductBrandOrAddNew'), true, 3000);
                    return false;
                }

                const result = await this.$store.dispatch('assignProductParamsMassAction', {
                    'router': this.$router,
                    'data': {'brand': requestedBrand},
                    'productIds': Object.keys(this.checkedItemsData).join(','),
                    'filters': filters,
                });

                if(result.error != 1) {
                    showMessage(this.$t('message.brandHasBeenChanged'), false, 3000);
                    this.closeMassAction();

                    let productIds = Object.keys(this.checkedItemsData);
                    let data = structuredClone(toRaw(this.ProductsGridInfoData));
                    if(productIds) {
                        _.each(productIds, function(prodId, ind) {
                            _.each(data, function(prodData, index) {
                                if(prodData.id == prodId) {
                                    data[index]['brandName'] = requestedBrand;
                                }
                            });
                        });
                    }

                    this.$store.commit('setProductsGridInfo', data);
                } else {
                    showMessage(this.$t('message.canNotAssignBrand'), true, 5000);
                }
            }
        },
        async getProductBrands()
        {
            let gridInfoBrands = structuredClone(toRaw(this.gridInfo.brands));
            this.productBrands = gridInfoBrands;
            this.itemsQuantity = parseInt(Object.keys(this.checkedItemsData).length);
        },
        async getInitialData()
        {
            if (Object.keys(this.checkedItemsData).length === 0) {
                showMessage(this.$t('message.pleaseChooseAtLeastOneProduct'), true, 3000);
                this.closeMassAction();
            }

            await this.getProductBrands();

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
