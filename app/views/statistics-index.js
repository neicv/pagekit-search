module.exports = {

    name: 'statistic',

    el: '#statistic',

    data: function() {
        return _.merge({
            keywords: false,
            config: {
                filter: this.$session.get('statistics.filter', {order: 'putdate desc', order2: 'wcount desc', limit:25})
            },
            pages: 0,
            count: '',
			db_len: [],
			interval: this.$session.get('statistics.interval','today'),
			view : this.$session.get('statistics.view', 'all'),
        }, window.$data);
    },

    created: function () {

        if (!this.view) {
            this.view = this.$session.get('statistics.view', 'all');
        }
		
		if (!this.interval) {
            this.interval = this.$session.get('statistics.interval', 'today');
        }
    },	
	
    ready: function () {
        this.resource = this.$resource('api/search/statistics{/id}');// {/id}');
        this.$watch('config.page', this.load, {immediate: true});    
    },

    watch: {

        'config.filter': {
            handler: function (filter) {
                if (this.config.page) {
                    this.config.page = 0;
                } else {
                    this.load();
                }

                this.$session.set('statistics.filter', filter);
            },
            deep: true
        },
		
	    view: function (view) {
                this.$session.set('statistics.view', view);
				this.load();
            },
			
		interval: function (interval) {
                this.$session.set('statistics.interval', interval);
				this.load();
            },
    },	

    methods: {

        load: function () {
            this.resource.query({ filter: this.config.filter, page: this.config.page, view: this.view, interval: this.interval }).then(function (res) {

                var data = res.data;
                this.$set('keywords', data.keywords);
                this.$set('pages', data.pages);
                this.$set('count', data.count);
				this.$set('db_len', data.db_len);
            });
        },
    },
	
	partials: {

            'all': require('../templates/all.html'),
            'summ': require('../templates/summ.html')

    }
};

Vue.ready(module.exports);
