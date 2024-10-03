export const saveConfigData = ({commit, state, dispatch}, payload) => {
    showLoader();
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val() + 'api/leads/leadtaskspreset/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' : $('#customer-groups-tab-config-token').val(),
                'presetName':payload.presetName,
                'taskTitle':payload.taskTitle,
                'taskNotes':payload.taskNotes,
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
            'url': $('#website_url').val() + 'api/leads/leadtaskspreset/',
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'secureToken' : $('#customer-groups-tab-config-token').val(),
                'presetName':payload.presetName,
                'taskTitle':payload.taskTitle,
                'taskNotes':payload.taskNotes,
                'id':payload.presetId
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
                'url': $('#website_url').val() + 'api/leads/leadtaskspreset/id/' + payload.id,
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
            'url': $('#website_url').val()+'api/leads/leadtaskspreset/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'limit': state.pagination.generalConfig.itemsPerPage,
                'offset': (state.pagination.generalConfig.currentPage - 1) * state.pagination.generalConfig.itemsPerPage,
                'id': payload.presetId
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

