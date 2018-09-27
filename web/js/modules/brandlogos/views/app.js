define([
    'backbone',
    './brand-form'
], function(Backbone,
            BrandFormView){
    var BrandsRouter = Backbone.Router.extend({

        routes: {
            ''         : 'index'
        },
        index: function (){
            console.log('test');
        },
        getParams:  function () {
            var result = {},
                tmpData = [];
            location.search
                .substr(1)
                .split("&")
                .forEach(function (item) {
                    tmpData = item.split("=");
                    result[decodeURIComponent(tmpData[0])] = decodeURIComponent(tmpData[1]);
                });
            return result;
        }

    });

    var initialize = function() {
        window.appBrandsRouter = new BrandsRouter;
        Backbone.history.start();
    };

    return {
        initialize: initialize
    };
});