	module.exports = {

		el: '#settings',

		data: function () {
			return _.merge({
				interval: ('year'),

			}, window.$data);
		},

		methods: {

			save: function () {
				this.$http.post('admin/system/settings/config', {name: 'friendlyit/search', config: this.config}).then(function () {
							this.$notify('Settings saved.');
						}, function (data) {
							this.$notify(data, 'danger');
						}
					);
			},	
			
			open: function () {
                
                this.$refs.modal.open();
            },
			clear: function () {
					this.$http.delete('admin/search/statistics/clear', {interval: this.interval}).then(function (res) {
							this.$notify(res.data.count + ' records deleted.' + '\n\r' + 'Log cleared.')
						});

					this.$refs.modal.close();
			}
			
		},

	};

	Vue.ready(module.exports);

	