<?php $view->script('settings', 'search:app/bundle/settings.js', ['vue', 'jquery']) ?>

<div id="settings" class="uk-form uk-form-horizontal" v-cloak>

    <div class="uk-grid pk-grid-large" data-uk-grid-margin>
        <div class="pk-width-sidebar">

            <div class="uk-panel">

                <ul class="uk-nav uk-nav-side pk-nav-large" data-uk-tab="{ connect: '#tab-content' }">
                    <li><a><i class="pk-icon-large-settings uk-margin-right"></i> {{ 'General' | trans }}</a></li>
                    <li><a><i class="pk-icon-large-comment uk-icon-small uk-margin-right"></i> {{ 'Additional' | trans }}</a></li>
                </ul>

            </div>

        </div>
	    <div class="pk-width-content">

            <ul id="tab-content" class="uk-switcher uk-margin">
                <li>


					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'General' | trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}</button>
						</div>
					</div>
					
					<div class="uk-form-row">
                        <label class="uk-form-label">{{ 'Limit search result' | trans }}</label>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
                                <!--<input type="number" v-model="config.defaults.limit_search_result" min="1" max="100" step="5" class="uk-form-width-small">-->
								<select class="uk-form-small" v-model="config.defaults.limit_search_result">
										<option value="25">25</option>
										<option value="50">50</option>
										<option value="100">100</option>
										<option value="150">150</option>
										<option value="250">250</option>
										<option value="0">{{ 'Unlimit' | trans }}</option>
								</select>
                            </p>
                        </div>
                    </div>
					
					
					<div class="uk-form-row">
						<label class="uk-form-label">{{ 'Result per page' | trans }}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<p class="uk-form-controls-condensed">
									<select class="uk-form-small" v-model="config.defaults.result_per_page">
										<option value="5">5</option>
										<option value="10">10</option>
										<option value="15">15</option>
										<option value="20">20</option>
										<option value="25">25</option>
										<option value="30">30</option>
										<option value="50">50</option>
										<option value="100">100</option>
										<option value="0">{{ 'All' | trans }}</option>
									</select>
								</p>
							</div>
						</label>
					</div>
				
					<div class="uk-form-row">
                        <span class="uk-form-label">{{ 'Default Search settings' | trans }}</span>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
								<label><input type="checkbox" v-model="config.defaults.use_areas_search"> {{ 'Use areas of search'| trans }}</label>
                            </p>
                            <p class="uk-form-controls-condensed">
                                <label><input type="checkbox" v-model="config.defaults.show_pages_counter"> {{ 'Show pages counter' | trans }}</label>
                            </p>
							<p class="uk-form-controls-condensed">
                                <label><input type="checkbox" v-model="config.defaults.show_posted_in"> {{ 'Show Posted In' | trans }}</label>
                            </p>
							<p class="uk-form-controls-condensed">
                                <label><input type="checkbox" v-model="config.defaults.data_creation"> {{ 'Consider Date' | trans }}</label>
                            </p>
						</div>
                    </div>
					
					<div class="uk-form-row">
                        <span class="uk-form-label">{{ 'Default layout settings' | trans }}</span>
                        <div class="uk-form-controls uk-form-controls-text">
                            <p class="uk-form-controls-condensed">
                                <label>
                                    <input type="checkbox" v-model="config.defaults.show_title" value="title">
                                    {{ 'Show Title' | trans }}
                                </label>
                                <input class="uk-form-small" type="text" v-model="config.defaults.title">
                            </p>
							<p class="uk-form-controls-condensed">
                                <label><input type="checkbox" v-model="config.defaults.markdown_enabled"> {{ 'Enable Markdown' | trans }}</label>
                            </p>

                        </div>
                    </div>

				</li>
				<li>

                    <div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
                        <div data-uk-margin>

                            <h2 class="uk-margin-remove">{{ 'Additional' | trans }}</h2>

                        </div>
                        <div data-uk-margin>

                            <button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}</button>

                        </div>
                    </div>

                    <div class="uk-form-row">
                        <span class="uk-form-label">{{ 'Additional' | trans }}</span>
                        <div class="uk-form-controls uk-form-controls-text">

                        </div>
                    </div>

                </li>
            </ul>

        </div>
    </div>

</div>

