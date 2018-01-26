/**
 * require.js > 2.0.6
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
require.config({
    deps: ["main"],
    paths: {
        'underscore': '../../libs/underscore/underscore-min',
        'backbone'  : '../../libs/backbone/backbone-min',
        'backbone.paginator'  : '../../libs/backbone/backbone.paginator.min',
        'text'      : '../../libs/require/text',
        'i18n'  : '../../libs/require/i18n',
        'plupload': '../../../../../../system/js/external/plupload/plupload',
        'pluploadhtml5':'../../../../../../system/js/external/plupload/plupload.html5',
        'pluploadflash':'../../../../../../system/js/external/plupload/plupload.flash',
        'pluploadhtml4':'../../../../../../system/js/external/plupload/plupload.html4'
    },
    shim: {
        underscore: {exports: '_'},
        backbone: {
            deps: ['underscore'],
            exports: 'Backbone'
        },
        'backbone.paginator': ['backbone'],
        plupload: {
            exports: "plupload"
        },
        pluploadhtml5: {
            exports: "pluploadhtml5"
        },
        pluploadflash: {
            exports: "pluploadflash"
        },
        pluploadhtml4: {
            exports: "pluploadhtml4"
        }
    }
});
