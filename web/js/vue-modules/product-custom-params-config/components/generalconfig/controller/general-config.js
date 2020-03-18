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
        },
        configScreenInfo: function() {
            return this.$store.getters.getConfigScreenInfo;
        }/*,
        customerGroups: function() {
           return this.alphabeticalSort(this.$store.getters.getConfigScreenInfo.customerGroups);
        }*/
    },
    methods: {
        alphabeticalSort: function(obj){
            //debugger;
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
        /*addProperty: function(name)
        {
            if (name == "0") {
                return false;
            }

            let found = this.propertyDataEl.find(function(obj) {
                return obj.name == name;
            });

            if (typeof found === 'undefined') {
                this.propertyDataEl.push({
                    'name': name,
                    'operators': this.operators,
                    'value' : '',
                    'label' : name,
                    'operator': 'equal',
                    'placeholder' : this.placeholders[name]
                })
            }
        },*/
        /*deletePropertyData: function(index)
        {
            this.propertyDataEl.splice(index,1);
            this.param_type = 'text';
        },*/
        /*prepareDate: function(createdAt) {
            if (moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('DD MMMM YYYY HH:mm:ss') !== 'Invalid date') {
                return moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('DD')  + ' ' + moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('MMM') + ' ' + moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('YYYY');
            }
            return '';
        },*/
        resetForm: function()
        {
            this.param_type = 'text';
            this.param_name = '';
            this.label = '';
        },
        async addCustomField(e){
            //debugger;

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

            //debugger;

            const result = await this.$store.dispatch('saveConfigData', {
                'param_type': this.param_type,
                'param_name':this.param_name,
                'label':this.label
            });

           // debugger;
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
            //debugger;
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
        }
    },
    async created(){
        this.$i18n.locale = this.localeMapping[this.locale];

        const result = await this.$store.dispatch('getProductConfigSavedData', {'router':this.$router});
        console.log('created', result);
        if(result.status === 'error') {
            showMessage('Please re-login', true, 3000);
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
