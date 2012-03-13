define([
	'Underscore',
	'Backbone'
], function(_, Backbone){

    var customerRowView = Backbone.View.extend({
        template: $('#tableRowTemplate').template(),
        tagName: 'tr',
        events: {
            'click a.delete': 'kill'
        },
        render: function(){
            $(this.el).html($.tmpl(this.template, this.model.toJSON()));
            return this;
        },
        kill: function() {
            var self = this;
            showConfirm('Are you sure?', function(){
                self.model.destroy();
                self.$el.remove();
            });
        }
    });

	return customerRowView;
});