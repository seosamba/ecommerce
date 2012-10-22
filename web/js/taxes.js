require.config({
    deps: ['modules/taxes/application'],
    paths: {
        'underscore'         : '/plugins/shopping/web/js/libs/underscore/underscore-min',
        'backbone'           : '/plugins/shopping/web/js/libs/backbone/backbone-min'
    },
    shim: {
        'underscore': {exports: '_'},
        'backbone' : {
            deps: ['underscore'],
            exports: 'Backbone'
        }
    }
});