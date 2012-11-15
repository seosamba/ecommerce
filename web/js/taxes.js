require.config({
    deps: ['modules/taxes/application'],
    paths: {
        'underscore'         : './libs/underscore/underscore-min',
        'backbone'           : './libs/backbone/backbone-min'
    },
    shim: {
        'underscore': {exports: '_'},
        'backbone' : {
            deps: ['underscore'],
            exports: 'Backbone'
        }
    }
});