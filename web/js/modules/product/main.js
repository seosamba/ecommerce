require.config({
	baseUrl: '/plugins/shopping/web/js/',
//	paths: {
//		Underscore: 'libs/underscore/',
//		Backbone: 'libs/backbone/'
//	},
	priority: [
		'libs/underscore/underscore',
		'libs/backbone/backbone'
	]
});

require(
	[
	'modules/product/application',
	'order!libs/underscore/underscore-min',
	'order!libs/backbone/backbone-min'
	],
	function(App){
		App.initialize();
	}
);