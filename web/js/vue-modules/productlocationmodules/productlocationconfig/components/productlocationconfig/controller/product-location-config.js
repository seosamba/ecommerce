import {mapGetters} from 'vuex';
import localeMapping from '../../../localizationLanguages';

export default {
    data() {
        return {
            websiteUrl: $('#website_url').val(),
            localeMapping: localeMapping,
            locale: $('#system-language-general-config').val(),
            selectedLocations: [],
            productId: $('#product-id').val(),
            showLocationsBlock: false,
            newLocation: '',
            newLocationSelected: '',
            newInventory: '',
            productInventory: null,
            isDefaultProduct: false,
            isDefaultTipProduct: false,
        }
    },
    components: {},
    computed: {
        ...mapGetters({
            locationsData:'getLocationsData',
            savedLocations: 'getSavedLocations',
        }),
    },
    methods: {
        setNewLocation(e) {
            let newLocationId = e.id;
            var self = this;

            _.each(this.savedLocations, function (location, key) {
                if(newLocationId == location.location_id) {
                    showMessage(self.$t('message.locationAlreadyExists'), true, 2000);
                    self.newLocationSelected = [];
                    return false;
                }
            });

            if(typeof newLocationId !== 'undefined'){
                this.newLocation = newLocationId;
            }
        },
        changeLocation(e, id) {
            let newLocationId = e.id;
            var self = this,
                allowChangeLocation = true;

            _.each(this.savedLocations, function (location, key) {
                if(newLocationId == location.location_id) {
                    self.getProductLocationSData(self.productId, self.$router);
                    showMessage(self.$t('message.locationAlreadyExists'), true, 2000);
                    allowChangeLocation = false;
                    return false;
                }
            });

            if(allowChangeLocation) {
                showConfirm(this.$t('message.areYouSureUpdateLocation'), async () => {
                    this.changeSavedLocationInfo(this.$router, id, newLocationId);
                    this.getProductLocationSData(this.productId, this.$router);
                }, function() {
                    self.getProductLocationSData(self.productId, self.$router);
                });
            }
        },
        changeInventory(event, id, oldInventory) {
            event.preventDefault();
            let newInventory = event.target.value,
                locId = id;

            newInventory = newInventory.replace(/[^0-9]/g, '');

            showConfirm(this.$t('message.areYouSureUpdateInventory'), async () => {
                if(newInventory.length < 1) {
                    //showMessage(this.$t('message.notEmptyNewInventory'), true, 2000);
                    //return false;
                    newInventory = 0;
                }

                if(this.productInventory != null && parseInt(this.productInventory) < parseInt(newInventory)) {
                    showMessage(this.$t('message.globalInventoryLess'), true, 2000);
                    event.target.value = oldInventory;
                    return false;
                }

                var allsavedLocationsInventories = 0;
                _.each(this.savedLocations, function (location, key) {
                    if(locId != location.id) {
                        allsavedLocationsInventories += parseInt(location.inventory);
                    }
                });

                if((allsavedLocationsInventories + parseInt(newInventory)) > parseInt(this.productInventory)) {
                    showMessage(this.$t('message.globalInventoryLess'), true, 2000);
                    event.target.value = oldInventory;
                    return false;
                }

                this.changeSavedLocationInfo(this.$router, id, 0, newInventory);
            }, function() {
                event.target.value = oldInventory;
            });
        },
        changeAppAccessStatus(event, id){
            let checkedDefault = event.target.checked,
                message = this.$t('message.areYouSureEnabled');

            if (checkedDefault === false) {
                message = this.$t('message.areYouSureDisabled');
            }

            showConfirm(message, async () => {
                let allowToChangeDefaultLocation = true,
                    checkedLocationName = '';
                if (checkedDefault === true) {
                    _.each(this.savedLocations, function (location, key) {
                        if(id != location.id && location.is_default_location == 1) {
                            allowToChangeDefaultLocation = false;
                            checkedLocationName = location.name;
                        }
                    });

                    checkedDefault = '1';
                } else {
                    checkedDefault = '0';
                }

                if(!allowToChangeDefaultLocation) {
                    showMessage('"' +checkedLocationName + '"' + this.$t('message.otherLocationChecked') + this.$t('message.pleaseUncheckDefaultLocationFirst'), true, 2000);
                    event.target.checked = false;
                    return false;
                }

                this.changeSavedLocationInfo(this.$router, id, 0, '', checkedDefault);
                this.getProductLocationSData(this.productId, this.$router);
            }, function() {
                if(checkedDefault === true) {
                    event.target.checked = false;
                } else {
                    event.target.checked = true;
                }
            });
        },
        async addNewLocation(e) {
            let newLocationId = this.newLocation,
                newInventory = this.newInventory;

            newLocationId = newLocationId.replace(/[^0-9]/g, '');
            newInventory = newInventory.replace(/[^0-9]/g, '');

            if(newLocationId.length < 1) {
                showMessage(this.$t('message.notEmptyNewLocationId'), true, 2000);
                return false;
            }

            if(newInventory.length < 1) {
                //showMessage(this.$t('message.notEmptyNewInventory'), true, 2000);
                //return false;
                newInventory = 0;
            }

            if(this.productInventory != null && parseInt(this.productInventory) < parseInt(newInventory)) {
                showMessage(this.$t('message.globalInventoryLess'), true, 2000);
                return false;
            }

            var allsavedLocationsInventories = 0;
            _.each(this.savedLocations, function (location, key) {
                allsavedLocationsInventories += parseInt(location.inventory);
            });

            if((allsavedLocationsInventories + parseInt(newInventory)) > parseInt(this.productInventory)) {
                showMessage(this.$t('message.globalInventoryLess'), true, 2000);
                return false;
            }

            const result = await this.$store.dispatch('addNewLocation', {'productId': this.productId, 'newLocationId': newLocationId, 'newInventory': newInventory});

            if(result.responseText.status === 'error') {
                if(result.responseText.message != '') {
                    showMessage(result.responseText.message, true, 3000);
                } else {
                    showMessage('Please re-login', true, 3000);
                }
            } else {
                showMessage(result.responseText.message, false, 3000);

                this.newLocation = '';
                this.newLocationSelected = '';
                this.newInventory = '';

                this.getProductLocationSData(this.productId, this.$router);
            }
        },
        async deleteLocation(id) {
            showConfirm(this.$t('message.areYouSureDelete'), async () => {
                const result = await this.$store.dispatch('deleteLocation', {'id': id});
                if (result.responseText.status === 'error') {
                    showMessage(result.responseText.message, true, 2000);
                    return false;
                } else {
                    showMessage(result.responseText.message, false, 2000);
                    this.getProductLocationSData(this.productId, this.$router);
                }
            });
        },
        async getProductLocationSData(productId, router){
            const resultData = await this.$store.dispatch('getProductLocationSavedData', {'productId': productId, 'router':router});
            if(resultData.status === 'error') {
                this.showCashRegistersBlock = false;
                if(resultData.message != '') {
                    showMessage(resultData.message, true, 3000);
                } else {
                    showMessage('Please re-login', true, 3000);
                }
            } else {
                this.showCashRegistersBlock = true;
                if(typeof resultData.selectedLocationsData !== 'undefined') {
                    this.selectedLocations = resultData.selectedLocationsData;
                } else {
                    this.selectedLocations = [];
                }

                if(typeof resultData.productInventory !== 'undefined') {
                    this.productInventory = resultData.productInventory;
                } else {
                    this.productInventory = null;
                }
            }
        },
        async changeSavedLocationInfo(router, locationRowId, newLocationId, newInventory, checkedDefault = null) {
            const resultData = await this.$store.dispatch('changeSavedLocationInfo', {
                'router':router,
                'id': locationRowId,
                'newLocationId': newLocationId,
                'newInventory': newInventory,
                'checkedDefault': checkedDefault,
            });

            if (resultData.responseText.status === 'error') {
                showMessage(result.responseText.message, true, 2000);
                return false;
            } else {
                showMessage(this.$t('message.updated'), false, 2000);
            }

            this.getLocationsData();
        },
        async getLocationsData() {
            const result = await this.$store.dispatch('getProductLocationSavedData', {'productId': this.productId, 'router':this.$router});
            if(result.status === 'error') {
                this.showLocationsBlock = false;
                if(result.message != '') {
                    showMessage(result.message, true, 3000);
                } else {
                    showMessage('Please re-login', true, 3000);
                }
            } else {
                if(typeof result.selectedLocationsData !== 'undefined') {
                    this.selectedLocations = result.selectedLocationsData;
                } else {
                    this.selectedLocations = [];
                }

                if(typeof result.productInventory !== 'undefined') {
                    this.productInventory = result.productInventory;
                } else {
                    this.productInventory = null;
                }

                if(this.productId == result.defaultProductId || this.productId == result.defaultTipProductId) {
                    this.showLocationsBlock = false;
                    if(this.productId == result.defaultProductId) {
                        this.isDefaultProduct = true;
                    } else if(this.productId == result.defaultTipProductId) {
                        this.isDefaultTipProduct = true;
                    }

                }
            }
        }

    },
    async created() {
        if (typeof this.localeMapping[this.locale] !== 'undefined') {
            this.$i18n.locale = this.localeMapping[this.locale];
        }

        if(typeof this.productId !== 'undefined' && this.productId != '') {
            this.showLocationsBlock = true;

            this.getLocationsData();
        } else {
            this.showLocationsBlock = false;
            this.selectedLocations = [];
            this.isDefaultProduct = false;
            this.isDefaultTipProduct = false;
        }

    },
    async updated() {
        this.$nextTick(function () {
            if (typeof checkboxRadioStyle !== 'undefined' && typeof checkboxRadioStyle() === "function") {
                checkboxRadioStyle();
            }
        });
    },
    async mounted() {
    },
}
