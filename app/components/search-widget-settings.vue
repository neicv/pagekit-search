<template>

    <div class="uk-grid pk-grid-large pk-width-sidebar-large" data-uk-grid-margin>
        <div class="pk-width-content uk-form-horizontal">
		
            <div class="uk-form-row">
                <label for="form-title" class="uk-form-label">{{ 'Title' | trans }}</label>

                <div class="uk-form-controls">
                    <input id="form-title" class="uk-form-width-large" type="text" name="title" v-model="widget.title" v-validate:required>

                    <p class="uk-form-help-block uk-text-danger" v-show="form.title.invalid">{{ 'Title cannot be blank.' | trans }}</p>
                </div>
            </div>

            <div class="uk-form-row">
                <label for="form-result_per_page" class="uk-form-label">{{ 'Number of search results' | trans }}</label>
				
                <div class="uk-form-controls">
                    <select id="form-result_per_page" class="uk-form-small uk-form-width-mini" v-model="widget.data.result_per_page" number>
                        <option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="8">8</option>
						<option value="10">10</option>
						<option value="12">12</option>
						<option value="15">15</option>
						<option value="20">20</option>
					</select>
                </div>
            </div>
	
		    <div class="uk-form-row">
                <label for="form-triggering_chars" class="uk-form-label">{{ 'Min. input length before triggering autocomplete' | trans }}</label>

                <div class="uk-form-controls">
                    <select id="form-triggering_chars" class="uk-form-small uk-form-width-mini" v-model="widget.data.triggering_chars" number>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                </div>
            </div>
	
			<div class="uk-form-row">
				<span class="uk-form-label">{{ 'Length search result in characters' | trans }}</span>
				<div class="uk-form-controls">
					<p class="uk-form-controls-condensed">
						<input class="uk-form-small " type="number" v-model="widget.data.char_limit" min="10" max="300" maxlength="3" required>
					</p>
				</div>
			</div>

			<div class="uk-form-row">
                <span class="uk-form-label">{{ 'Appearance' | trans }}</span>
                <div class="uk-form-controls uk-form-controls-text">
                    <p class="uk-form-controls-condensed">
                        <label><input type="checkbox" v-model="widget.data.css_enabled"> {{ 'Use self CSS' | trans }}</label>
                    </p>
                </div>
            </div>
			
			

        </div>
        <div class="pk-width-sidebar">

            <partial name="settings"></partial>

        </div>
    </div>

</template>

<script>

    module.exports = {

        section: {
            label: 'Settings'
        },

        replace: false,

        props: ['widget', 'config', 'form'],

        created: function () {
            this.$options.partials = this.$parent.$options.partials;
        }

    };

    window.Widgets.components['search-searchwidget:settings'] = module.exports;

</script>