import {mapGetters} from 'vuex';
import lodash from 'lodash';
import moment from 'moment';
export default {
    data () {
        return {
            loaded: false,
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
            ruleId : '',
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

    },
    computed: {
        configScreenInfo: function() {
            return this.$store.getters.getConfigScreenInfo;
        },
        customerGroups: function() {
            return this.alphabeticalSort(this.$store.getters.getConfigScreenInfo.customerGroups);
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
        /*deletePropertyData: function(index)
        {
            this.propertyDataEl.splice(index,1)
            this.chosenProperty = '0';
        },*/
        /*prepareDate: function(createdAt) {
            if (moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('DD MMMM YYYY HH:mm:ss') !== 'Invalid date') {
                return moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('DD')  + ' ' + moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('MMM') + ' ' + moment(createdAt, 'YYYY-MM-DD HH:mm:ss').format('YYYY');
            }
            return '';
        },*/
        goToRulesScreen: function()
        {
            this.$router.push({ name: 'index'});
        },
        async updateRule()
        {
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

            this.actionsData= [];
            this.actionsData.push({
                'actionType': 'assign_group',
                'customer_group_id' : this.selectedGroup
            });

            let ruleData = {
                'router':this.$router,
                'ruleId' : this.ruleId,
                'ruleName':this.ruleName,
                'fieldsData':this.propertyDataEl,
                'actionsData': this.actionsData
            };

            const result = await this.$store.dispatch('updateConfigData', ruleData);
            if (result.status === 'error') {
                showMessage(result.message, true, 2000);
            } else {
                showMessage(result.message, false, 2000);
            }
        }
    },
    async created(){

        this.$i18n.locale = this.localeMapping[this.locale];
        this.ruleId = this.$route.params.id;
        const result = await this.$store.dispatch('getRuleConfig', {
            'router': this.$router,
            'ruleId': this.ruleId
        });

        if(result.status === 'error') {
            this.$router.push({ name: 'login'});
        } else {
            this.loaded = true;

            let fieldsData = result.rulesData.fieldsData;
            let actionsData = result.rulesData.actionsData;
            let self = this;

            for (var key in fieldsData) {
                self.propertyDataEl.push({
                    'name': fieldsData[key]['field_name'],
                    'operators': this.operators,
                    'value' : fieldsData[key]['field_value'],
                    'label' : fieldsData[key]['field_name'],
                    'operator': fieldsData[key]['rule_comparison_operator'],
                    'placeholder' : this.placeholders[fieldsData[key]['field_name']]
                });
            };

            for (var key in actionsData) {
                if (actionsData[key]['action_type'] === 'assign_group') {
                    self.selectedGroup = JSON.parse(actionsData[key]['action_config'])['customer_group_id'];
                }
            }

            self.ruleName = result.rulesData.rule_name;

            if (typeof flexkit !== 'undefined' && typeof flexkit.chooseBoxStyle() === "function") {
                flexkit.chooseBoxStyle();
            }
        }

    }
}
