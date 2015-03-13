define([
	'underscore',
	'backbone',
    '../collections/rules',
    '../../zones/collections/zones',
    './rule'
], function(_, Backbone, RulesCollection, ZonesCollection, RuleView){

    var rulesListView = Backbone.View.extend({
        el: $('#manage-taxes'),
        events: {
            'click #new-rule-btn': 'newRule',
            'click #save-btn': 'save'
        },
        initialize: function(){
            $('#price-inc-tax').on('change', this.changeTaxConfig)

            this.rulesCollection = new RulesCollection;
            this.rulesCollection.on('add', this.render, this);
            this.rulesCollection.on('remove', this.render, this);
            this.rulesCollection.on('reset', this.render, this);

            this.zones  = new ZonesCollection();
        },
        render: function(){
            $('#rules').empty();
            this.rulesCollection.each(function(rule){
                var view = new RuleView({model: rule});
                $('#rules').append(view.render().el);
            });
            checkboxRadioStyle();
        },
        save: function() {
            showSpinner();
            var taxError = false;
            $('[name="zoneId"]').removeClass('error').filter(function(){
                if($(this).val() == -1){
                    $(this).addClass('error');
                    taxError = true;
                }
            });
            if (taxError) {
                hideSpinner();
                showMessage('Please select zone', true);
                return false;
            }
            var self = this;
            $.post(this.rulesCollection.url, {rules: this.rulesCollection.toJSON(), secureToken:$('.secure-token-tax').val()}, function(response){
                self.$el.closest('.seotoaster').find('.closebutton .close').trigger('click');
                hideSpinner();
                showMessage('Saved');
            }).fail(function() {
                hideSpinner();
            });
        },
        newRule: function(){
            this.rulesCollection.add();
        },
        changeTaxConfig: function(e){
            $.post($('#website_url').val() + '/plugin/shopping/run/setConfig', {config: {showPriceIncTax: e.target.checked ? 1 : 0}, secureToken: $('.secure-token-tax').val()});
        }
    })
	
	return rulesListView;
});