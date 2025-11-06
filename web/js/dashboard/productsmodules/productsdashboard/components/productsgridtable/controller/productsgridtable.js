import {mapGetters} from 'vuex';
import lodash from 'lodash';
import localeMapping from '../../../localizationLanguages';
import pagination from '../../pagination';
//import massactiongridsendsms from '../../massactiongridsendsms';
//import massactiongridaddtags from '../../massactiongridaddtags';

import { isProxy, toRaw } from 'vue';
export default {
    data () {
        return {
            loadedScreen: false,
            websiteUrl: $('#website_url').val(),
            searchData:'',
            planId:0,
            localeMapping: localeMapping,
            locale: $('#dashboard-system-language').val(),
            perPageDataValues : {
                '10':10,
                '20':20,
                '50':50,
                '100':100
            },




            columnsHeaders: {},
            listOfDefaultColumnNamesOrder: [],
            options: [],
            columnSelectionModel:[],
            tableColumnOrderName:'',
            restrictColumnEditing:'0',
            columnsModified:false,
            applyColumnOrderToAllUsers:'',
            userPresetsList: [],
            presetId:0,
            currentPresetId:0,
            newPresetName:'',
            presetDefault:false,
            stages:{},
            customerGroups:{},
            leadTypes:{},
            userTimezoneOffset:'',
            displayCallDialog:false,
            activeDialogId:0,
            callDialogLeadName:'',
            callDialogCountryCodeMobile:'',
            callDialogCountryCodeDesktop:'',
            callDialogMobile:'',
            callDialogDeviceFrom:'mobile',
            callDialogDeviceTo:'mobile',
            callDialogNumber:'',
            callDialogCountryCodeChosen:'',
            urlPredefinedFilterParams:[],
            scrollBarStyle:'',
            initialScrollBarSize:'',
            currentScrollBarSize:''
        }
    },
    components: {
        pagination: pagination,
        //massactiongridsendsms:massactiongridsendsms,
        //massactiongridaddtags:massactiongridaddtags,

    },
    computed: {
        ...mapGetters({
            formatDate: 'formatDate',
            formatOnlyDate: 'formatOnlyDate',
            formatTimeOnly: 'formatTimeOnly',
            configDataInfo:'getConfigDataInfo',
            additionalInfo:'getAdditionalInfo',
            filterData:'getFilterData',
            //sortByColumn: 'sortByColumn',
            truncateText: 'truncateText',
            //changeFilter: 'getChangeFilter',
            unescapeValue:'unescapeValue',
            ProductsGridInfoData: 'getProductsGridInfo',
            leadsGridAdditionalInfoData: 'getLeadGridAdditionalInfo',
            checkedItems:'getCheckedItems',
            activeMassAction:'getActiveMassAction',
            massActionActive:'getIsMassActionActive',
            //
            salesStats:'getSalesStats',
            opportunitiesStats:'getOpportunitiesStats',
            toCurrency:'toCurrency',
            //updateOppStats:'getUpdateOppStats',
            activeGridTablePreset:'getActiveGridTablePreset',
            //applyFilterRemote:'getApplyFilterRemote',
            pagerState: 'getPagerState',
        })
    },
    watch: {
        applyFilterRemote()
        {
            this.applyFilter();
        },
        changeFilter (newData, originalData) {
            this.$store.commit('setPaginationData',
            {
                productsGrid: {
                    currentPage: 1,
                    itemsPerPage: this.pagerState('productsGrid').itemsPerPage,
                    totalItems: 0,
                    visiblePages: 4
                }
            });

            if (typeof newData.searchData !== 'undefined') {
                if (isProxy(newData.searchData)) {
                    let filterDataObject = {};
                    if (newData.searchData.filterBy !== 'undefined') {
                        filterDataObject['filter-by'] = toRaw(newData.searchData.filterBy);
                    }

                    if (newData.searchData.filterOrderBy !== 'undefined') {
                        filterDataObject['interaction_value'] = toRaw(newData.searchData.filterOrderBy);
                    }

                    if (newData.searchData.filterIncludeCustomLeadFields !== 'undefined') {
                        filterDataObject['search_includes_custom_lead_fields'] = toRaw(newData.searchData.filterIncludeCustomLeadFields);
                    }

                    if (newData.searchData.filterIncludeCustomNotes !== 'undefined') {
                        filterDataObject['search_includes_notes'] = toRaw(newData.searchData.filterIncludeCustomNotes);
                    }

                    if (newData.searchData.filterIncludeCallTranscript !== 'undefined') {
                        filterDataObject['search_includes_call_transcript'] = toRaw(newData.searchData.filterIncludeCallTranscript);
                    }

                    if (newData.searchData.filterCreationDateFrom !== 'undefined') {
                        filterDataObject['created_at_from'] = toRaw(newData.searchData.filterCreationDateFrom);
                    }

                    if (newData.searchData.filterCreationDateTo !== 'undefined') {
                        filterDataObject['created_at_to'] = toRaw(newData.searchData.filterCreationDateTo);
                    }

                    if (newData.searchData.filterActivityDateFrom !== 'undefined') {
                        filterDataObject['next_activity_from'] = toRaw(newData.searchData.filterActivityDateFrom);
                    }

                    if (newData.searchData.filterActivityDateTo !== 'undefined') {
                        filterDataObject['next_activity_to'] = toRaw(newData.searchData.filterActivityDateTo);
                    }

                    if (newData.searchData.filterSequenceDateFrom !== 'undefined') {
                        filterDataObject['last_sequence_from'] = toRaw(newData.searchData.filterSequenceDateFrom);
                    }

                    if (newData.searchData.filterSequenceDateTo !== 'undefined') {
                        filterDataObject['last_sequence_to'] = toRaw(newData.searchData.filterSequenceDateTo);
                    }

                    if (newData.searchData.filterOpportunityDateFrom !== 'undefined') {
                        filterDataObject['opportunity_date_from'] = toRaw(newData.searchData.filterOpportunityDateFrom);
                    }

                    if (newData.searchData.filterOpportunityDateTo !== 'undefined') {
                        filterDataObject['opportunity_date_to'] = toRaw(newData.searchData.filterOpportunityDateTo);
                    }

                    if (newData.searchData.creationAt !== 'undefined') {
                        filterDataObject['creation_at'] = toRaw(newData.searchData.creationAt);
                    }

                    if (newData.searchData.stateChangeAt !== 'undefined') {
                        filterDataObject['state_change_at'] = toRaw(newData.searchData.stateChangeAt);
                    }

                    if (newData.searchData.expectedAt !== 'undefined') {
                        filterDataObject['expected_at'] = toRaw(newData.searchData.expectedAt);
                    }

                    if (newData.searchData.filterActivities !== 'undefined') {
                        filterDataObject['activity_id'] = toRaw(newData.searchData.filterActivities);
                    }

                    if (newData.searchData.lastUpdatedFlag !== 'undefined') {
                        filterDataObject['last_updated'] = toRaw(newData.searchData.lastUpdatedFlag);
                    }

                    if (newData.searchData.searchTerm !== 'undefined') {
                        filterDataObject['lead-attribute'] = toRaw(newData.searchData.searchTerm);
                    }

                    if (newData.searchData.filterLifecycles !== 'undefined') {
                        filterDataObject['stage_id'] = toRaw(newData.searchData.filterLifecycles);
                    }

                    if (newData.searchData.emailInteractionActivitySubtype !== 'undefined') {
                        filterDataObject['outbound_emails_sub_type'] = toRaw(newData.searchData.emailInteractionActivitySubtype);
                    }

                    if (newData.searchData.filterByTaskStatus !== 'undefined') {
                        filterDataObject['lead_task'] = toRaw(newData.searchData.filterByTaskStatus);
                    }

                    if (newData.searchData.filterByCountry !== 'undefined') {
                        filterDataObject['lead_country'] = toRaw(newData.searchData.filterByCountry);
                    }

                    if (newData.searchData.filterByCountryState !== 'undefined') {
                        filterDataObject['lead_country_state'] = toRaw(newData.searchData.filterByCountryState);
                    }

                    if (newData.searchData.filterByIndustry !== 'undefined') {
                        filterDataObject['industry_id'] = toRaw(newData.searchData.filterByIndustry);
                    }

                    if (newData.searchData.filterByEmailSequence !== 'undefined') {
                        filterDataObject['email_sms_sequences'] = toRaw(newData.searchData.filterByEmailSequence);
                    }

                    if (newData.searchData.filterBySequenceType !== 'undefined') {
                        filterDataObject['email_sequence_type'] = toRaw(newData.searchData.filterBySequenceType);
                    }

                    if (newData.searchData.filterBySequenceTypeAction !== 'undefined') {
                        filterDataObject['email_sequence_type_action'] = toRaw(newData.searchData.filterBySequenceTypeAction);
                    }

                    if (newData.searchData.filterByOpportunityOwnerId !== 'undefined') {
                        filterDataObject['opportunity_owner_id'] = toRaw(newData.searchData.filterByOpportunityOwnerId);
                    }

                    if (newData.searchData.filterByTotalOpportunityAmountFrom !== 'undefined') {
                        filterDataObject['total_opportunity_amount_from'] = toRaw(newData.searchData.filterByTotalOpportunityAmountFrom);
                    }

                    if (newData.searchData.filterByTotalOpportunityAmountTo !== 'undefined') {
                        filterDataObject['total_opportunity_amount_to'] = toRaw(newData.searchData.filterByTotalOpportunityAmountTo);
                    }

                    if (newData.searchData.filterByTotalSpentPurchaseAmountFrom !== 'undefined') {
                        filterDataObject['total_spent_purchase_amount_from'] = toRaw(newData.searchData.filterByTotalSpentPurchaseAmountFrom);
                    }

                    if (newData.searchData.filterByTotalSpentPurchaseAmountTo !== 'undefined') {
                        filterDataObject['total_spent_purchase_amount_to'] = toRaw(newData.searchData.filterByTotalSpentPurchaseAmountTo);
                    }

                    if (newData.searchData.filterLeadOwnerId !== 'undefined') {
                        filterDataObject['owner_id'] = toRaw(newData.searchData.filterLeadOwnerId);
                    }

                    if (newData.searchData.filterByGroup !== 'undefined') {
                        filterDataObject['customer_group_id'] = toRaw(newData.searchData.filterByGroup);
                    }

                    if (newData.searchData.filterByTags !== 'undefined') {
                        filterDataObject['lead_tags'] = toRaw(newData.searchData.filterByTags);
                    }

                    if (newData.searchData.filterBySourceTypes !== 'undefined') {
                        filterDataObject['lead_source_type_and_utm'] = toRaw(newData.searchData.filterBySourceTypes);
                    }

                    if (newData.searchData.filterBySourceUtmValues !== 'undefined') {
                        filterDataObject['lead_utm_source_value_id'] = toRaw(newData.searchData.filterBySourceUtmValues);
                    }

                    if (newData.searchData.filterByCampaignUtmValues !== 'undefined') {
                        filterDataObject['lead_utm_campaign_value_id'] = toRaw(newData.searchData.filterByCampaignUtmValues);
                    }

                    if (newData.searchData.filterByCity !== 'undefined') {
                        filterDataObject['lead_city'] = toRaw(newData.searchData.filterByCity);
                    }

                    if (newData.searchData.filterByTitleOperator !== 'undefined') {
                        filterDataObject['lead_title_operator'] = toRaw(newData.searchData.filterByTitleOperator);
                    }

                    if (newData.searchData.byLeadTitle !== 'undefined') {
                        filterDataObject['lead_titles'] = toRaw(newData.searchData.byLeadTitle);
                    }

                    if (newData.searchData.filterByOpportunityStatus !== 'undefined') {
                        filterDataObject['lead_opportunity_stages'] = toRaw(newData.searchData.filterByOpportunityStatus);
                    }

                    if (newData.searchData.filterByScoreFrom !== 'undefined') {
                        filterDataObject['lead_score_from'] = toRaw(newData.searchData.filterByScoreFrom);
                    }

                    if (newData.searchData.filterByScoreTo !== 'undefined') {
                        filterDataObject['lead_score_to'] = toRaw(newData.searchData.filterByScoreTo);
                    }

                    if (newData.searchData.filterBySentimentScoreFrom !== 'undefined') {
                        filterDataObject['lead_sentiment_score_from'] = toRaw(newData.searchData.filterBySentimentScoreFrom);
                    }

                    if (newData.searchData.filterBySentimentScoreTo !== 'undefined') {
                        filterDataObject['lead_sentiment_score_to'] = toRaw(newData.searchData.filterBySentimentScoreTo);
                    }

                    if (newData.searchData.filterByLeadType !== 'undefined') {
                        filterDataObject['lead_source_type'] = toRaw(newData.searchData.filterByLeadType);
                    }

                    if (newData.searchData.filterBySignCallDuration !== 'undefined') {
                        filterDataObject['more_less_call_duration'] = toRaw(newData.searchData.filterBySignCallDuration);
                    }

                    if (newData.searchData.filterByCallDuration !== 'undefined') {
                        filterDataObject['call_duration'] = toRaw(newData.searchData.filterByCallDuration);
                    }

                    if (newData.searchData.filterByLeadSource !== 'undefined') {
                        filterDataObject['lead_source'] = toRaw(newData.searchData.filterByLeadSource);
                    }

                    if (newData.searchData.filterByExcludeTags !== 'undefined') {
                        filterDataObject['lead_tags_exclude'] = toRaw(newData.searchData.filterByExcludeTags);
                    }

                    if (newData.searchData.filterByLeadZip !== 'undefined') {
                        filterDataObject['lead_zip'] = toRaw(newData.searchData.filterByLeadZip);
                    }

                    if (newData.searchData.filterByNpsScoreOperator !== 'undefined') {
                        filterDataObject['lead_filter_nps_score_operator'] = toRaw(newData.searchData.filterByNpsScoreOperator);
                    }

                    if (newData.searchData.filterByNpsScore !== 'undefined') {
                        filterDataObject['nps_score'] = toRaw(newData.searchData.filterByNpsScore);
                    }

                    if (newData.searchData.filterByEmailValidation !== 'undefined') {
                        filterDataObject['email_validation_status'] = toRaw(newData.searchData.filterByEmailValidation);
                    }

                    if (newData.searchData.filterEmailValidationDatepicker !== 'undefined') {
                        filterDataObject['validated_date_to'] = toRaw(newData.searchData.filterEmailValidationDatepicker);
                    }

                    if (newData.searchData.filterByOpportunityType !== 'undefined') {
                        filterDataObject['opportunity_type_id'] = toRaw(newData.searchData.filterByOpportunityType);
                    }

                    if (newData.searchData.filterByEmailStatus !== 'undefined') {
                        filterDataObject['email_status'] = toRaw(newData.searchData.filterByEmailStatus);
                    }

                    if (newData.searchData.filterByLastInteractionType !== 'undefined') {
                        filterDataObject['last_interaction_id'] = toRaw(newData.searchData.filterByLastInteractionType);
                    }

                    if (newData.searchData.filterLeadsCustomParams !== 'undefined') {
                        filterDataObject['custom_params_search'] = toRaw(newData.searchData.filterLeadsCustomParams);
                    }

                    if (newData.searchData.filterOrganizationsCustomParams !== 'undefined') {
                        filterDataObject['custom_organization_params_search'] = toRaw(newData.searchData.filterOrganizationsCustomParams);
                    }

                    if (newData.searchData.specialFilterType !== 'undefined') {
                        filterDataObject['special_filter_type'] = toRaw(newData.searchData.specialFilterType);
                    }

                    if (newData.searchData.rangeOptions !== 'undefined') {
                        filterDataObject['range_options'] = toRaw(newData.searchData.rangeOptions);
                    }

                    if (newData.searchData.switchLastNextActivityDate !== 'undefined') {
                        filterDataObject['switch_last_next_activity_date'] = toRaw(newData.searchData.switchLastNextActivityDate);
                    }

                    if (newData.searchData.emailSmsSequencesTypePeriod !== 'undefined') {
                        filterDataObject['email_sms_sequences_type_period'] = toRaw(newData.searchData.emailSmsSequencesTypePeriod);
                    }

                    this.searchData = filterDataObject;
                } else {
                    this.searchData = newData.searchData;
                }
            } else {
                this.searchData = '';
            }

            this.$store.commit('setFilterData', toRaw(this.searchData));

            this.applyFilter();
        },
        async updateOppStats (newData, originalData) {
            if (typeof newData !== 'undefined') {
                let leadIdsString = '';

                _.each(this.ProductsGridInfoData, function(leadInfo, index) {
                    leadIdsString = leadIdsString.concat(leadInfo.id, ",");
                });

                const resultStats = await this.$store.dispatch('getLeadStatsGridData', {'router':this.$router, 'leadIdsString' : leadIdsString});
            }
        },
        activeMassAction  (newData, originalData) {
            if (newData == '0') {
                this.$store.commit('setIsMassActionActive', false);
            } else {
                this.$store.commit('setIsMassActionActive', true);
            }
        }

    },
    methods: {
        async applyFilter(type) {



            const result = await this.$store.dispatch('getProductsGridData', {
                'router': this.$router,
                'searchData': this.filterData
            });

            if (result.status === 'error') {
                showMessage('Please re-login', true, 3000);
            } else {
                this.loadedScreen = true;
            }



        },
        async goToLeadDetailsScreen(id, tabName, subTabName)
        {
            let openTabName = tabName || '',
                openSubTabName = subTabName || '';

            if (openTabName !== '') {
                this.$router.push({ name: 'lead', params: {'id': id}, query:{'tabName': openTabName}});
            } else if (openSubTabName !== '') {
                this.$router.push({ name: 'lead', params: {'id': id}, query:{'subTabName': openSubTabName}});
            } else {
                this.$router.push({name: 'lead', params: {'id': id}, query: {'tabName': 'timeline'}});
            }
        },
        addRemoveItem: function(e, itemKey) {
            if (e.target.checked) {
                this.addCheckedItem(itemKey);
            } else {
                this.removeCheckedItem(itemKey);
            }

            this.forceUpdate();
        },
        addCheckedItem: function(itemKey) {
            let currentItems = this.checkedItems;
            if (_.isNull(currentItems)) {
                currentItems = {};
            }
            currentItems[itemKey] = itemKey;
            this.$store.commit('setCheckedItems', currentItems);
            this.$store.commit('setAllCheckedItemsTracking', {'items': currentItems});
        },
        removeCheckedItem: function(itemKey){
            let currentItems = this.checkedItems;
            if (currentItems !== null && currentItems.hasOwnProperty(itemKey)) {
                delete currentItems[itemKey];
                this.$store.commit('setCheckedItems', currentItems);
                this.$store.commit('setAllCheckedItemsTracking', {'items': currentItems});
            }
        },
        isCheckedItem: function(itemKey){
            let currentItems = this.checkedItems;

            if (currentItems !== null && currentItems.hasOwnProperty(itemKey)) {
                return true;
            }
            return false;
        },
        checkRemoveAllItemsOnPage: function(e)
        {
            let currentCheckedItems = this.checkedItems,
                productsGridInfo = this.ProductsGridInfoData;

            if (e.target.checked && productsGridInfo.length > 0) {
                productsGridInfo.forEach(function(productsGridInfo) {
                    currentCheckedItems[productsGridInfo.id] = productsGridInfo.id;
                });

            } else {
                productsGridInfo.forEach(function(productsGridInfo) {
                    delete currentCheckedItems[productsGridInfo.id];
                });
            }

            this.$store.commit('setCheckedItems', currentCheckedItems);
            this.$store.commit('setAllCheckedItemsTracking', {'items': currentCheckedItems});
            this.forceUpdate();
        },
        isAllChecked() {
            let currentCheckedItems = this.checkedItems,
                productsGridInfo = toRaw(this.ProductsGridInfoData),
                allKeyExists = true;

            productsGridInfo.forEach(function(productsGridInfo) {
                if (!currentCheckedItems.hasOwnProperty(productsGridInfo.id)) {
                    allKeyExists = false;
                }
            });

            return allKeyExists;
        },
        forceUpdate() {
            // ...
            this.$forceUpdate();  // Notice we have to use a $ here
            // ...
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

        let urlParamsString = window.location.search;

        if (urlParamsString !== '' && urlParamsString.indexOf('?') > -1) {
            this.loadedScreen = true;
        } else {
            this.applyFilter('firstLoad');
        }


    },
    async updated() {
        this.$nextTick(function () {
            if (typeof checkboxRadioStyle !== 'undefined' && typeof checkboxRadioStyle() === "function") {
                checkboxRadioStyle();
            }

            const table = document.getElementById('leads-grid-table');
            const tableWidth = table.offsetWidth;
            this.scrollBarStyle = 'width:'+tableWidth+'px;';
            this.currentScrollBarSize = tableWidth;

            $('.opportunities-block-scroll').on('scroll', function (e) {
                $('#leads-grid-table-dashboard-table-scroll').scrollLeft($('.opportunities-block-scroll').scrollLeft());
            });

            $('#leads-grid-table-dashboard-table-scroll').on('scroll', function (e) {
                $('.opportunities-block-scroll').scrollLeft($('#leads-grid-table-dashboard-table-scroll').scrollLeft());
            });
        })
    }
}
