<?php
/**
 * Show error messages
 *
 * @author     techlabpro01
 * @package    classified-listing/Templates
 * @version    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$messages) {
    return;
}

?>
<div class="rtcl-error alert alert-danger" role="alert">
    <?php foreach ($messages as $message) : ?>
        <p><?php echo wp_kses_post($message); ?></p>
    <?php endforeach; ?>
</div>
