import {mapGetters} from 'vuex';
import pagination from '../../pagination';
import moment from 'moment';

export default {
    data () {
        return {
            loadedForm: false,
            loadedGrid: false,
            websiteUrl: $('#website_url').val(),
            param_type: 'text',
            param_name: '',
            label: '',
            locale: $('#system-language-product-custom-fields').val(),
            localeMapping: {
                'en':'en',
                'en_US':'en',
                'es':'es',
                'es_ES':'es',
                'fr':'fr',
                'fr_FR':'fr'
            }
        }
    },
    components: {
        pagination: pagination
    },
    computed: {
        configDataInfo: function() {
            return this.$store.getters.getConfigDataInfo;
        }
    },
    methods: {
        alphabeticalSort: function(obj){
            // convert object into array
            var sortable=[];
            for(var key in obj)
                if(obj.hasOwnProperty(key))
                    sortable.push([key, obj[key]]); // each item is an array in format [key, value]

            // sort items by value
            sortable.sort(function(a, b)
            {
                var x=a[1].toLowerCase(),
                    y=b[1].toLowerCase();
                return x<y ? -1 : x>y ? 1 : 0;
            });
            return sortable; // array in format [ [ key1, val1 ], [ key2, val2 ], ... ]
        },
        resetForm: function()
        {
            this.param_type = 'text';
            this.param_name = '';
            this.label = '';
        },
        async addCustomField(e){
            if (this.param_type == '') {
                showMessage(this.$t('message.specifyParamType'), true, 2000);
                return false;
            }

            if (this.param_name == '') {
                showMessage(this.$t('message.specifyParamName'), true, 2000);
                return false;
            }

            if (this.label == '') {
                showMessage(this.$t('message.specifLabel'), true, 2000);
                return false;
            }

            const result = await this.$store.dispatch('saveConfigData', {
                'param_type': this.param_type,
                'param_name':this.param_name,
                'label':this.label
            });

            if (result.status === 'error') {
                showMessage(result.message, true, 2000);
                return false;
            } else {
                this.resetForm();
                showMessage(result.message, false, 2000);
                const resultConfigData = await this.$store.dispatch('getProductConfigSavedData', {'router':this.$router});
                if(result.status === 'error') {

                } else {
                    this.loadedGrid = true;
                }
            }
        },
        goToRuleDetailScreen: function (ruleId)
        {
            this.$router.push({ name: 'rulesdetails', params: {'id': ruleId}});
        },
        async deleteConfigItem(id){
            showConfirm(this.$t('message.actionConfirmation'), async () => {
                const result = await this.$store.dispatch('deleteConfigRecord', {'id': id});

                if (result.status === 'error') {
                    showMessage(result.message, true, 2000);
                    return false;
                } else {
                    showMessage(result.message, false, 2000);
                    const resultConfigData = await this.$store.dispatch('getProductConfigSavedData', {'router':this.$router});
                    if(result.status === 'error') {

                    } else {
                        this.loadedGrid = true;
                    }
                }
            });
        },
        async updateCustomFieldNameLabel(id, oldName, oldLabel, fieldRow, event) {
            if(fieldRow == 'label') {
                var customFieldName = oldName;
                var customFieldLabel = event.target.value;
            } else {
                var customFieldName = event.target.value;
                var customFieldLabel = oldLabel;
            }

            let customFieldNameFiltered = customFieldName.replace(/[^a-zA-Z0-9'-_ ]/g, '');
            let customFieldLabelFiltered = customFieldLabel.replace(/[^a-zA-Z0-9'-_ ]/g, '');

            if(customFieldNameFiltered.length < 1) {
                showMessage(this.$t('message.emptyName'), true, 2000);
                event.target.value = oldName;
                return false;
            } else if(customFieldLabelFiltered.length < 1) {
                showMessage(this.$t('message.emptyLabel'), true, 2000);
                event.target.value = oldLabel;
                return false;
            } else {
                const result = await this.$store.dispatch('updateFieldNameLabel', {'id': id, 'customFieldName': customFieldNameFiltered, 'customFieldLabel': customFieldLabelFiltered});

                if (result.status === 'error') {
                    if(fieldRow == 'label') {
                        event.target.value = oldLabel;
                    } else {
                        event.target.value = oldName;
                    }
                    showMessage(result.message, true, 2000);
                    return false;
                } else {
                    if(fieldRow == 'label') {
                        event.target.value = customFieldLabelFiltered;
                    } else {
                        event.target.value = customFieldNameFiltered;
                    }
                    showMessage(result.message, false, 2000);
                    return true;
                }
            }
        },
    },
    async created(){
        this.$i18n.locale = this.localeMapping[this.locale];

        const result = await this.$store.dispatch('getProductConfigSavedData', {'router':this.$router});
        console.log('created', result);
        if(result.status === 'error') {
            showMessage(this.$t('message.relogin'), true, 3000);
        } else {
            this.loadedForm = true;
        }
        if(result.status === 'error') {

        } else {
            this.loadedGrid = true;
        }
    },
    async updated() {
        this.$nextTick(function () {
            if (typeof checkboxRadioStyle !== 'undefined' && typeof checkboxRadioStyle() === "function") {
                checkboxRadioStyle();
            }
        })
    }
}
