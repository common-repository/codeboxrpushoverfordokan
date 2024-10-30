<?php

//$dokan_template_settings = Dokan_Template_Settings::init();

$dokan_pushover_settings = Dokan_Pushover_Settings::init();
//$dokan_pushover_settings->insert_settings_info();

$validate                = $dokan_pushover_settings->profile_validate();
//var_dump($validate);

if ( $validate !== false && !is_wp_error( $validate ) ) {
    $dokan_pushover_settings->insert_settings_info();
}


$cbx_ajax_icon     = plugins_url('codeboxrpushoverfordokan/public/assets/css/busy.gif');

global $current_user;
$profile_info  = dokan_get_store_info( $current_user->ID );


$cbpd_info  = isset($profile_info['pushovernotifiction']) ? $profile_info['pushovernotifiction'] : array();



$enable             = isset($cbpd_info['enable']) ? $cbpd_info['enable'] : '0';
$userapitoken       = isset($cbpd_info['userapitoken']) ? $cbpd_info['userapitoken'] : '';
$device             = isset($cbpd_info['device']) ? $cbpd_info['device'] : '';
$neworder           = isset($cbpd_info['neworder']) ? $cbpd_info['neworder']: '0';
$freeorder          = isset($cbpd_info['freeorder'])? $cbpd_info['freeorder']: '0';
$backorder          = isset($cbpd_info['backorder'])? $cbpd_info['backorder']: '0';
$nostock            = isset($cbpd_info['nostock']) ? $cbpd_info['nostock']: '0';
$lowstock           = isset($cbpd_info['lowstock']) ? $cbpd_info['lowstock']: '0';


?>

<?php

    if ( is_wp_error( $validate ) ) {
        $messages = $validate->get_error_messages();

        foreach( $messages as $message ) {
            dokan_get_template_part( 'global/dokan-error', '', array(
                'message' => $message
            ) );
        }
    }

?>

<?php
/**
 * @since 2.2.2 Insert action before social settings form
 */
do_action( 'dokan_pushpver_settings_before_form', $current_user, $profile_info ); ?>

