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
            minDateCreationDate: null,
            maxDateCreationDate: null,
            componentKey: 0,
            promoPrice: '',
            promoFrom:'',
            promoDue:'',
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
            currencyOnly:'currencyOnly',
            formatDate: 'formatDate',
            formatOnlyDate: 'formatOnlyDate',
            formatTimeOnly: 'formatTimeOnly',
            currencyInfo: 'getCurrencyInfo',
        })
    },
    watch: {

    },
    methods: {
        closeMassAction() {
            this.$store.commit('setActiveMassAction', 0);
        },
        selectedDatepickerPromoDate(selectedDate, datepickerType){
            if (datepickerType === 'promo-from') {
                this.minDateCreationDate = selectedDate;
            } else {
                this.maxDateCreationDate = selectedDate;
            }
            this.componentKey += 1;
        },
        async submitRegularForm()
        {
            let filters = toRaw(this.filterData);
            let self = this;

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            if(this.promoPrice == '' || this.promoPrice == 0) {
                showMessage(this.$t('message.enterCorrectPrice'), true, 3000);
            }

            let promoFrom = moment(this.promoFrom).format('DD-MMM-yyyy');
            let promoDue = moment(this.promoDue).format('DD-MMM-yyyy');

            if (promoFrom === 'Invalid date' || promoDue === 'Invalid date') {
                showMessage(this.$t('message.wrongDateFormat'), true, 3000);
            }

            if(promoFrom == '' || promoDue == '') {
                showMessage(this.$t('message.wrongDateFormat'), true, 3000);
            }

            const result = await this.$store.dispatch('assignPromoMassAction', {
                'router': this.$router,
                'promoPrice': this.promoPrice,
                'promoFrom':  promoFrom,
                'promoDue':   promoDue,
                'productIds': Object.keys(this.checkedItemsData),
                'filters': filters,
            });

            if(result.error != 1) {
                showMessage(this.$t('message.promoHasBeenAdded'), false, 3000);
                this.closeMassAction();

                let productIds = Object.keys(this.checkedItemsData);
                let data = structuredClone(toRaw(this.ProductsGridInfoData));
                if(productIds) {
                    _.each(productIds, function(prodId, ind) {
                        _.each(data, function(prodData, index) {
                            if(prodData.id == prodId) {
                                data[index]['negative_stock'] = self.selectedStock;
                            }
                        });
                    });
                }

                this.$store.commit('setProductsGridInfo', data);
            } else {
                showMessage(this.$t('message.canNotAssignNegativeStock'), true, 5000);
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
