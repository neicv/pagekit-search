<?php $view->script('search-submit', 'friendlyit/search:app/bundle/search.js', 'vue') ?>
	
<?php if($params['show_title']): ?>
	<h2 class="tm-article-subtitle uk-text-success">
		<?= ($params['title'])?>
	</h2>
<?php endif ?>

<div class="tm-block-main uk-block-default tm-padding-around" id="tm-main">
	<div class="tm-middle uk-grid" data-uk-grid-margin="" data-uk-grid-match="">
		<div class="tm-main tm-content uk-width-medium-1-1">
			<div id="system-message-container"></div>
			
			<form id="search-form" class="uk-form uk-margin-bottom" action="<?= $view->url('@search/submit') ?>" method="post">

			
			<div class="uk-panel uk-panel-box">
				<FIELDSET>
					<div class="uk-form-row"><label for="search_searchword"><?= __('Search Keyword: ')?></label>
					
					<input class="inputbox" type="text" name="search[searchword]" v-model="search.searchword" placeholder="<?= __('Search...')?>" size="30" maxlength="<?= $upper_limit; ?>" value="<?= $this->escape($origkeyword); ?>" >
					
					<button title="<?= __(' Search')?>" class="uk-button uk-button-primary" onclick="this.form.submit(); return false"><i class="uk-icon-search"></i><?= __(' Search')?></button>
					
					<!--<INPUT name="task" type="hidden" value="search">-->
					</DIV>
					<div class="clearfix"></div>
				</FIELDSET>
				
				<FIELDSET>
					<LEGEND><?= __('Search for:')?></LEGEND>			 
					<DIV class="uk-form-row">
						<DIV class="controls">
							<?= $lists['searchphrase']; ?>
						</DIV>
					</DIV>
					
					<DIV class="uk-form-row">
						<LABEL class="ordering" for="ordering"><?= __('Ordering:')?></LABEL>
						<?= $lists['ordering'];?>
					</DIV>
				</FIELDSET>
				
				<?php if($params['use_areas_search']) : ?>
				<FIELDSET>
					<LEGEND><?= __('Search Only:')?></LEGEND>			 
						<DIV class="uk-form-row">

									<?php foreach ($searchareas['search'] as $val => $txt) :
									$checked = is_array($searchareas['active']) && in_array($val, $searchareas['active']) ? 'checked="checked"' : '';
									?>
									<label for="area-<?= $val;?>" class="checkbox">
									<input type="checkbox" name="search[areas][]" v-model="search.areas" value="<?= $val;?>" id="area-<?= $val;?>" <?= $checked;?> >
									<!--<?php //echo JText::_($txt); ?>-->
									<?= $txt; ?>
									</label>
									<?php endforeach; ?>
							
						</DIV>
				</FIELDSET>
				<?php endif ?>
			</DIV>
				
				
				<DIV class="uk-margin-top">
						<STRONG>
							<?php if (!empty($searchword)):?>
							<!--@trans('Total: %total% results found.', ['%total%' =>  '<span class="uk-badge uk-badge-info">' . $total . '</span>'])-->
							<?= __($lists['searchkeywordnresult'], ['%s' =>  '<span class="uk-badge uk-badge-info">' . $total . '</span>'])?>
							<?php endif;?>
						</STRONG>	
				
				
				
					<?php if ($total > 0) : ?>
					<div class="uk-float-right uk-clearfix">
						<LABEL for="limit"><?= __('Display #')?></LABEL>
							<?php // $lists['limitbox'];?>
							<?php echo $pagination->getLimitBox(); ?>
						</div>
						<?php if($params['show_pages_counter']): ?>
						<p class="counter">
							<?php echo $pagination->getPagesCounter(); ?>
						</p>
						<?php endif ?>
					<?php endif; ?> 
					
				</div>
			<?php $view->token()->get() ?>
			</form>
			
			
			<?php if ($error) : ?>
				<div class="error">
					<?= __($error)?>
				</div>
			<?php else:?>
		
		
				<?php foreach ($results as $result) : ?>
					<ARTICLE class="uk-article">

				
					<h1 class="tm-article-title uk-article-title ">
						<!--</?php //echo $this->pagination->limitstart + $result->count.'. ';?>-->
						<?php if ($result->href) :?>
						
							<!--<a href="</?php echo $view->url($result->href);?>" title="</?php echo $this->escape($result->title);?>" -->
							<a href="<?= $result->href ?>" title="<?php echo $this->escape($result->title);?>" 
							<?php if ($result->browsernav == 1) :?> target="_blank"<?php endif;?>>
							<?php echo $this->escape($result->title);?></a>

						<?php else:?>
							<?php echo $this->escape($result->title);?>
						<?php endif; ?>
					</h1>

					<h5 class="uk-article-content  uk-margin">
						<?php  echo $result->text; ?>
					</h5>
					
					<?php if ($result->section) : ?>
						<ul class="uk-article-meta uk-subnav uk-subnav-line">

						<?php if($params['show_posted_in']): ?>
						<li>
							<span class="uk-article-meta">
								<?= __('Posted in: ')?><?php echo $this->escape($result->section); ?>
							</span>
						</li>
						<?php endif ?>
						<?php  if($params['data_creation'] && $result->created): ?>
						<li>
							<span class="uk-article-meta">
							<!--</?= __('Created on:  %s', ['%s' =>  '<time datetime="'.date( 'Y-m-d H:i:s', $result->created).'">'.date($result->created).'</time>' ]) ?>-->
							<?php $date = new DateTime($result->created);?>
							<?= __('Created on:  %s', ['%s' =>  '<time datetime="'.$date->format(\DateTime::ATOM).'" v-cloak>{{ "'.$date->format(\DateTime::ATOM).'" | date "longDate" }}</time>' ]) ?>
							</span>
						</li>
						<?php endif ?>
						</ul>
					<?php endif; ?>
					
					
					</ARTICLE>
				<?php endforeach; ?>
		
				<div class="pagination">
					<P><?php echo $pagination->getPagesLinks(); ?></P>
				</div>
				
			<?php endif; ?>
			
		</div>
	</div>	
</div>