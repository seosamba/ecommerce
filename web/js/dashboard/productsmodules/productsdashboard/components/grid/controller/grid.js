import {mapGetters} from 'vuex';
import lodash from 'lodash';
import localeMapping from '../../../localizationLanguages';
import pagination from '../../pagination';
import productsgridtable from '../../productsgridtable';
import moment from 'moment';
import { isProxy, toRaw } from 'vue';
export default {
    data () {
        return {
            loadedScreen: false,
            websiteUrl: $('#website_url').val(),
            localeMapping: localeMapping,
            locale: $('#dashboard-system-language').val(),
            filterByBrands:[],
            filterByTags:[],
            filterByStock:[],
            searchTerm:'',
            quickLeadExistingLeadId:0,
            urlPredefinedFilterParams:[],







        }
    },
    components: {
        productsgridtable: productsgridtable,
    },
    computed: {
        ...mapGetters({
            configDataInfo:'getConfigDataInfo',
            additionalInfo:'getAdditionalInfo',
            truncateText: 'truncateText',
            sortByColumn: 'sortByColumn',
        }),
    },
    watch: {

    },
    methods: {

        async resetSearchBar()
        {
            this.searchTerm = '';
        },

        goToProfile()
        {
          if (this.quickLeadExistingLeadId > 0) {
              this.$router.push({ name: 'lead', params: {'id': this.quickLeadExistingLeadId}, query:{'tabName': 'timeline'}});
          }
        },


        getParams(pathParams) {
            let result = {},
                tmpData = [];

            pathParams
                .split("&")
                .forEach(function (item) {
                    tmpData = item.split("=");
                    if (tmpData[0] !== '') {
                        result[decodeURIComponent(tmpData[0])] = decodeURIComponent(tmpData[1]);
                    }
                });
            return result;
        }
    },
    async created(){
        if (typeof this.localeMapping[this.locale] !== 'undefined') {
            this.$i18n.locale = this.localeMapping[this.locale];
        }


        const result = await this.$store.dispatch('getGeneralProductsScreenData', {'router':this.$router});
        if(result.status === 'error') {
            showMessage('Please re-login', true, 3000);
        } else {
            let urlParamsString = window.location.search;

            if (urlParamsString !== '' && urlParamsString.indexOf('?') > -1) {
                this.urlPredefinedFilterParams = this.getParams(urlParamsString.replace('?', ''));
                //this.processUrlPredefinedParams();
            } else {
                this.urlPredefinedFilterParams = [];
            }

            // this.reprocessFilterFields();
            // this.reprocessPreset();


            this.loadedScreen = true;
        }

    },
    async updated() {
        this.$nextTick(function () {
            //this.processPhoneMobileCountryCodesSelectors();
            if (typeof checkboxRadioStyle !== 'undefined' && typeof checkboxRadioStyle() === "function") {
                checkboxRadioStyle();
            }
        })
    }
}
