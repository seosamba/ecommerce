/**
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
require.config({
    paths: {
        'underscore': '../../libs/underscore/underscore-min',
        'backbone'  : '../../libs/backbone/backbone-min',
        'backbone.paginator'  : '../../libs/backbone/backbone.paginator.min',
        'text'  : '../../libs/require/text',
        'i18n'  : '../../libs/require/i18n'
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
    './router'
], function(Router){
    return Router.initialize();
});