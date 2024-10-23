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
            priceSign:'plus',
            priceType:'percent',
            nonTaxable:0,
            configId: 0,
            defaultGroupId:0,
            websiteUrl: $('#website_url').val(),
            localeMapping: localeMapping,
            locale: $('#system-language-customer-groups-config').val(),
            formProcessing: false
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
                this.groupName = result.groupName;
                this.priceValue = result.priceValue;
                this.priceSign = result.priceSign;
                this.priceType = result.priceType;
                this.nonTaxable = result.nonTaxable;

                this.loadedGrid = false;
                this.loadedDetails = true;
            }

        },
        async saveConfig(e){
            if (this.formProcessing === true) {
                return false;
            }

            this.formProcessing = true;

            if (this.groupName === '') {
                showMessage(this.$t('message.pleaseSpecifyGroupName'), true, 2000);
                this.formProcessing = false;
                return false;
            }

            if (this.priceValue === '') {
                showMessage(this.$t('message.pleaseSpecifyPriceValue'), true, 2000);
                this.formProcessing = false;
                return false;
            }

            const result = await this.$store.dispatch('saveConfigData', {
                'groupName':this.groupName,
                'priceValue':this.priceValue,
                'priceSign':this.priceSign,
                'priceType':this.priceType,
                'nonTaxable':this.nonTaxable,
            });

            this.formProcessing = false;

            if (result.error == '1') {
                showMessage(result.message, true, 2000);
                return false;
            } else {
                showMessage(result.message, false, 2000);
                this.loadData();
            }
        },
        async updateConfig(configId) {
            if (this.formProcessing === true) {
                return false;
            }

            this.formProcessing = true;

            if (this.groupName === '') {
                showMessage(this.$t('message.pleaseSpecifyGroupName'), true, 2000);
                this.formProcessing = false;
                return false;
            }

            if (this.priceValue === '') {
                showMessage(this.$t('message.pleaseSpecifyPriceValue'), true, 2000);
                this.formProcessing = false;
                return false;
            }

            const result = await this.$store.dispatch('updateConfigData', {
                'configId':configId,
                'groupName':this.groupName,
                'priceValue':this.priceValue,
                'priceSign':this.priceSign,
                'priceType':this.priceType,
                'nonTaxable':this.nonTaxable,
            });

            this.formProcessing = false;

            if (result.error == '1') {
                showMessage(result.message, true, 2000);
                return false;
            } else {
                showMessage(result.message, false, 2000);
            }
        },
        async deleteConfigItem(configId){
            let self = this;

            showConfirm(this.$t('message.areYouSure'), async () => {
                const result = await this.$store.dispatch('deleteConfigRecord', {'id': configId});

                if (result.status === 'error') {
                    showMessage(result.message, true, 2000);
                    return false;
                } else {
                    showMessage(result.message, false, 2000);
                    self.loadData();
                }
            });
        },
        async changeDefaultGroup()
        {
            const result = await this.$store.dispatch('changeDefaultGroup', {'router':this.$router, 'defaultGroupId':this.defaultGroupId});
            showMessage(this.$t('message.changed'), false, 2000);
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
                if (typeof this.additionalInfo.defaultGroupId === 'undefined' || !this.additionalInfo.defaultGroupId) {
                    this.defaultGroupId = 0;
                } else {
                    this.defaultGroupId = this.additionalInfo.defaultGroupId;
                }

                this.resetParams();
            }
        },
        resetParams(){
            this.groupName = '';
            this.priceValue = '';
            this.priceSign = 'plus';
            this.priceType = 'percent';
            this.nonTaxable = 0;
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
