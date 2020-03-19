export const saveConfigData = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val() + 'api/store/Productcustomfieldsconfig/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken'   : $('#product-custom-params-config-token').val(),
                'param_type'    : payload.param_type,
                'param_name'    : payload.param_name,
                'label'         : payload.label,
                'dropdownParams':payload.dropdownParams
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
    debugger;
    /*showLoader();
    return new Promise((resolve, reject) => {
        debugger;
        $.ajax({
            'url': $('#website_url').val() + 'api/store/Productcustomfieldsconfig/',
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'secureToken': $('#product-custom-params-config-token').val(),
                'param_type' : payload.param_type,
                'param_name' : payload.param_name,
                'label'      : payload.label,
                'dropdownParams':payload.selectionEl,
                'id' : payload.ruleId
            })
        }).done(async function (response) {
            debugger;
            hideLoader();
            if (response.status === 'error') {
                resolve(response);
            } else {
                resolve(response);
            }

        }).fail(async function(response){
            debugger;
            hideLoader();
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });*/
};

export const deleteConfigRecord = ({commit, state, dispatch}, payload) => {
        showLoader();
        return new Promise((resolve, reject) => {
            $.ajax({
                'url': $('#website_url').val() + 'api/store/Productcustomfieldsconfig/id/' + payload.id+'/secureToken/'+ $('#product-custom-params-config-token').val(),
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

export const getProductConfigSavedData = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/Productcustomfieldsconfig/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'limit' : state.pagination.customFieldsConfig.itemsPerPage,
                'offset': (state.pagination.customFieldsConfig.currentPage - 1) * state.pagination.customFieldsConfig.itemsPerPage
            }
        }).done(async  function(response){
            hideLoader();
            if (response.status !== 'error') {
                commit('setPaginationData', {customFieldsConfig: {totalItems: response.totalRecords}});
                commit('setConfigDataInfo', response.data);
                //commit('setConfigScreenInfo', response.rulesData.configData);
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};

export const updateCustomFieldData = ({commit, state, dispatch}, payload) => {
    debugger;
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/Productcustomfieldsconfig/',
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'id'         : payload.id,
                'param_name' : payload.customFieldName,
                'label'      : payload.customFieldLabel,
                'secureToken': $('#product-custom-params-config-token').val()
            })
        }).done(function(response){
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

export const getRuleConfig = ({commit, state, dispatch}, payload) => {
    showLoader();
    /*return new Promise((resolve, reject) => {
        debugger;
        $.ajax({
            'url': $('#website_url').val()+'api/store/Productcustomfieldsconfig/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'id': payload.ruleId
            }
        }).done(async  function(response){
            hideLoader();
            if (response.status !== 'error') {
                commit('setConfigScreenInfo', response.rulesData.configData);
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });*/
};
