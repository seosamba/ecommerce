define([
	'backbone'
], function(Backbone){

    var customerRowView = Backbone.View.extend({
        template: $('#tableRowTemplate').template(),
        tagName: 'tr',
        events: {
            'click a.details': 'details',
            'change input[name^=select]': 'toggle'
        },
        initialize: function() {
            this.model.on('change:checked', this.render, this);
        },
        render: function(){
            var desktopCountryCode = this.model.get('mobile_country_code'),
                desktopMasks = this.model.get('desktopMasks'),
                id = this.model.get('id'),
                idSelector = '#user-mobile-attribute-id-'+String(id);

            $(this.el).html($.tmpl(this.template, this.model.toJSON()));

            if (typeof desktopMasks[desktopCountryCode] !== 'undefined') {
                $(idSelector, this.el).mask(desktopMasks[desktopCountryCode].mask_value, {autoclear: false});
            } else {
                $(idSelector, this.el).mask('(999) 999 9999', {autoclear: false});
            }

            return this;
        },
        details: function(){
            Toastr.StoreClientsWidget.showCustomerDetails(this.model.get('id'));
        },
        toggle: function(e) {
            this.model.set({checked: e.target.checked}, {silent: true});

            if (typeof _checkboxRadio === "function")  {
                _checkboxRadio();
            }
        }
    });

	return customerRowView;
});