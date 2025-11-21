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

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            const result = await this.$store.dispatch('deleteProductMassAction', {
                'router': this.$router,
                'id': Object.keys(this.checkedItemsData).join(','),
                'filters': filters,
            });

            if(result.error != 1) {
                showMessage(this.$t('message.productHasBeenDeleted'), false, 3000);
                this.closeMassAction();

                const result = await this.$store.dispatch('getProductsGridData', {
                    'router': this.$router,
                    'searchData': this.filterData
                });

                if (result.status === 'error') {
                    showMessage('Please re-login', true, 3000);
                } else {
                    this.$store.commit('setUpdateGridStats', Date.now());
                }
            } else {
                showMessage(this.$t('message.canNotDeleteProduct'), true, 5000);
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
