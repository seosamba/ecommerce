/**
 * require.js > 2.0.6
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
require.config({
    deps: ["main"],
    paths: {
        'underscore': '/plugins/shopping/web/js/libs/underscore/underscore-min',
        'backbone'  : '/plugins/shopping/web/js/libs/backbone/backbone-min',
        'backbone.paginator'  : '../../libs/backbone/backbone.paginator.min',
        'text'      : '/plugins/shopping/web/js/libs/require/text'
    },
    shim: {
        underscore: {exports: '_'},
        backbone: {
            deps: ['underscore'],
            exports: 'Backbone'
        },
        'backbone.paginator': ['backbone']
    }
});
