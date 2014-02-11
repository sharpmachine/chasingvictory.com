
	<?php $wcatTerms = get_terms('product_cat', array('hide_empty' => 1, 'orderby' => 'ASC',  'parent' =>0)); //, 'exclude' => '17,77'
		foreach($wcatTerms as $wcatTerm): ?>
			<div class="select-styled champagn-border">
				<select name="product_cat"  id="dropdown_product_cat" class="form-control product-filter-dropdown">
					<option><?php echo $wcatTerm->name; ?></option>
					
					<?php
					$wsubargs = array(
					   'hierarchical' => 1,
					   'show_option_none' => '',
					   'hide_empty' => 0,
					   'parent' => $wcatTerm->term_id,
					   'taxonomy' => 'product_cat'
					);
					$wsubcats = get_categories($wsubargs);
					foreach ($wsubcats as $wsc):
					?>
					<option value="<?php echo $wsc->slug;?>"><a href="<?php echo get_term_link( $wsc->slug, $wsc->taxonomy );?>"><?php echo $wsc->name;?></a></option>
				<?php
				endforeach;
				?>  
				<select>
			</div>
	<?php 
		endforeach; 
	?>
