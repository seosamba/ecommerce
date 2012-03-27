define([
	'Underscore',
	'Backbone'
], function(_, Backbone){

    var BrandView = Backbone.View.extend({
        tagName: 'li',
        className: 'grid_2 h150 ui-corner-all',
        template: "<img src='<%= src %>'/><p class='ui-corner-bottom'><%= name %></p>",
        events: {
            'click': 'triggerUpload'
        },
        initialize: function(){
            this.model.on('change', this.render, this);
        },
        render: function(){
            $(this.el).html(_.template(this.template, this.model.toJSON()));
            return this;
        },
        triggerUpload: function(){
            app.filename = this.model.get('name');
            $('#brand-logo-uploader-pickfiles').trigger('click');
        }
    });

	return BrandView;
});