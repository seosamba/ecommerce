export const saveConfigData = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val() + 'api/store/groupconfig/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' : $('#customer-groups-tab-config-token').val(),
                'groupName':payload.groupName,
                'priceValue':payload.priceValue,
                'priceSign':payload.priceSign,
                'priceType':payload.priceType,
                'nonTaxable':payload.nonTaxable,
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
};

export const updateConfigData = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val() + 'api/store/groupconfig/',
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'secureToken' : $('#customer-groups-tab-config-token').val(),
                'groupName':payload.groupName,
                'priceValue':payload.priceValue,
                'priceSign':payload.priceSign,
                'priceType':payload.priceType,
                'nonTaxable':payload.nonTaxable,
                'id':payload.configId
            })
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
};

export const deleteConfigRecord = ({commit, state, dispatch}, payload) => {
        showLoader();
        return new Promise((resolve, reject) => {
            $.ajax({
                'url': $('#website_url').val() + 'api/store/groupconfig/id/' + payload.id,
                'type': 'DELETE',
                'dataType': 'json'
            }).done(async function (response) {
                hideLoader();
                if (response.status === 'error') {
                    resolve(response);
                } else {
                    resolve(response);
                }

            }).fail(async function (response) {
                hideLoader();
                resolve({name: 'login', 'message': 'Please re-login'});
            });
        });
};

export const getConfigSavedData = ({commit, state, dispatch}, payload) => {
    showLoader();

    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/groupconfig/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'limit': state.pagination.generalConfig.itemsPerPage,
                'offset': (state.pagination.generalConfig.currentPage - 1) * state.pagination.generalConfig.itemsPerPage,
                'id': payload.configId
            }
        }).done(async  function(response){
            hideLoader();
            if (response.status !== 'error') {
                commit('setPaginationData', {generalConfig: {totalItems: response.totalRecords}});
                commit('setConfigDataInfo', response.data);
                commit('setAdditionalInfo', response.additionalInfo);
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};

export const getConfigDetails = ({commit, state, dispatch}, payload) => {
    showLoader();

    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/groupconfig/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'id': payload.configId
            }
        }).done(async  function(response){
            hideLoader();
            if (response.status !== 'error') {
                commit('setAdditionalInfo', response.additionalInfo);
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};


export const changeDefaultGroup = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val() + 'plugin/shopping/run/changeDefaultUserGroup/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' : $('#customer-groups-tab-config-token').val(),
                'defaultGroupId':payload.defaultGroupId,
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
};

