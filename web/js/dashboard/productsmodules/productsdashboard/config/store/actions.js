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
                'groupByCompany': payload.groupByCompany,
                'productIds':payload.productIdsString
            }
        }).done(async  function(response){
            if(!payload.groupByCompany) {
                commit('setSuppliersCompanies', response);
            }
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

//Set products param mass-action
export const assignProductParamsMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'plugin/shopping/run/processMassProducts/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' :  $('#shopping-token').val(),
                'data': payload.data,
                'productIds':payload.productIds,
                'step':payload.step,
                'filter':payload.filters,
                'matchingFilter':payload.matchingFilter,
                'filterQuantity':payload.filterQuantity,
                'productChangedType':payload.productChangedType,
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

export const getAllCompaniesDataMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {

        $.ajax({
            'url': $('#website_url').val()+'api/store/companies/id/',
            'type': 'GET',
            'dataType': 'json',
            'data': {

            }
        }).done(async  function(response){
            resolve(response);
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};

//Change product suppliers mass-action
export const changeProductSuppliersMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'plugin/shopping/run/processMassProductsSuppliers/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken':$('#shopping-token').val(),
                'companies': payload.companies,
                'productIds':payload.productIds,
                'removeOldCompanies': '1',
                'step':payload.step,
                'filter':payload.filters,
                'matchingFilter':payload.matchingFilter,
                'filterQuantity':payload.filterQuantity,
            }
        }).done(async  function(response){
            resolve(response);
        }).fail(async function(response){
            resolve({ 'error': 1, 'message': response.responseText});
        });
    });
};

//Delete product mass-action
export const deleteProductMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'plugin/shopping/run/processMassProductsDelete/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken':$('#shopping-token').val(),
                'productIds':payload.productIds,
                'step':payload.step,
                'filter':payload.filters,
                'matchingFilter':payload.matchingFilter,
                'filterQuantity':payload.filterQuantity,
            }
        }).done(async  function(response){
            resolve(response);
        }).fail(async function(response){
            resolve({ 'error': 1, 'message': response.responseText});
        });
    });
};

//Assign promo mass-action
export const assignPromoMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'plugin/promo/run/assignPromoMass/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'promo-price' : payload.promoPrice,
                'promo-from'  : payload.promoFrom,
                'promo-due'   : payload.promoDue,
                'productIds'  : payload.productIds,
                'secureToken' : $('#shopping-token').val()
            }
        }).done(async  function(response){
            resolve(response);
        }).fail(async function(response){
            resolve({ 'error': 1, 'message': response.responseText});
        });
    });
};

export const countProductsMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'plugin/shopping/run/countAllProducts/',
            'type': 'GET',
            'dataType': 'json',
            'data': {
                'secureToken':$('#shopping-token').val(),
                'filter': payload.filters,
            }
        }).done(async  function(response){
            if (response.status !== 'error') {
                resolve(response);
            } else {
                resolve({ name: 'login', 'message': 'Please re-login'});
            }
        }).fail(async function(response){
            resolve({ name: 'login', 'message': 'Please re-login'});
        });
    });
};

//Process product price/option price mass-action
export const processMassProductsPriceMassAction = ({commit, state, dispatch}, payload) => {
    return new Promise((resolve, reject) => {
        $.ajax({
            'url': $('#website_url').val()+'plugin/shopping/run/processMassProductsPrice/',
            'type': 'POST',
            'dataType': 'json',
            'data': {
                'secureToken' :  $('#shopping-token').val(),
                'productIds':payload.productIds,
                'step':payload.step,
                'filter':payload.filters,
                'matchingFilter':payload.matchingFilter,
                'filterQuantity':payload.filterQuantity,
                'productOptionSwitcher':payload.productOptionSwitcher,
                'minusOptionProcess':payload.minusOptionProcess,
                'priceToChange':payload.priceToChange,
                'selectedPriceSign':payload.selectedPriceSign,
                'selectedPriceType':payload.selectedPriceType,
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



