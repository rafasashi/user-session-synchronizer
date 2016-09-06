	<style>
		.ussync-email-confirmation{
			background: #fff;
			//  max-width: 1000px;
			padding: 20px;

		}
		.ussync-left {
			//    max-width: 650px;
			width:100%;
			float: left;
			padding: 5px
		}
		.ussync-right{
			max-width: 350px;
			float: left;
			padding: 5px
		}
		.form-group {
			margin: 6px;
		}
		.form-group label {
			display: block;
			font-size: 18px;
			line-height: 29px
		}
		.form-group .ussync-form-input {
			width: 100%;
			padding: 9px;
		}
	</style>
	<div class="form-group">
		<?php
		$userEmail = array();
		if (isset($_POST["ussync_email_bulk_verification"])):

			$args = array('role' => $_POST["ussync_email_user_role"]);
			$user_query = new WP_User_Query($args);
			$userEmail = array();
			if (!empty($user_query->results)) {
				foreach ($user_query->results as $user) {
					update_user_meta($user->ID, "ussync_email_verified", "true");
					$userEmail[] = "verified";
				}
				?>            
				<div class="updated fade"><p><b><?php echo count($userEmail) ?> users is verified.</p></b></div>
				<?php
			} else {
				?>
				<div class="updated fade"><p><b><?php echo 0 ?> users is verified.</p></b></div> 
				<?php
			}
		endif;
		?>    

	</div>
	<div class="ussync-email-confirmation">
		<h1>User Email Verification</h1>
		<div class="ussync-left">


			<form class="" method="post">
				<div class="form-group">
					<label for="ussync_email_title"><?php _e("Select User Role"); ?></label>
					<select name="ussync_email_user_role">
						<?php wp_dropdown_roles(); ?>
					</select>
					<input type="Submit" name="ussync_email_bulk_verification" class="button button-primary button-large" value="<?php _e("Bulk Verify"); ?>"/>
				</div>
			</form>
		</div>

	</div>