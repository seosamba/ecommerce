define([
	'underscore',
	'backbone'
], function(_, Backbone){
	var ruleView = Backbone.View.extend({
        className: 'taxrule grid_12 ui-corner-all',
        events: {
            'change input[name=default]': 'setDefault',
            'click .delete-rule': 'remove',
            'change [data-reflection=property]': 'setProperty'
        },
        template: _.template($('#ruleTemplate').text()),
        render: function(){
            $(this.el).html(this.template(this.model.toJSON()));
            return this;
        },
        remove: function(){
            this.model.destroy();
        },
        setProperty: function(e){
            var value = e.target.value,
            property = e.target.name;
            var data = {};
            data[property] = value;
            this.model.set(data);
        },
        setDefault: function(){
            this.model.set({isDefault: 1});
            this.model.collection.chain()
                .without(this.model)
                .each(function(rule){
                    rule.set({isDefault: 0});
                });
        }
    });

    return ruleView;
});