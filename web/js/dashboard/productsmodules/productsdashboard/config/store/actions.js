export const getGeneralProductsScreenData = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        showSpinner('#products-screen-config-block', 'products-screen-config-block-spinner dashboard-spinner');
        $.ajax({
            'url': $('#website_url').val()+'api/productsdashboard/productsgridinfo/',
            'type': 'GET',
            'dataType': 'json',
            'data': {}
        }).done(async  function(response){
            hideSpinner('.products-screen-config-block-spinner');

            if (response.status !== 'error') {
                commit('setConfigDataInfo', response.data);
                commit('setGridInfo', response.gridInfo);
                commit('setCurrencyInfo', response.currencyInfo);
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
        showSpinner('#product-grid-table-body', 'product-grid-table-body-spinner dashboard-spinner');

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
                //commit('setLeadGridAdditionalInfo', response.leadGridAdditionalInfo);
                if (typeof payload.updateGridStats !== 'undefined') {
                    commit('setUpdateGridStats', Date.now())
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

export const getSuppliersCompaniesGridData = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {

        $.ajax({
            'url': $('#website_url').val()+'api/store/companyproducts/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'isGrid': 1,
                'productIds':payload.productIdsString
            }
        }).done(async  function(response){
            commit('setSuppliersCompanies', response);
            resolve(response);
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};

export const getSalesStatsGridData = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {

        $.ajax({
            'url': $('#website_url').val()+'api/store/stats',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'isGrid': 1,
                'id':payload.productIdsString
            }
        }).done(async  function(response){
            commit('setSalesStats', response);
            resolve(response);
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};

export const updateParam = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/products/id/'+payload.id,
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'id': payload.id,
                'secureToken':$('#shopping-token').val(),
                'data':payload.data,
                'newGrid':1,
            })
        }).done(async  function(response){
            resolve(response);
        }).fail(async function(response){
            resolve({ 'error': 1, 'message': response.responseText});
        });
    });
};

//Add brand for products mass-action
export const getAssignBrandMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/brands/',
            'type': 'GET',
            'dataType': 'json'
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

//Add brand for products mass-action
export const assignProductBrandMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/products/id/'+payload.productIds,
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'secureToken' :  $('#shopping-token').val(),
                'data': {'brand': payload.brand},
                'id':payload.productIds,
                'filters':payload.filters,
                'filterAsArray':1,
                'newGrid':1,

            })
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

//Add template for products mass-action
export const getAssignTemplateMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/templates/filter/typeproduct/',
            'type': 'GET',
            'dataType': 'json'
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

//Add template for products mass-action
export const assignProductTemplateMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/products/id/'+payload.productIds,
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'secureToken' :  $('#shopping-token').val(),
                'data': {'pageTemplate': payload.pageTemplate},
                'id':payload.productIds,
                'filters':payload.filters,
                'filterAsArray':1,
                'newGrid':1,

            })
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

//Add tax for products mass-action
export const assignProductTaxMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/products/id/'+payload.productIds,
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'secureToken' :  $('#shopping-token').val(),
                'data': {'taxClass': payload.taxClass},
                'id':payload.productIds,
                'filters':payload.filters,
                'filterAsArray':1,
                'newGrid':1,

            })
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

export const assignProductShippingMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'api/store/products/id/'+payload.productIds,
            'type': 'PUT',
            'dataType': 'json',
            'data': JSON.stringify({
                'secureToken' :  $('#shopping-token').val(),
                'data': {'freeShipping': payload.freeShipping},
                'id':payload.productIds,
                'filters':payload.filters,
                'filterAsArray':1,
                'newGrid':1,

            })
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


