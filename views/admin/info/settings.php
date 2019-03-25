<h2 class="uk-margin-remove">{{ 'Search Settings' | trans }}</h2>


<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Description' | trans }}</h4>
<p>
There are several options available that you can use to customize the way your Pagekit site's search works. 
Below, will show how to access these settings, and will also explain what each of them controls
</p>
<p class="important">If you want more control over the layout of Search Page create or use a <strong>Custom
        Theme</strong>.
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Result per page' | trans }}</h4>
<p>
Result per page: Select how many search results will be displayed on the page.
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Limit search result' | trans }}</h4>
<p>
Limit of search results: Select how many search result will found.
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Default Search settings' | trans }}</h4>
<p>

<h5>
    <i class="uk-icon-check uk-text-primary"></i>
    <strong>Use areas of search</strong>
</h5>

The Use Search Areas setting gives your users the option to search only through certain types of content. For example, if the user only wants to search through Blog and not Pages, using the search areas feature (shown to users as Search Only) they can do this. 
If you disable this setting, the Search Only feature (highlighted in the screenshot) will not show to users
</p>

<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">SCREENSHOT</span>
    <a href="packages/friendlyit/search/assets/png/settings-area.png" data-uk-lightbox="" title="Open image">
        <img class="uk-margin-large-top" src="packages/friendlyit/search/assets/png/settings-area.png" width="420" height="240" alt="Settings">
    </a>
</div>

<h5>
    <i class="uk-icon-check uk-text-primary"></i>
    <strong>Show pages counter</strong>
</h5>

Show pages counter or not.

<h5>
    <i class="uk-icon-check uk-text-primary"></i>
    <strong>Show Posted In</strong>
</h5>

Show Posted In - shows in which category the item was published.

<h5>
    <i class="uk-icon-check uk-text-primary"></i>
    <strong>Consider Date</strong>
</h5>

By default, when you search a Pagekit site, in the results the date the article was created on will show. 
If you disable the created date feature, the search results will no longer show the Created on data. 
<!--In the screenshot to the right, we've highlighted the created on feature so you know exactly what it is we're referring to.-->
</p>
<hr/>


<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Default layout settings' | trans }}</h4>
<p>
<h5>
    <i class="uk-icon-check uk-text-primary"></i>
    <strong>Show Title</strong>
</h5>

Title: The enable a title of the Search Page. This is the title displayed in the front end for the Search Page.

<h5>
    <i class="uk-icon-check uk-text-primary"></i>
    <strong>Enable Markdown</strong>
</h5>
Enables Markdown for result of Search. (this is inherited from the component)
</p>

<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Highlight Search Terms' | trans }}</h4>
<p>

<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">SCREENSHOT</span>
    <a href="packages/friendlyit/search/assets/png/settings-hl.png" data-uk-lightbox="" title="Open image">
        <img class="uk-margin-large-top" src="packages/friendlyit/search/assets/png/settings-hl.png" width="420" height="240" alt="Settings">
    </a>
</div>

Highlight Search Terms does not come the default Pagekit templates. </p>
<p>
Adding distinctive color for searching phrase will help users to find out in what context search term appears in the sentence on search results page. <br>
This part is very easy to do alone, all you have to do is use predefault options of highlight
</p>
<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">options</span>
    <table class="uk-table uk-table-striped uk-table-condensed">
        <thead>
            <tr>
                <th>Option</th>
                <th>Default Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>{{ 'None' | trans }}</code></td>
                <td>none</td>
            </tr>
            <tr>
                <td><code><span class="highlight">{{ 'Default' | trans }} </span></code></td>
                <td>highlight</td>
            </tr>
            <tr>
                <td><code><span class ="uk-text-bold uk-text-success">{{ 'Success' | trans }} </span></code></td>
                <td>uk-text-bold uk-text-success</td>
            </tr>
            <tr>
                <td><code><span class ="uk-text-bold uk-text-primary">{{ 'Primary' | trans }} </span></code></td>
                <td>uk-text-bold uk-text-primary</td>
            </tr>
            <tr>
                <td><code><span class ="uk-text-bold uk-text-warning">{{ 'Warning' | trans }} </span></code></td>
                <td>uk-text-bold uk-text-warning</td>
            </tr>
            <tr>
                <td><code><span class ="uk-text-bold uk-text-danger">{{ 'Danger' | trans }} </span></code></td>
                <td>uk-text-bold uk-text-danger</td>
            </tr>
        </tbody>
    </table>
</div>

<p>
For use custom style - select "hightlight" and add a extra CSS line into your template style file.<br>
In most cases those phrases wil get "hightlight" :
</p>

<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">HTML</span>
    <div class="pre" v-pre>
        &nbsp;<strong>&lt;span</strong> class="highlight">Pagekit <strong>&lt;/span&gt;</strong> <br>                    
    </div>
</div>

You can easy change font color or/and add background color or add underline, what you decide to do. <br>
Just use simple style, for example: 

<p>
<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">HTML</span>
    <div class="pre" v-pre>
        &nbsp;span.highlight {background: #9BCC56; color: #fff; padding:0 3px;} <br>                    
    </div>
</div>
</p>

Here is the final result: <br>
<p>
<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">SCREENSHOT</span>
    <a href="packages/friendlyit/search/assets/png/settings-res.png" data-uk-lightbox="" title="Open image">
        <img class="uk-margin-large-top" src="packages/friendlyit/search/assets/png/settings-res.png" width="420" height="240" alt="Settings">
    </a>
</div>
</p>
<p>
Of course use your own colors, style effects etc.
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Additional' | trans }}</h4>

<h5>
    <i class="uk-icon-check uk-text-primary"></i>
    <strong>Search Statistics</strong>
</h5>


Enabling gathering of search statistics
When you first access the Pagekit Search Extension, you'll see a Gathering statistics disabled error message. 
To being using the search statistics feature, we'll first enable statistics gathering.


<h5>
    <i class="uk-icon-check uk-text-primary"></i>
    <strong>Clear Statistics</strong>
</h5>

Reseting search data<br>

For busy websites, your users may search on your website quite often. With so much data showing in the search statistics, it may make it difficult to find the information you're actually looking for.
 Clearing the search statistics will remove all data, and allow you to start fresh.
<hr/>