module.exports = [

    {
        entry: {
            "settings": "./app/views/settings.js",
			"search": "./app/views/search.js",
			"search-widget-settings": "./app/components/search-widget-settings.vue",
        },
        output: {
            filename: "./app/bundle/[name].js"
        },
		externals: {
            'uikit': 'UIkit',
            'vue': 'Vue'
        },     
        module: {
            loaders: [
                { test: /\.vue$/, loader: "vue" }
            ]
        }
    }

];
