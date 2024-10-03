import {mapGetters} from 'vuex';
import localeMapping from '../../../localizationLanguages';
import pagination from '../../pagination';
export default {
    data () {
        return {
            loadedGrid: false,
            loadedDetails:false,
            groupName: '',
            priceValue:'',
            priceSign:'+',
            configId: 0,
            websiteUrl: $('#website_url').val(),
            localeMapping: localeMapping,
            locale: $('#system-language-customer-groups-config').val()
        }
    },
    components: {
        pagination: pagination
    },
    computed: {
        ...mapGetters({
            configDataInfo:'getConfigDataInfo',
            additionalInfo:'getAdditionalInfo',
            truncateText: 'truncateText',
            sortByColumn: 'sortByColumn',
            filterData:'getFilterData',
        }),
    },
    methods: {
        async ruleDetails(configId) {

            const result = await this.$store.dispatch('getConfigSavedData', {'router':this.$router, 'configId':configId});

            if(result.status === 'error') {
                showMessage('Please re-login', true, 3000);
            } else {
                this.configId = result.id;
                this.groupName = result.preset_name;
                this.priceValue = result.task_title;
                this.priceSign = result.price_sign;

                this.loadedGrid = false;
                this.loadedDetails = true;
            }

        },
        async saveConfig(e){
            if (this.groupName === '') {
                showMessage(this.$t('message.pleaseGroupName'), true, 2000);
                return false;
            }

            if (this.priceValue === '') {
                showMessage(this.$t('message.pleaseSpecifyPriceValue'), true, 2000);
                return false;
            }

            const result = await this.$store.dispatch('saveConfigData', {'groupName':this.groupName, 'priceValue':this.priceValue, 'priceSign':this.priceSign});

            if (result.error == '1') {
                showMessage(result.message, true, 2000);
                return false;
            } else {
                showMessage(result.message, false, 2000);
                this.resetParams();

                const resultConfigData = await this.$store.dispatch('getConfigSavedData', {'router':this.$router});
                if(result.status === 'error') {

                } else {
                    this.loadedGrid = true;
                }
            }
        },
        async updateConfig(configId) {
            if (this.groupName === '') {
                showMessage(this.$t('message.pleaseSpecifyGroupName'), true, 2000);
                return false;
            }

            if (this.priceValue === '') {
                showMessage(this.$t('message.pleaseSpecifyPriceValue'), true, 2000);
                return false;
            }

            const result = await this.$store.dispatch('updateConfigData', {'configId':configId, 'groupName':this.groupName, 'priceValue':this.priceValue, 'priceSign':this.priceSign});

            if (result.error == '1') {
                showMessage(result.message, true, 2000);
                return false;
            } else {
                showMessage(result.message, false, 2000);
            }
        },
        async deleteConfigItem(configId){
            showConfirm(this.$t('message.areYouSure'), async () => {
                const result = await this.$store.dispatch('deleteConfigRecord', {'id': configId});

                if (result.status === 'error') {
                    showMessage(result.message, true, 2000);
                    return false;
                } else {
                    showMessage(result.message, false, 2000);
                    const resultConfigData = await this.$store.dispatch('getConfigSavedData', {'router':this.$router});
                    if(result.status === 'error') {

                    } else {
                        this.loadedGrid = true;
                    }
                }
            });
        },
        backToMainGrid() {
            this.loadData()
        },
        async loadData()
        {
            const result = await this.$store.dispatch('getConfigSavedData', {'router':this.$router});

            if(result.status === 'error') {
                showMessage('Please re-login', true, 3000);
            } else {
                this.loadedDetails = false;
                this.loadedGrid = true;
                this.resetParams();
            }
        },
        resetParams(){
            this.groupName = '';
            this.priceValue = '';
            this.priceSign = '+';
            this.configId = 0;
        }

    },
    async created(){
        if (typeof this.localeMapping[this.locale] !== 'undefined') {
            this.$i18n.locale = this.localeMapping[this.locale];
        }

        this.loadData();
    },
    async updated() {
        this.$nextTick(function () {
            if (typeof checkboxRadioStyle !== 'undefined' && typeof checkboxRadioStyle() === "function") {
                checkboxRadioStyle();
            }
        })
    }
}
