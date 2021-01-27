
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

if (typeof window.i18n !== 'undefined') {
	window.trans = (string) => _.get(window.i18n, string);
	
	/* Vue.prototype.trans = string => _.get(window.i18n, string); */
	Vue.prototype.trans = (string, args) => {
			let value = _.get(window.i18n, string);
		
		_.eachRight(args, (paramVal, paramKey) => {
				value = _.replace(value, `:${paramKey}`, paramVal);
		});
		return value;
	};
}

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key)));

/* Vue.component('example-component', require('./components/ExampleComponent.vue')); */

Vue.component(
	'passport-clients',
	require('./components/passport/Clients.vue')
);

Vue.component(
	'passport-authorized-clients',
	require('./components/passport/AuthorizedClients.vue')
);

Vue.component(
	'passport-personal-access-tokens',
	require('./components/passport/PersonalAccessTokens.vue')
);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

if (document.getElementById('managePassport')) {
	const app = new Vue({
		el: '#managePassport'
	});
}
