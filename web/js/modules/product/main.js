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
	'modules/product/app',
	'order!libs/underscore/underscore-dev',
	'order!libs/backbone/backbone-dev'
	],
	function(App){
		App.initialize();
	}
);