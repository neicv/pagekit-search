<?php $view->style('statistics-index', 'friendlyit/search:assets/css/search.css') ?>
<?php $view->script('statistics-index', 'friendlyit/search:app/bundle/statistics-index.js', 'vue') ?>

<div id="statistic" class="uk-form" v-cloak>

	<div class="uk-grid pk-grid-large" data-uk-grid-margin>
        <div class="pk-width-sidebar">
		    <div class="uk-panel">
				<!--<h4>-->
                <ul class="uk-nav uk-nav-side pk-nav-large">
					
                    <li :class="{'uk-active': view == 'all'}">
                        <a @click.prevent="view = 'all'"><i class="fi-icon-large-bars uk-margin-right"></i>{{ 'All' | trans }}</a>
                    </li>
					<li :class="{'uk-active': view == 'summ'}">
					   <a  @click.prevent="view = 'summ'"><i class="fi-icon-large-bar-chart uk-margin-right"></i>{{ 'Summary' | trans }}</a>
                    </li>
					<li class="uk-nav-divider">
					</li>
				</ul>
				<!--</h4>-->
            </div>
		<h5 class="uk-margin-remove"> {{ 'Database size:' | trans }} {{ db_len.len_mb }} {{ 'Mb' }} </h5>
        </div>
	
		<div class="pk-width-content">
			
			<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
				<div class="uk-flex uk-flex-middle uk-flex-wrap" data-uk-margin>
				<h2 class="uk-margin-remove">{{ '{0} %count% Records|{1} %count% Record|]1,Inf[ %count% Records' | transChoice count {count:count} }}</h2>
					<div class="pk-search">
						<div class="uk-search">
							<input class="uk-search-field" type="text" v-model="config.filter.search" debounce="300">
						</div>
					</div>
				</div>

				<div class="uk-flex uk-flex-middle uk-flex-right uk-margin-right" data-uk-margin>
				<h4>
					<ul class="uk-subnav uk-subnav-pill uk-margin-remove"><!--  uk-subnav-line" data-uk-switcher="{connect:'#my-id'}">-->
						<li :class="{'uk-active': interval == 'today'}">
							<a @click.prevent="interval = 'today'">{{ 'Today' | trans }}</a>
						</li>
						<li :class="{'uk-active': interval == 'yesterday'}">
							<a @click.prevent="interval = 'yesterday'">{{ 'Yesterday' | trans }}</a>
						</li>
						<li :class="{'uk-active': interval == 'week'}">
							<a @click.prevent="interval = 'week'">{{ 'Week' | trans }}</a>
						</li>
						<li :class="{'uk-active': interval == 'month'}">
							<a @click.prevent="interval = 'month'">{{ 'Month' | trans }}</a>
						</li>
						<li :class="{'uk-active': interval == 'year'}">
							<a @click.prevent="interval = 'year'">{{ 'Year' | trans }}</a>
						</li>
					</ul>
				</h4>
				</div>
			</div>
			
			<partial :name="view"></partial>
			
			<h3 class="uk-h1 uk-text-muted uk-text-center" v-show="keywords && !keywords.length">{{ 'No records found.' | trans }}</h3>

			<v-pagination :page.sync="config.page" :data.sync="data"  :pages="pages" v-show="pages > 1 || page > 0"></v-pagination>
		</div>	
	</div>
</div>
