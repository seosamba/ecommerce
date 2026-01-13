export const getProductLocationSavedData = ({commit, state, dispatch}, payload) => {
    showLoader();

    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'plugin/shopping/run/productLocationsData/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'secureToken': $('#general-config-token').val(),
                'productId': payload.productId,
            }
        }).done(async  function(response){
            hideLoader();
            if (response.responseText.status !== 'error') {
                commit('setLocationsData', response.responseText.pickupLocations);
                commit('setSavedLocations', response.responseText.savedLocations);
                resolve(response.responseText);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};

export const addNewLocation = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val() + 'plugin/shopping/run/addNewLocation/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' : $('#general-config-token').val(),
                'productId': payload.productId,
                'newLocationId': payload.newLocationId,
                'newInventory': payload.newInventory,

            }
        }).done(async function (response) {
            hideLoader();
            if (response.status === 'error') {
                resolve(response);
            } else {
                resolve(response);
            }

        }).fail(async function(response){
            hideLoader();
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
}

export const deleteLocation = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val() + 'plugin/shopping/run/deleteLocation/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' : $('#general-config-token').val(),
                'id': payload.id,

            }
        }).done(async function (response) {
            hideLoader();
            if (response.status === 'error') {
                resolve(response);
            } else {
                resolve(response);
            }

        }).fail(async function(response){
            hideLoader();
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
}

export const changeSavedLocationInfo = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val() + 'plugin/shopping/run/changeSavedLocationInfo/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' : $('#general-config-token').val(),
                'id': payload.id,
                'newLocationId': payload.newLocationId,
                'newInventory': payload.newInventory,
                'checkedDefault': payload.checkedDefault,

            }
        }).done(async function (response) {
            hideLoader();
            if (response.status === 'error') {
                resolve(response);
            } else {
                resolve(response);
            }

        }).fail(async function(response){
            hideLoader();
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
}