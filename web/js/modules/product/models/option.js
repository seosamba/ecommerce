define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/collections/selections'
], function(_, Backbone, Selections){
	
	var ProductOption = Backbone.Model.extend({
		defaults: {
            title: '',
            type: 'dropdown'
		},
		initialize: function(){
			var list = new Selections();
			if (this.has('selection')) {
                list.add(this.get('selection'));
            }
            this.set({selection: list});

            this.bind('change:selection', function(){
                var list = new Selections();
                list.reset(this.get('selection'));
                this.set({selection: list});
            }, this);
        }
	});
	
	return ProductOption;
});