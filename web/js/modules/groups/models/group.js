define([
    'backbone'
], function (Backbone) {
    var GroupsModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/groups/id/';
        }
    });

    return GroupsModel;
});