<form method="post" id="pushover-form"  action="" class="dokan-form-horizontal"><?php ///settings-form ?>

    <?php wp_nonce_field( 'dokan_pushover_settings_nonce' ); ?>

    <?php //foreach( $fields as $key => $field ) { ?>


        <div class="dokan-form-group cbpd-form-group">
            <label class="dokan-w3 dokan-control-label cbpd-control-label"   for="pushovernotifiction_enable[enable]"><?php _e('Enable', 'codeboxrpushoverfordokan'); ?></label>
            <div class="dokan-w5 dokan-text-left cbpd-text-left">
                <div class="checkbox">
                    <label>
                        <input id="pushovernotifiction_enable" name="pushovernotifiction[enable]" value="1" type="checkbox"   <?php checked($enable, '1');  ?> /> <?php _e('Enable sending of notifications', 'codeboxrpushoverfordokan'); ?>
                    </label>
                </div>
            </div>
        </div>

    <!-- setting_cbpdpushup_userapitoken -->
        <div class="dokan-form-group cbpd-form-group">
            <label class="dokan-w3 dokan-control-label cbpd-control-label  "
                   for="pushovernotifiction_usertoken"><?php _e('User Api Token', 'codeboxrpushoverfordokan'); ?></label>

            <div class="dokan-w5 dokan-text-left cbpd-text-left">
                <input id="pushovernotifiction_userapitoken" name="pushovernotifiction[userapitoken]" class="cbpd-form-control dokan-form-control" type="text"   value="<?php echo $userapitoken; ?>"  />
            </div>
        </div>
    <!-- setting_cbpdpushup_device -->
    <div class="dokan-form-group cbpd-form-group">
        <label class="dokan-w3 cbpd-control-label  dokan-control-label" for="pushovernotifiction_device"><?php _e('Device', 'codeboxrpushoverfordokan'); ?>
            <span class="dokan-tooltips-help tips" title="" data-original-title="<?php _e('Optional: Name of device to send notifications', 'codeboxrpushoverfordokan'); ?>">
                <i class="fa fa-question-circle"></i>
            </span>
        </label>
        <div class="dokan-w5 cbpd-text-left  dokan-text-left">
            <input id="pushovernotifiction_device" name="pushovernotifiction[device]" class="cbpd-form-control dokan-form-control" type="text"  value="<?php echo $device; ?>"    />
        </div>
    </div>
    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" ><span class="dokan-tooltips-help tips" title="" data-original-title="<?php _e('Just choose those you need, try to avoid the notification you really doesn\'t need', 'codeboxrpushoverfordokan'); ?>">
                <i class="fa fa-question-circle"></i>
            </span>
        </label>
        <div class="dokan-w5 dokan-text-left">
            <p><?php _e('Notifications Types', 'codeboxrpushoverfordokan'); ?></p>
        </div>
    </div>




    <div class="dokan-form-group cbpd-form-group">
        <label class="dokan-w3 dokan-control-label cbpd-control-label"   for="pushovernotifiction_neworder"><?php _e('New Order', 'codeboxrpushoverfordokan'); ?></label>
        <div class="dokan-w5 dokan-text-left cbpd-text-left">
            <div class="checkbox">
                <label>
                    <input  type="checkbox" id="pushovernotifiction_neworder"   name="pushovernotifiction[neworder]" value="1" <?php checked($neworder, '1');  ?> /> <?php _e('Send notification when a new order is received.', 'codeboxrpushoverfordokan'); ?>
                </label>
            </div>
        </div>
    </div>

    <div class="dokan-form-group cbpd-form-group">
        <label class="dokan-w3 dokan-control-label cbpd-control-label"   for="pushovernotifiction_freeorder"><?php _e('Free Order', 'codeboxrpushoverfordokan'); ?></label>
        <div class="dokan-w5 dokan-text-left cbpd-text-left">
            <div class="checkbox">
                <label>
                    <input   id="pushovernotifiction_freeorder"   name="pushovernotifiction[freeorder]" type="checkbox" value="1" <?php checked($freeorder, '1');  ?> /> <?php _e('Send notification when an order total '.Codeboxrpushoverfordokan::cbpd_get_currency_symbol().'0', 'codeboxrpushoverfordokan'); ?>
                </label>
            </div>
        </div>
    </div>

    <div class="dokan-form-group cbpd-form-group">
        <label class="dokan-w3  dokan-control-label cbpd-control-label"   for="pushovernotifiction_backorder"><?php _e('Back Order', 'codeboxrpushoverfordokan'); ?></label>
        <div class="dokan-w5 dokan-text-left cbpd-text-left">
            <div class="checkbox">
                <label>
                    <input  type="checkbox" id="pushovernotifiction_backorder" name="pushovernotifiction[backorder]"  value="1"  <?php checked($backorder, '1');  ?> /> <?php _e('Send notification when a product is back ordered.'); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="dokan-form-group cbpd-form-group">
        <label class="dokan-w3 dokan-control-label cbpd-control-label"   for="pushovernotifiction_nostock"><?php _e('No Stock', 'codeboxrpushoverfordokan'); ?></label>
        <div class="dokan-w5 dokan-text-left cbpd-text-left">
            <div class="checkbox">
                <label>
                    <input  id="pushovernotifiction_nostock"  name="pushovernotifiction[nostock]"  type="checkbox" value="1"  <?php checked($nostock, '1');  ?> /> <?php _e('Send notification when a product has no stock.'); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="dokan-form-group cbpd-form-group">
        <label class="dokan-w3  dokan-control-label cbpd-control-label "   for="pushovernotifiction_lowstock"><?php _e('Low Stock', 'codeboxrpushoverfordokan'); ?></label>
        <div class="dokan-w5 dokan-text-left cbpd-text-left">
            <div class="checkbox">
                <label>
                    <input   id="pushovernotifiction_lowstock"   name="pushovernotifiction[lowstock]" value="1" type="checkbox" <?php checked($lowstock, '1');  ?> /> <?php _e('Send notification when a product hits the low stock.'); ?>
                </label>
            </div>
        </div>
    </div>

    <div class="dokan-form-group cbpd-form-group">
        <label class="dokan-w3 cbpd-control-label  dokan-control-label"
               for="setting_cbpdpushup_sendtest"><?php _e('Send Test', 'codeboxrpushoverfordokan'); ?></label>

        <div class="dokan-w5 dokan-text-left cbpd-text-left">
            <a href="#" class="btn button setting_cbpdpushup_sendtest" data-busy = "0"
               id="setting_cbpdpushup_sendtest"><?php _e('Click to send test notification', 'codeboxrpushoverfordokan'); ?></a>
            <span data-busy = "0" style="display: none;" class = "cbpushover_ajax_icon" style="display: none;"><img src = "<?php echo $cbx_ajax_icon?>"/></span>
        </div>
    </div>
    <?php //} ?>

    <?php
    /**
     * @since 2.2.2 Insert action on bottom social settings form
     */
    do_action( 'dokan_pushover_settings_form_bottom', $current_user, $profile_info ); ?>

    <div class="dokan-form-group">
        <div class="dokan-w4 ajax_prev dokan-text-left" style="margin-left:24%;">
            <input type="submit" name="dokan_update_pushover_settings" class="dokan-btn dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Update Settings', 'dokan' ); ?>">
        </div>
    </div>

</form>

<?php
/**
 * @since 2.2.2 Insert action after social settings form
 */
do_action( 'dokan_pushover_settings_after_form', $current_user, $profile_info ); ?>