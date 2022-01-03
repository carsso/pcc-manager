import VueCompositionApi from '@vue/composition-api'
import VueLodash from 'vue-lodash'
import lodash from 'lodash'
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

Vue.use(VueCompositionApi)
Vue.use(VueLodash, { lodash: lodash })

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.prototype.global = window;

let globalData = new Vue({
    data: { $currentDarkmode: (document.documentElement.dataset.bsColorScheme == 'dark') }
});

Vue.mixin({
    computed: {
        $currentDarkmode: {
            get: function () { return globalData.$data.$currentDarkmode },
            set: function (darkmode) {
                globalData.$data.$currentDarkmode = darkmode;
                document.documentElement.dataset.bsColorScheme = darkmode ? 'dark' : 'light';
            }
        }
    },
});


Vue.filter('round', function (value, decimals) {
    if (!value) {
        value = 0
    }

    if (!decimals) {
        decimals = 0
    }

    value = Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals)
    return value
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
