/**
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
require.config({
    paths: {
        'underscore': './libs/underscore/underscore-min',
        'backbone'  : './libs/backbone/backbone-min',
        'backbone.paginator'  : './libs/backbone/backbone.paginator.min'
    },
    shim: {
        underscore: { exports: '_' },
        backbone: {
            deps: ['underscore'],
            exports: 'Backbone'
        },
        'backbone.paginator': ['backbone']
    }
});

require([
    'modules/store-orders/main'
], function(AppView){
    window.StoreOrders = new AppView();
    return StoreOrders;
});