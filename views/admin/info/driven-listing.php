<h2 class="uk-margin-remove">{{ 'Driven Listings Plugin' | trans }}</h2>
<p>
    <strong>Listings</strong> provides a clean way to create manageable content for your site.
    Create any type of listed content
    like professional portfolios, products showcase, events, restaurant menus and more. </p>
<p class="important">If you want more control over the layout of your items create a <strong>Custom
        Template</strong>.
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
    <span class="uk-panel-badge uk-badge">HTML</span>
    <div class="pre" v-pre>
    Put this code in your template
    (before item rendering code)<br/>
	<strong>&lt;div&gt;</strong> id="dv-listings-item-{{item.id}}"><strong>&lt;/div&gt;</strong><br/>
	for GoTo selected item"
    
    </div>
</div>

<hr/>

<p><i class="uk-icon-check uk-text-primary"></i> <strong>Items Search</strong>: Use the following example (display
    all items in the category) - for adding the possibility of a bulleted search .</p>

<section class="" data-uk-margin>

    <div class="uk-panel dr-pre uk-margin-bottom">
        <span class="uk-panel-badge uk-badge">html</span>
        <div class="pre" v-pre>
            <div class="dr-muted">
                &nbsp;<strong>&lt;div <em>v-for="category in list.categories"</em>&gt;</strong><br/>
                &nbsp;&nbsp;&nbsp;<strong>&lt;h1&gt;</strong>{{ category.title }}<strong>&lt;/h1&gt;</strong><br/>
                &nbsp;&nbsp;&nbsp;<strong>&lt;span <em>v-html="category.description"</em>&gt;&lt;/span&gt;</strong><br/>
            </div>
            &nbsp;&nbsp;&nbsp;<strong>&lt;div <em>v-for="item in category.items"</em>&gt;</strong><br/>
            &nbsp;&nbsp;&nbsp;<strong><span class="uk-text-large">&lt;div<em> id="dv-listings-item-{{item.id}}"</em>&gt;&lt;/div&gt;</span></strong><br/>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>&lt;strong&gt;</strong>{{ item.title }}<strong>
                &lt;/strong&gt;</strong><br/>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>&lt;span <em>v-html="item.description"</em>&gt;&lt;/span&gt;</strong><br/>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br/>
            &nbsp;&nbsp;&nbsp;<strong>&lt;/div&gt;</strong><br/>

            <div class="dr-muted">
                &nbsp;<strong>&lt;/div&gt;</strong><br/>
            </div>
        </div>
    </div>

</section>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Extra' | trans }}</h4>
<p>

The original published extension have <strong><a :href="'https://github.com/DrivenNetwork/pagekit-listings/issues/13'" title="issue">issue #13</a> </strong>
<p class="important">
'Getting datetime error when installing with Pagekit'
</p>
I suggest using this <a :href="'https://github.com/neicv/pagekit-listings/blob/master/driven-listings.zip'" title="solution">solution</a>  to avoid this problem.

</p>