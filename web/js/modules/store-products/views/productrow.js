define([
	'backbone',
    'text!../templates/productrow.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(Backbone, ProductRowTemplate, i18n){
    var ProductRowView = Backbone.View.extend({
        tagName: 'tr',
        events: {
            'change input[type=checkbox]': 'toggle',
            'dblclick td.editable': 'editProperty'
        },
        template: _.template(ProductRowTemplate),
        initialize: function(){
            this.model.on('change', this.render, this);
        },
        render: function(){
            this.model.set('currency', $('input[name=system-currency]').val());
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        },
        toggle: function(e){
            if (e.currentTarget.checked){
                this.model.set('checked', true);
            } else {
                this.model.set('checked', false);
            }
        },
        editProperty: function(e){
            e.preventDefault();
            var self = this,
                prop = $(e.currentTarget).data('prop');
            smoke.prompt(_.isUndefined(i18n['Input new value']) ? 'Input new value':i18n['Input new value'], function(e){
                if (e && self.model.get(prop) !== e){
                    self.model.set(prop, e);
                    self.model.save
                }
            }, {value: this.model.get(prop)});
            return false;
        }
    });

	return ProductRowView;
});