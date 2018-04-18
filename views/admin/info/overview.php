<h2 class="uk-margin-remove">{{ 'Overview' | trans }}</h2>
<p>
    The <strong>Search Extension</strong> is used to display a search box, where the user types a kewords to search the website.
</p>
The Search extension allows you to do two things - view statistics about what people are searching for on your site and customize the available search settings.
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Statistics' | trans }}</h4>
<p>
Statistics: Search Term Analysis back-end screen allows you to view statistics about searches performed by visitors of your site.
</p>
<p class="important">Note: By default, statistics functionality is disabled after installing Extension! -- refer to Options for information on enabling statistics.
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Description' | trans }}</h4>
<p>
The Search Statistics screen allows you to see how many searches were done for each keyword combination and how many results were returned for each search.
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Usage' | trans }}</h4>
<p>
    To display one or many listings on a page copy and paste the PLUGIN CODE for each list on your Pagekit page. The plugin code for each list can be found on the <a :href="'../listings'" title=""Listings Page">Listings</a> page.

</p>

<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">default</span>
    <div class="pre" v-pre>
        (listings){"id":"1"}
    </div>
</div>

<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">options</span>
    <div class="pre" v-pre>
        (listings){ "id":"1",
        "listingTitle":"uk-hidden",
        "listingDescription":"uk-text-center"
        }
    </div>
</div>

<hr/>

