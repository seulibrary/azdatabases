global.jQuery = require('jquery');
var $ = global.jQuery;
window.$ = $;

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

var templateLayout = `
    <ul id="results" class="list-unstyled">
      <li v-if="items != null && items.length == 0"><p>No Results</p></li>
      <li v-for="item in items">
        <p><a :href="item.url" target="_blank"><b v-html="item.name"></b></a></p>
        <p v-html="item.description"></p>
        <p><b>Categories:</b> 
        <ul class="list-inline text-capitalize">
          <li v-for="cat in item.categories" v-html="cat.replace(/_/g, '&nbsp;')">
          </li>
        </ul>
        </p>
      </li>
    </ul>
`;

const All = Vue.extend({
    template: templateLayout,
    data: function() {
        return {
            items: null,
        }
    },
    created: function() {
        this.loadAllResults();
    },
    methods: {
        loadAllResults() {
            var self = this;
            self.items = null;
            var link = '/database/list';
            var data = $.get(link, function(data) {
                self.items = data;

            });
        }
    }
});

const Letters = Vue.extend({
    template: templateLayout,
    data: function() {
        return {
            items: null,
            az: null,
        }
    },
    created: function() {
        this.az = this.$route.params.letter;
        this.loadLetter(this.az);
    },
    watch: {
        $route() {
            this.az = this.$route.params.letter;
            this.loadLetter(this.az);
        }
    },
    methods: {
        loadLetter(link) {
            var self = this;
            var link = link || self.az;
            self.items = null;
            var link = '/database/az/' + link;
            var data = $.get(link, function(data) {
                self.items = data;
            });
        }
    }
});

const Areas = Vue.extend({
    template: templateLayout,
    data: function() {
        return {
            items: null,
            area: null,
            subject: null,
        }
    },
    created: function() {
        this.area = this.$route.params.area;
        this.subject = this.$route.params.subject;
        this.loadArea();
    },
    watch: {
        $route() {
            this.area = this.$route.params.area;
            this.subject = this.$route.params.subject;
            this.loadArea();
        }
    },
    methods: {
        loadArea(link, combined) {
            var self = this;
            self.items = null;
            var url = '';

            if (self.subject === undefined) {
                url = self.area;
            } else {
                url = self.area + '/' + self.subject;
            }
            var link = '/database/area/' + url;
            var data = $.get(link, function(data) {
                self.items = data;
            });
        }
    }
});

Vue.component('search-component', {
    template: '<input type="text" v-model="query" v-on:input="search()" class="form-control" placeholder="Search..." />',
    data: function() {
        return {
            query: null,
        }
    },
    methods: {
        search() {
            var query = this.query.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
                return '&#' + i.charCodeAt(0) + ';';
            });
            router.push({
                path: '/search/' + query
            });
        }
    }
})

const Search = Vue.extend({
    template: templateLayout,
    created: function() {
        this.getSearch();
    },
    data: function() {
        return {
            items: null,
        }
    },
    watch: {
        $route() {
            this.getSearch();
        }
    },
    methods: {
        getSearch() {
            var self = this;
            self.items = null;
            var link = '/database/search/' + this.$route.params.query;
            var data = $.get(link, function(data) {
                self.items = data;
            });
        },
    }
})

const routes = [{
        path: '/',
        component: All
    },
    {
        path: '/area/:area/:subject?',
        component: Areas,
        props: true
    },
    {
        path: '/az/:letter',
        component: Letters,
        props: true
    },
    {
        path: '/search/:query',
        component: Search,
        props: true
    },
]

const router = new VueRouter({
    routes
})

const app = new Vue({
    router
}).$mount('#app')