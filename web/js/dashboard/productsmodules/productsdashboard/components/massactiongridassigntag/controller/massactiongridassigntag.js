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
            productTags:[],
            usedTags:{},
            filter:'',
            itemsQuantity:0,
            checkedTags:[],
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
            let self = this;

            if (Object.keys(filters).length === 0) {
                filters = {};
            }

            const result = await this.$store.dispatch('assignProductParamsMassAction', {
                'router': this.$router,
                'data': {'tags': this.checkedTags},
                'productIds': Object.keys(this.checkedItemsData).join(','),
                'filters': filters,
            });

            if(result.error != 1) {
                showMessage(this.$t('message.done'), false, 3000);
                this.closeMassAction();

                let productIds = Object.keys(this.checkedItemsData);
                let data = toRaw(this.ProductsGridInfoData);
                if(productIds) {
                    _.each(productIds, function(prodId, ind) {
                        _.each(data, function(prodData, index) {
                            if(prodData.id == prodId) {
                                data[index]['tags'] = self.checkedTags;
                            }
                        });
                    });
                }

                this.$store.commit('setProductsGridInfo', data);
            } else {
                showMessage(this.$t('message.canNotAssignTemplate'), true, 5000);
            }
        },
        async getProductTags()
        {
            let checkedProductsIds = Object.keys(this.checkedItemsData);
            let data = toRaw(this.ProductsGridInfoData);
            let gridInfoTags = toRaw(this.gridInfo.tags);
            let self = this;

            _.each(data, function(prod){
                if (checkedProductsIds.includes(prod.id)) {
                    _.each(prod.tags, function(tag){
                        if (!_.has(self.usedTags, tag.name)){
                            self.usedTags[tag.name] = 1;
                            self.checkedTags.push({'id':tag.id, 'name': tag.name});
                        } else{
                            self.usedTags[tag.name] += 1;
                        }
                    });
                }
            });

            this.productTags = gridInfoTags;
            this.itemsQuantity = parseInt(Object.keys(this.checkedItemsData).length);
        },
        async getInitialData()
        {
            if (Object.keys(this.checkedItemsData).length === 0) {
                showMessage(this.$t('message.pleaseChooseAtLeastOneProduct'), true, 3000);
                this.closeMassAction();
            }

            await this.getProductTags();

            if (this.itemsQuantity > 0) {
                this.loadedScreen = true;
            }
        },
        async markTag(event, tagId, tagName)
        {
            let isChecked = event.target.checked,
                self = this;

            if (isChecked === true) {
                self.checkedTags.push({'id':tagId, 'name': tagName});
            } else {
                const index = self.checkedTags.findIndex(item => item.id === tagId);
                if (index !== -1) {
                    self.checkedTags.splice(index, 1);
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
