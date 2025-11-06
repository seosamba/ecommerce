import {mapGetters} from 'vuex';
import lodash from 'lodash';
import localeMapping from '../../../localizationLanguages';
import moment from 'moment';
import {toRaw} from "vue";



export default {
    data () {
        return {
            loadedScreen: false,
            websiteUrl: $('#website_url').val(),
            localeMapping: localeMapping,
            locale: $('#dashboard-system-language').val(),
            productId: 0,

        }
    },
    components: {

    },
    computed: {
        ...mapGetters({
            //productData:'getDetailedScreenProductData',
        }),
    },
    watch: {

    },
    methods: {
        backToGrid() {
            this.$router.push({ name: 'grid'});
        },



    },
    async created(){
        if (typeof this.localeMapping[this.locale] !== 'undefined') {
            this.$i18n.locale = this.localeMapping[this.locale];
        }

        // this.productId = this.$route.params.id;
        //
        // const result = await this.$store.dispatch('getProductDataInfo', {
        //     'router': this.$router,
        //     'id': this.productId
        // });
        //
        // if (typeof result.id === 'undefined') {
        //     this.$router.push({ name: 'grid'});
        // } else {
        //     this.loadedScreen = true;
        //
        //     console.log(result);
        // }

    },
    async updated() {
        this.$nextTick(function () {

            if (typeof checkboxRadioStyle !== 'undefined' && typeof checkboxRadioStyle() === "function") {
                checkboxRadioStyle();
            }
        })
    },
    mounted() {
        let vm = this;
        window.onpopstate = function(event) {
            if (event.state !== null) {
                let pathParams = decodeURI(event.state.current),
                    additionalParams = '',
                    updateNavigation = false;

                const regex = /(\btabName=[\w]*)/i;
                if (pathParams.indexOf('?') > -1 && pathParams.match(regex) && pathParams.match(regex).length >= 1) {
                    additionalParams = vm.getParams(pathParams.match(regex)[0].trim());
                    if (typeof additionalParams.tabName !== 'undefined' && vm.allowedTabNames.includes(additionalParams.tabName)) {
                        vm.activeTab = additionalParams.tabName;
                        updateNavigation = true;
                    }
                }

                const regexSubTab = /(\bsubTabName=[\w]*)/i;
                if (pathParams.indexOf('?') > -1 && pathParams.match(regexSubTab) && pathParams.match(regexSubTab).length >= 1) {
                    additionalParams = vm.getParams(pathParams.match(regexSubTab)[0].trim());
                    if (typeof additionalParams.subTabName !== 'undefined' && vm.allowedSubTabNames.includes(additionalParams.subTabName)) {
                        vm.activeSubTab = additionalParams.subTabName;
                        if (typeof vm.customTabsObject[vm.activeSubTab] !== 'undefined') {
                            vm.activeCustomTabId = vm.customTabsObject[vm.activeSubTab];
                        }
                        updateNavigation = true;
                    }
                }

                if (updateNavigation === true) {
                    vm.setAllTabs();
                }
            }
        }
    }


}
