
<footer id="footer" role="contentinfo">
  <div class="footer">
    <div class="container">
      <div class="col-md-8 col-md-offset-2 col-sm-12">
        <div class="row">
          <div class="col-sm-6 pull-right">
            <h3>Newsletter</h3>
            <div class="input-group input-group-lg">
              <input type="email" class="form-control" placeholder="Enter your email to subscribe…" value="">
              <span class="input-group-btn">
                <button class="btn btn-champagne" type="button">Go</button>
              </span>
            </div><!-- /input-group -->
          </div>
          <div class="col-sm-6">
            <h3>Social</h3>
            <div class="social-media">
              <a href="https://www.facebook.com/pages/Chasing-Victory-Wooden-Rings/198903756809909"><i class="fa fa-facebook"></i></a> 
              <a href="http://twitter.com/chasingvictory"><i class="fa fa-twitter"></i></a>
              <a href="http://www.pinterest.com/chasingvictory"><i class="fa fa-pinterest"></i></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="site-info text-center">
     &copy;<?php echo date ('Y'); ?> <?php bloginfo( 'name' ); ?>
     | <a href="<?php bloginfo('url'); ?>/terms-conditions">Terms &amp; Conditions</a>
     | <a href="<?php bloginfo('url'); ?>/privacy-policy">Privacy Policy</a>
     | <a href="<?php bloginfo('url'); ?>/store-policies">Store Policies</a>
   </div><!-- #site-info -->
 </div>
</footer>

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php bloginfo('template_directory'); ?>/js/transition.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/alert.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/modal.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/dropdown.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/scrollspy.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/tab.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/tooltip.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/popover.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/button.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/collapse.js"></script>
    <script src="<?php bloginfo('template_directory'); ?>/js/carousel.js"></script>

    <!-- scripts concatenated and minified via ant build script-->
    <script src="<?php bloginfo ('template_directory'); ?>/js/plugins.js"></script>
    <script src="<?php bloginfo ('template_directory'); ?>/js/script.js"></script>

    <!-- Remove these before deploying to production -->
    <script src="<?php bloginfo ('template_directory'); ?>/js/hashgrid.js" type="text/javascript"></script>

    <script type="text/javascript">
    var grid = new hashgrid({ numberOfGrids: 1 });
    </script>

    <?php wp_footer(); ?>
  </body>
  </html>