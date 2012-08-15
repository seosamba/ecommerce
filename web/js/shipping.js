requirejs.config({
    paths: {
        'underscore': 'libs/underscore/underscore-min',
        'backbone'  : 'libs/backbone/backbone-min'
    },
    shim: {
        underscore: {exports: '_'},
        backbone: {
            deps: ['underscore'],
            exports: 'Backbone'
        }
    }
});

require([
    'modules/shipping/application'
], function(AppView){
    window.App = new AppView();
    return App;
});