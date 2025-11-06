export const getGeneralProductsScreenData = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        showSpinner('#leads-screen-config-block', 'products-screen-config-block-spinner dashboard-spinner');
        $.ajax({
            'url': $('#website_url').val()+'api/productsdashboard/productsgridinfo/',
            'type': 'GET',
            'dataType': 'json',
            'data': {}
        }).done(async  function(response){
            hideSpinner('.products-screen-config-block-spinner');

            if (response.status !== 'error') {
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

export const getProductsGridData = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        showSpinner('#product-grid-table-dashboard-table-scroll', 'product-grid-table-body-spinner dashboard-spinner');

        $.ajax({
            'url': $('#website_url').val()+'api/store/products/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'limit': state.pagination.productsGrid.itemsPerPage,
                'offset': (state.pagination.productsGrid.currentPage - 1) * state.pagination.productsGrid.itemsPerPage,
                'filter': payload.searchData,
                'isGrid': 1
            }
        }).done(async  function(response){
            hideSpinner('.product-grid-table-body-spinner');
            if (response.status !== 'error') {
                let totalRecords = 0;
                if (response.totalRecords !== null) {
                    totalRecords = response.totalRecords;
                }

                commit('setProductsGridInfo', response.data);
                commit('setPaginationData', {productsGrid: {totalItems: totalRecords}});
                commit('setTotalItemsFound', response.totalRecords);
                commit('setLeadGridAdditionalInfo', response.leadGridAdditionalInfo);
                commit('setCurrencyInfo', response.currencyInfo);
                if (typeof payload.updateOppStats !== 'undefined') {
                    commit('setUpdateOppStats', Date.now())
                }
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};


export const countLeadsMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        let uniqueEmail = 0,
            validateEmailBalance = 0,
            additionalParams = 0,
            advertising = 0;

        if(payload.uniqueEmail) {
            uniqueEmail = 1;
        }

        if(payload.validateEmailBalance) {
            validateEmailBalance = 1;
        }

        if(payload.additionalParams) {
            additionalParams = payload.additionalParams;
        }

        if(payload.advertising) {
            advertising = payload.advertising;
        }

        $.ajax({
            'url': $('#website_url').val()+'plugin/leads/run/countAllLeads/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' :  $('#leads-screen-config-token').val(),
                'filters': payload.filters,
                'leadIds':payload.leadIds,
                'existMobileNumber': payload.existMobileNumber,
                'filterAsArray':1,
                'uniqueEmail':uniqueEmail,
                'validateEmailBalance':validateEmailBalance,
                'additionalParams':additionalParams,
                'advertising':advertising,
            }
        }).done(async  function(response){
            if (response.status !== 'error') {
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ error: 1});
        });
    });
};

//Add tags for leads mass-action
export const addLeadTagsMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        let allRecords = 0;
        if(payload.allRecords) {
            allRecords = 1;
        }

        $.ajax({
            'url': $('#website_url').val()+'plugin/leads/run/addTags/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' :  $('#leads-screen-config-token').val(),
                'tagsIds':payload.tagsIds,
                'leadIds':payload.leadIds,
                'tagsFunction':payload.tagsFunction,
                'filters':payload.filters,
                'tagsType':payload.tagsType,
                'allRecords':allRecords,
                'offset':payload.offset,
                'preParseParams':0,
            }
        }).done(async  function(response){
            if (response.status !== 'error') {
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ error: 1});
        });
    });
};


