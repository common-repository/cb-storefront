<div id="cbheader">
<div><a href="http://www.cbengine.com" class="cbenginelogo" target="_blank" title="CBengine"><span>cbengine.com</span></a></div>
</div>

<script>
jQuery(document).ready(function($) {
	// hides as soon as the DOM is ready
	$( 'div.v-option-body2' ).hide();
	// shows on clicking the noted link
	$( 'h3' ).click(function() {
		$(this).toggleClass("open");
		$(this).next("div").slideToggle( '1000' );
		return false;
	});
});
</script>

To setup your CB Storefront,  type this shortcode into any WordPress page: [cb-storefront]

<div id="v-options">
<div id="vop-body">


	<div class="v-option">
		<h3>CB StoreFront Install <span>How to install</span></h3>
		<div class="v-option-body clear">


			<p>From the WordPress admin menu click <strong>Pages &raquo; Add New</strong> ... 
			Then type the following into the form fields </p>

			<table border="0" cellspacing="0" style="border-collapse: collapse" cellpadding="0" class="cbhowto">
			  <tr>
			    <td><strong>Page Title:</strong></td>
			    <td>My Storefront</td>
			    <td>this can be anything</td>
			  </tr>
			  <tr>
			    <td><strong>Page Permalink:</strong></td>
			    <td>/storefront/</td>
			    <td>this can be anything</td>
			  </tr>
			  <tr>
			    <td><strong>Page Content:</strong></td>
			    <td>[cb-storefront]</td>
			    <td><b>required</b></td>
			  </tr>
			</table>
			<p>Then Click Publish. That's It.</p>

		</div>
	</div>
</div>
</div>

<div id="v-options">
<div id="vop-body">
	<form method="post" action="">
	<input type="hidden" id="save-storefront-options" name="save-storefront-options" value="1" />
	<div class="v-option">
		<h3>CB StoreFront Options <span>control the storefront</span></h3>
		<div class="v-option-body clear">
			<div class="v-field text clear ">
				<div class="v-field-d"><span>Enter your ClickBank.com Account Nickname.</span></div>
				<label for="cb-storefront_cbid">ClickBank ID</label>
				<input id="cb-storefront_cbid" type="text" name="cb-storefront_cbid" value="<?php $this->opt('cbid'); ?>" />
			</div>
			<div class="v-save-button submit">
				<input type="hidden" name="action" value="save" />
				<input class="button-primary" type="submit" value="Save changes" name="save"/>
			</div>
		</div>
	</div>
	</form>

</div>
</div>

For more information about this plugin visit... <a href="http://www.cbengine.com" target="_blank" class="button">CBengine.com</a>


