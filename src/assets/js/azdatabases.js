global.jQuery = require('jquery');
var $ = global.jQuery;
window.$ = $;

import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

var slug = '/databases/';

var templateLayout = `
<div>
    <h1 id="dtitle" v-html="title"></h1>
    <ul id="results" class="list-unstyled">
      <li v-if="items != null && items.length == 0"><p>No Results</p></li>
      <li v-for="item in items">
        <h4><a :href="item.url" target="_blank"><b v-html="item.name"></b></a></h4>
        <p v-html="item.description"></p>
        <p v-if="item.userLimit" v-html="item.userLimit" class="userLimits"></p>
        <p><b>Subjects:</b> 
            <ul class="cat">
                <template v-for="cat in item.categories">
                    <template v-for="(subs, cate) in item.subjects" v-if="cate === cat">
                        <li v-for="s in subs">
                            <router-link :to="'/area/' + cat + '/' + s" v-html="s.replace(/_/g, ' ')"></router-link>
                        </li>
                    </template>
                </template>
            </ul>
        </p>
      </li>
    </ul>
</div>
`;

const All = Vue.extend({
    template: templateLayout,
    data: function() {
        return {
            items: null,
            title: 'ALL DATABASES',
        }
    },
    created: function() {
        this.loadAllResults();
    },
    methods: {
        loadAllResults() {
            var self = this;
            self.items = null;
            var link = slug + 'list';
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
            title: null,
        }
    },
    created: function() {
        this.az = this.$route.params.letter;
        this.loadLetter(this.az);
        this.title = this.az.toUpperCase();
    },
    watch: {
        $route() {
            this.az = this.$route.params.letter;
            this.loadLetter(this.az);
            this.title = this.az.toUpperCase();
        }
    },
    methods: {
        loadLetter(link) {
            var self = this;
            var link = link || self.az;
            self.items = null;
            var link = slug + 'az/' + link;
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
            title: null,
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
                self.title = self.area.replace(/_/g, ' ').toUpperCase();
            } else {
                url = self.area + '/' + self.subject;
                self.title = self.area.replace(/_/g, ' ').toUpperCase() + ' - ' + self.subject.replace(/_/g, ' ').toUpperCase();
            }
            var link = slug + 'area/' + url;
            var data = $.get(link, function(data) {
                self.items = data;
            });
        }
    }
});

Vue.component('search-component', {
    template: '<input type="text" v-model="query" v-on:input="search()" class="form-control" placeholder="Search for Databases" />',
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
            title: null,
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
            var search = this.$route.params.query;

            if (search == ' ' || search == '&nbsp;' || search == null) {
                return ' ';
            }
            var link = slug + 'search/' + search;
            var data = $.get(link, function(data) {
                self.items = data;
            });
            self.title = 'SEARCH - ' + search.toUpperCase();
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

router.beforeEach(function (to, from, next) {
    scrollTo(document.body, 0, 30);
    next();
})

function scrollTo(element, to, duration) {
    if (duration < 0) return;
    var difference = to - element.scrollTop;
    var perTick = difference / duration * 2;

    setTimeout(function() {
        element.scrollTop = element.scrollTop + perTick;
        scrollTo(element, to, duration - 2);
    }, 10);
}

const app = new Vue({
    router
}).$mount('#app')


    