<?php $view->style('info-styles', 'friendlyit/search:assets/css/info.css') ?>
<?php $view->script('info', 'friendlyit/search:assets/js/info.js', ['vue', 'uk-tooltip']) ?>
<?php $view->script('uikit-lightbox', 'app/assets/uikit/js/components/lightbox.min.js', ['uk-lightbox']) ?>

<section id="info">

    <div class="uk-grid" data-uk-grid-margin>

        <div class="uk-width-small-1-1 uk-width-medium-1-4">
            <ul class="uk-nav uk-nav-side"
                data-uk-switcher="{connect:'#search-info-content',swiping:false, active:0}">
                <li><a href="#">Overview</a></li>
                <li><a href="#">Important</a></li>
                <li><a href="#">Search widget</a></li>
                <li><a href="#">Driven Listings Plugin</a></li>
                <!-- <li><a href="#">Data Statistic</a></li> -->
                <li><a href="#">About Search</a></li>
            </ul>
            <hr>
            <div class="uk-text-center">
                <a class="uk-button uk-button-primary uk-width-small-1-3 uk-width-medium-1-1" target="_search"
                   href="https://github.com/neicv/pagekit-search" title="View Search on GitHub"><i
                            class="uk-icon-github uk-margin-small-right"></i> {{ 'View on GitHub' | trans }}</a>
            </div>
        </div>

        <div class="uk-width-small-1-1 uk-width-medium-3-4">
            <ul id="search-info-content" class="uk-switcher">

                <li>
                    <?php include 'overview.php'; ?>
                </li>

                <li>
                    <?php include 'important.php'; ?>
                </li>

                <li>
                    <?php include 'widget.php'; ?>
                </li>

                <li>
                    <?php include 'driven-listing.php';?>
                </li>

                <li>
                    <?php include 'about.php';?>
                </li>

            </ul>

        </div>
    </div>

    <div id="search-info-content">

    </div>

</section>