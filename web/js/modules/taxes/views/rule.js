define([
	'Underscore',
	'Backbone'
], function(_, Backbone){
	var ruleView = Backbone.View.extend({
        className: 'taxrule grid_12 ui-corner-all',
        events: {
            'change input[name=default]': 'setDefault',
            'click .delete-rule': 'remove',
            'change [data-reflection=property]': 'setProperty'
        },
        template: $('#ruleTemplate').template(),
        render: function(){
            $(this.el).html($.tmpl(this.template, this.model.toJSON()));
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

            console.log(this.model.toJSON());
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