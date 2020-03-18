export const saveConfigData = ({commit, state, dispatch}, payload) => {
    showLoader();
    /*return new Promise((resolve, reject) => {
        debugger;
        $.ajax({
            'url': $('#website_url').val() + 'api/store/productcustomfields/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' : $('#assign-groups-rules-config-token').val(),
                'rule_name' :  payload.ruleName,
                'fieldsData':payload.fieldsData,
                'actionsData': payload.actionsData
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
    });*/
};

export const updateConfigData = ({commit, state, dispatch}, payload) => {
    showLoader();
    /*return new Promise((resolve, reject) => {
        debugger;
        $.ajax({
            'url': $('#website_url').val() + 'api/store/productcustomfields/',
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'secureToken' : $('#assign-groups-rules-config-token').val(),
                'rule_name' :  payload.ruleName,
                'fieldsData':payload.fieldsData,
                'actionsData': payload.actionsData,
                'id' : payload.ruleId
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
    });*/
};

export const deleteConfigRecord = ({commit, state, dispatch}, payload) => {
        showLoader();
        /*return new Promise((resolve, reject) => {
            debugger;
            $.ajax({
                'url': $('#website_url').val() + 'api/store/productcustomfields/id/' + payload.id+'/secureToken/'+ $('#assign-groups-rules-config-token').val(),
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
        });*/
};

export const getConfigSavedData = ({commit, state, dispatch}, payload) => {
    showLoader();
    debugger;
    return new Promise((resolve, reject) => {
        debugger;
        $.ajax({
            'url': $('#website_url').val()+'api/store/productcustomfields/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'limit': state.pagination.rulesConfig.itemsPerPage,
                'offset': (state.pagination.rulesConfig.currentPage - 1) * state.pagination.rulesConfig.itemsPerPage
            }
        }).done(async  function(response){
            debugger;
            hideLoader();
            if (response.status !== 'error') {
                commit('setPaginationData', {rulesConfig: {totalItems: response.rulesData.totalRecords}});
                commit('setConfigDataInfo', response.rulesData.data);
                commit('setConfigScreenInfo', response.rulesData.configData);
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};

export const getRuleConfig = ({commit, state, dispatch}, payload) => {
    showLoader();
    /*return new Promise((resolve, reject) => {
        debugger;
        $.ajax({
            'url': $('#website_url').val()+'api/store/productcustomfields/',
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
