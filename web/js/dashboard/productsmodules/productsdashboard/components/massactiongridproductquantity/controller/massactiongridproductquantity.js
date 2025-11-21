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
            inventory:'',
            inventoryEl:true,
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

            let productQuantity= this.inventory;
            let positivNumber = Math.sign(productQuantity);

            if(positivNumber == -1 || Number.isNaN(positivNumber)) {
                showMessage(this.$t('message.pleaseEnterValidNumber'), true, 5000);
                return false;
            }

            const result = await this.$store.dispatch('assignProductParamsMassAction', {
                'router': this.$router,
                'data': {'inventory': productQuantity},
                'productIds': Object.keys(this.checkedItemsData).join(','),
                'filters': filters,
            });

            if(result.error != 1) {
                showMessage(this.$t('message.productHasBeenDisabled'), false, 3000);
                this.closeMassAction();

                let productIds = Object.keys(this.checkedItemsData);
                let data = structuredClone(toRaw(this.ProductsGridInfoData));
                if(productIds) {
                    _.each(productIds, function(prodId, ind) {
                        _.each(data, function(prodData, index) {
                            if(prodData.id == prodId) {
                                data[index]['inventory'] = productQuantity;
                            }
                        });
                    });
                }

                this.$store.commit('setProductsGridInfo', data);
            } else {
                showMessage(this.$t('message.canNotChangeProductInventory'), true, 5000);
            }
        },
        useInfinite(event)
        {
            let isChecked = event.target.checked;

            if (isChecked === true) {
                this.inventoryEl = false;
                this.inventory = '';
            } else {
                this.inventoryEl = true;
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
