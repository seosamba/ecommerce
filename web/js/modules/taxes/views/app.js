define([
	'underscore',
	'backbone',
    '../collections/rules',
    '../../zones/collections/zones',
    './rule',
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
        },
        save: function() {
            $.post(this.rulesCollection.url, {rules: this.rulesCollection.toJSON()}, function(response){
                closePopup();
            });
        },
        newRule: function(){
            this.rulesCollection.add();
        },
        changeTaxConfig: function(e){
            $.post('/plugin/shopping/run/setConfig', {config: {showPriceIncTax: e.target.checked ? 1 : 0}}) ;
        }
    })
	
	return rulesListView;
});