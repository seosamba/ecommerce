import {mapGetters} from 'vuex';
import pagination from '../../pagination';
import moment from 'moment';

export default {
    data () {
        return {
            loadedForm: false,
            loadedGrid: false,
            websiteUrl: $('#website_url').val(),
            chosenProperty: '0',
            propertyDataEl: [],
            selectedGroup: '0',
            ruleName: '',
            actionsData: [],
            operators:{
                'equal': "Equal",
                'notequal': "Not equal",
                'like' : "Like"
            },
            placeholders: {
            },
            locale: $('#system-language-rule-groups').val(),
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
        },
        customerGroups: function() {
           return this.alphabeticalSort(this.$store.getters.getConfigScreenInfo.customerGroups);
        }
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
        addProperty: function(name)
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
        },
        deletePropertyData: function(index)
        {
            this.propertyDataEl.splice(index,1)
            this.chosenProperty = '0';
        },
        prepareDate: function(createdAt) {
            if (moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('DD MMMM YYYY HH:mm:ss') !== 'Invalid date') {
                return moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('DD')  + ' ' + moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('MMM') + ' ' + moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('YYYY');
            }
            return '';
        },
        resetForm: function()
        {
            this.actionsData = [];
            this.chosenProperty = '0';
            this.selectedGroup = 0;
            this.ruleName = '';
            this.propertyDataEl = [];
        },
        async addRule(e){
            if (this.propertyDataEl.length == '0') {
                showMessage(this.$t('message.specifyPropertyAction'), true, 2000);
                return false;
            }

            if (this.selectedGroup == '0') {
                showMessage(this.$t('message.specifyGroup'), true, 2000);
                return false;
            }

            if (this.ruleName == '') {
                showMessage(this.$t('message.specifyRuleName'), true, 2000);
                return false;
            }

            this.actionsData.push({
                'actionType': 'assign_group',
                'customer_group_id' : this.selectedGroup
            });

            const result = await this.$store.dispatch('saveConfigData', {
                'ruleName':this.ruleName,
                'fieldsData':this.propertyDataEl,
                'actionsData': this.actionsData
            });

            if (result.status === 'error') {
                showMessage(result.message, true, 2000);
                return false;
            } else {
                this.resetForm();
                showMessage(result.message, false, 2000);
                const resultConfigData = await this.$store.dispatch('getConfigSavedData', {'router':this.$router});
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
                    const resultConfigData = await this.$store.dispatch('getConfigSavedData', {'router':this.$router});
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

        const result = await this.$store.dispatch('getConfigSavedData', {'router':this.$router});
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
