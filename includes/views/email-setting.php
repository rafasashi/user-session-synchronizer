<?php

	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');

	$email_title=get_option("ussync_email_conf_title");
	$email_sender=get_option("ussync_email_confemail");	
	$email_content=get_option("ussync-email-header");
	
	//var_dump($email_content);exit;
	
	if($email_title==''&&$email_sender==''&&$email_content==''){

		$this->activate_plugins_email();
		
		$email_title=get_option("ussync_email_conf_title");
		$email_sender=get_option("ussync_email_confemail");	
		$email_content=get_option("ussync-email-header");		
	}
?>
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

<div class="ussync-email-confirmation">
    <h1>Email Verification Template Settings</h1>
    <?php
    if (isset($_POST["ussync_email_save"])) {
        foreach ($_POST as $ussync_em_key => $value) {
            update_option($ussync_em_key, stripslashes($value));
        }
        echo "<div style='color:green'>Template Is saved</div>";
    }
  //  echo do_shortcode("[ussyncemailverificationcode]");
    ?>
    <div class="ussync-left">
        <form class="" method="post">
            <div class="form-group">
                <label for="ussync_email_title"><?php _e("Email Subject Title"); ?></label>
                <input type="text" name="ussync_email_conf_title" value="<?php echo $email_title; ?>" id="ussync_email_title" class="ussync-form-input" placeholder="Verification code"/>
            </div>
            <div class="form-group">
                <label for="ussync_email_confemail"><?php _e("Sender Email")?></label>
                <input type="email" name="ussync_email_confemail" value="<?php echo $email_sender ?>"id="ussync_email_confemail" class="ussync-form-input"/>
            </div>
          
            <div class="form-group">
                <label for="ussync_email_confemail"><?php _e("Email Template Content");?></label>
                <?php wp_editor($email_content, "ussync-email-header"); ?>
            </div>
            <div class="form-group">
                <input type="Submit" name="ussync_email_save" class="ussync-form-input" value="<?php _e("Save Template");?>"/>
            </div>
        </form>
    </div>
    <div class="ussync-right">
        <div class="ussync-email-shortcode">
            <p>
                <?php _e("Copy & paste this Shortcode inside the Editor<br>[ussyncemailverificationcode]<br />");?>
            </p>
        </div>
    </div>
    <div class="clear" style="clear:both"></div>
</div>