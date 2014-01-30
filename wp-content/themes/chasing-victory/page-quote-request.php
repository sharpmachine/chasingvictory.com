<?php get_header(); ?>

<div id="single-page" class="page">
	<div class="container">
		
		<?php get_template_part('page', 'title' ); ?>

		<div class="row">
			<div class="col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">

				<?php //get_template_part( 'loop', 'page' ); ?>

				<p class="text-center">Chasing Victory Rings can be made from many types of woods and gemstones.  It's important to remember that when choosing materials, high contrast looks best.
					It's also a good idea to keep your design simple.  We recommend trying to keep inlay material options to a minimum (1-3).  Again, keep it simple!
				</p>
					<br>
				<form action="">

					<div class="row">

						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Ring Style:</label>
								<div class="view-swatch pull-right text-right">
									<a href="#" class="gemstones-active" data-toggle="modal" data-target="#swatchModal">View Swatches</a>
								</div>
								<div class="select-styled">
									<select name="" id="" class="form-control">
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
									</select>
								</div>
							</div>
						</div>
						
						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Gemstone:</label>
								<div class="view-swatch pull-right text-right">
									<a href="#" class="gemstones-active" data-toggle="modal" data-target="#swatchModal">View Swatches</a>
								</div>
								<div class="select-styled">
									<select name="" id="" class="form-control">
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
									</select>
								</div>
							</div>
						</div>

					</div>

					<div class="row">

						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Material:</label>
								<div class="view-swatch pull-right text-right">
									<a href="#" class="gemstones-active" data-toggle="modal" data-target="#swatchModal">View Swatches</a>
								</div>
								<div class="select-styled">
									<select name="" id="" class="form-control">
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
									</select>
								</div>
							</div>
						</div>
						
						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Inlay:</label>
								<div class="view-swatch pull-right text-right">
									<a href="#" class="gemstones-active" data-toggle="modal" data-target="#swatchModal">View Swatches</a>
								</div>
								<div class="select-styled">
									<select name="" id="" class="form-control">
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
									</select>
								</div>
							</div>
						</div>

					</div>



					<div class="row">

						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Size:</label>
								<div class="select-styled">
									<select name="" id="" class="form-control">
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
									</select>
								</div>
							</div>
						</div>
						
						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Width:</label>
								<div class="select-styled">
									<select name="" id="" class="form-control">
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
										<option value="">Option</option>
									</select>
								</div>
							</div>
						</div>

					</div>


					<div class="row">

						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Date Needed:</label>
								<input type="date" placeholder="MM/DD/YY" class="form-control">
							</div>
						</div>
						
						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Upload a photo or drawing (optional):</label>
								<div class="fileUpload btn btn-sm btn-gray-lighter"><span>Upload</span><input class="upload" id="uploadBtn" type="file" /></div>
								<input id="uploadFile" type="text" disabled="disabled" placeholder="Choose File" />
							</div>
						</div>

					</div>

					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<label for="message">Description:</label>
								<textarea name="message" id="message" rows="5" class="form-control"></textarea>
							</div>
							
						</div>
					</div>


					<div class="row">

						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Name:</label>
								<input type="text" class="form-control">
							</div>
						</div>
						
						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Email:</label>
								<input type="email" class="form-control">
							</div>
						</div>

					</div>

					<div class="row">

						<div class="col-sm-6">
							<div class="form-group">
								<label for="ring-style" class="pull-left">Phone</label>
								<input type="text" class="form-control">
							</div>
						</div>

					</div>

					<div class="row">
						<div class="col-xs-12">
							<hr>
							<div class="form-group">
								<input type="submit" class="btn btn-default center-block" value="Submit Quote Request">
							</div>
						</div>
					</div>
				</form>

			</div>
		</div>
	</div>
	<?php get_template_part('swatches'); ?>
</div><!-- #page -->

<?php get_footer(); ?>
