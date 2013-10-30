require.config({
    deps: ['modules/zones/application'],
    paths: {
        'underscore'         : './libs/underscore/underscore-min',
        'backbone'           : './libs/backbone/backbone-min',
        'i18n'               : './libs/require/i18n'
    },
    shim: {
        'underscore': {exports: '_'},
        'backbone' : {
            deps: ['underscore'],
            exports: 'Backbone'
        }
    }
});