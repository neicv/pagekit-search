<h2 class="uk-margin-remove">{{ 'Search Widget' | trans }}</h2>


<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Description' | trans }}</h4>
<p>
    This <strong>Search widget</strong> displays a Search entry field where the user can type in a phrase and press 'Enter' to search the web site..
 </p>
<p class="important">If you want more control over the layout of Search widget create or use a <strong>Custom
        Theme</strong>.
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Autocomplete' | trans }}</h4>
<p>
Create inputs that allow users to choose from a list of pre-generated search values while typing..
A dropdown from the Dropdown component is injected to display autocomplete suggestions. You can even navigate through the dropdown with the up and down keys of your keyboard.
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Details' | trans }}</h4>
<p>
Title: Search Widget must have a title
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Screenshot' | trans }}</h4>

<a href="packages/friendlyit/search/assets/png/settings-wg1.png" data-uk-lightbox="" title="Open image">
    <img class="uk-margin-large-top" src="packages/friendlyit/search/assets/png/settings-wg1.png" width="360" height="240" alt="Settings">
</a>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Settings' | trans }}</h4>
<p>
<ul type="square">
<li>Title:  The title of the Search Widget. This is also the title displayed in the front end for the widget depending on the Show Title Form Field</li>
<li>Number of search results: Select how many search result will displayed in widget.</li>
<li>Min. input length before triggering autocomplete: select the number of entered characters to start the search engine.</li>
<li>Length search result in characters: </li>
<li>Appearance : Use custom CSS - Experimental function (beta version)</li>
</ul>
</p>
<hr/>

<h4><i class="uk-icon-angle-right uk-text-primary"></i> {{ 'Usage' | trans }}</h4>
<p>
    To display Searsh widget in custom place, modify your Theme. Add code like this example code in choised php views file.   

</p>

<div class="uk-panel dr-pre uk-margin-bottom">
    <span class="uk-panel-badge uk-badge">HTML</span>
    <div class="pre" v-pre>
        &nbsp;<strong>&lt;?php</strong> if ($view->position()->exists('header_search')) : <strong>?&gt;</strong> </br>                    
        &nbsp;&nbsp;<strong>&lt;div</strong> class="tm-navbar-social tm-search uk-hidden-small"<strong>&gt;</strong></br>
        &nbsp;&nbsp;&nbsp;<strong>&lt;div</strong> data-uk-dropdown="{mode:'click', pos:'left-center'}"<strong>&gt;</strong></br>
        &nbsp;&nbsp;&nbsp;&nbsp;<strong>&lt;button</strong> class="tm-navbar-button tm-search-button"><strong>&lt;/button&gt;</strong></br>
        &nbsp;&nbsp;&nbsp;&nbsp;<strong>&lt;div</strong> class="uk-dropdown-blank tm-navbar-dropdown"<strong>&gt;</strong></br>
        &nbsp;&nbsp;&nbsp;&nbsp;<strong>&lt;?=</strong> $view->position('header_search', 'position-blank.php') <strong>?&gt;</strong></br>
        &nbsp;&nbsp;&nbsp;&nbsp;<strong>&lt;/div</strong>&gt;</br>
        &nbsp;&nbsp;&nbsp;<strong>&lt;/div</strong>&gt;</br>
        &nbsp;&nbsp;<strong>&lt;/div</strong>&gt;</br>
        &nbsp;&lt;<strong>?php</strong> endif <strong>?</strong>&gt;
                    </br>
    </div>
</div>
