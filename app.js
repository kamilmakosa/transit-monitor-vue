const Selector = window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Selector.vue');
const Searcher = window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Searcher.vue');
const Monitor = window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Monitor.vue');
const Timetable = window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Timetable.vue');
const LeafletMap = window.httpVueLoader('http://51.178.29.39/transit-monitor/components/LeafletMap.vue');

var router = new VueRouter({
    mode: 'history',
    base: '/transit-monitor/'
});

const app = new Vue({
    router: router,
    components: {
        'selector': Selector,
        'searcher': Searcher,
        'monitor': Monitor,
        'timetable': Timetable,
        'leaflet-map': LeafletMap,
    },
    data() {
        return {
            stop_id: undefined,
            mode: undefined,
            map: false
        }
    },
    created: function() {
        this.setMode();
    },
    watch: {
        $route: function() {
            this.setMode();
        }
    },
    methods: {
        setMode() {
            var path = this.$route.path;
            path = path.replace(/^\/+|\/+$/g, '');
            path = path.split("/");
            if(this.$route.path == '/' || path[0] == 'monitor') {
                this.mode = 'monitor';
            }
            if(path[0] == 'timetable') {
                this.mode = 'timetable';
            }
            if(path[0] == 'map') {
                this.mode = 'map';
                this.map = true;
            }
            if (REACT_APP) {
                window.ReactNativeWebView.postMessage('msg');
            }
        }
    }
}).$mount('#app')
