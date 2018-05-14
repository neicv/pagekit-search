<h2 class="uk-margin-remove">{{ 'Important' | trans }}</h2>
<p>
    The <strong>IMPORTANT CHANGE</strong> in new release.
</p>
<p class="important">
View Template
..\views\form\placeholder.html
$result->title should not be escaped in this case, as it may contain span HTML tags wrapping the searched terms, if present in the title.
</p>
<p>
If You use your own search result output template, you will notice that you need to correct the code according to the original template.
(see row 108 - 114 in original placeholder.html)
</p>
<hr/>


