<?php
/**
 *
 * @author        RadiusTheme
 * @package       classified-listing/templates
 * @version       1.0.0
 *
 * @var Payment $payment
 */


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\Payment;


Functions::print_notices();
?>

<div class="rtcl-payment-receipt">
    <?php
    if ($payment->gateway && $payment->gateway->id === "offline" && $payment->get_status() === "rtcl-pending") {
        Functions::the_offline_payment_instructions();
    }
    $data = array();
    ob_start();
    ?>
    <div class="payment-info">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td><?php esc_html_e('PAYMENT', 'classified-listing'); ?> #</td>
                        <td><?php echo absint($payment->get_id()); ?></td>
                    </tr>

                    <tr>
                        <td><?php esc_html_e('Total Amount', 'classified-listing'); ?></td>
                        <td>
                            <?php echo Functions::get_formatted_price($payment->get_total(), true); ?>
                        </td>
                    </tr>

                    <tr>
                        <td><?php esc_html_e('Date', 'classified-listing'); ?></td>
                        <td>
                            <?php echo Functions::datetime('rtcl', $payment->get_date_paid()); ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td><?php esc_html_e('Payment Method', 'classified-listing'); ?></td>
                        <td>
                            <?php echo $payment->get_payment_method_title() ?>
                        </td>
                    </tr>

                    <tr>
                        <td><?php esc_html_e('Payment Status', 'classified-listing'); ?></td>
                        <td><?php echo Functions::get_status_i18n($payment->get_status()); ?></td>
                    </tr>
                    <?php if ($transaction_key = $payment->get_transaction_id()): ?>
                        <tr>
                            <td><?php esc_html_e('Transaction Key', 'classified-listing'); ?></td>
                            <td><?php echo esc_html($transaction_key); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td><?php esc_html_e('Order Key', 'classified-listing'); ?></td>
                            <td><?php echo esc_html($payment->get_order_key()); ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <?php
    $data['payment_info'] = ob_get_clean();
    ob_start();
    ?>
    <div class="pricing-info">
        <h2><?php esc_html_e('Details', 'classified-listing'); ?></h2>
        <table class="table table-bordered table-striped">
            <tr>
                <th colspan="2"><?php echo get_the_title($payment->get_listing_id()); ?> (<span
                            class="listing-id"><?php esc_html_e("ID#", 'classified-listing');
                        echo absint($payment->get_listing_id()) ?></span>)
                </th>
            </tr>
            <tr>
                <td class="text-right rtcl-vertical-middle"><?php esc_html_e('Pricing Option ', 'classified-listing'); ?></td>
                <td><?php echo esc_html($payment->pricing->getTitle()); ?></td>
            </tr>
            <tr>
                <td class="text-right rtcl-vertical-middle"><?php esc_html_e('Duration ', 'classified-listing'); ?></td>
                <td><?php
                    printf('%d %s %s %s',
                        absint($payment->pricing->getVisible()),
                        __('Days', 'classified-listing'),
                        $payment->pricing->getFeatured() ? '<span class="badge badge-warning">' . __('Featured', 'classified-listing') . '</span>' : null,
                        $payment->pricing->getTop() ? '<span class="badge badge-info">' . __('Top', 'classified-listing') . '</span>' : null
                    ); ?></td>
            </tr>
            <tr>
                <td class="text-right rtcl-vertical-middle"><?php esc_html_e('Amount ', 'classified-listing'); ?></td>
                <td><?php echo Functions::get_formatted_price($payment->pricing->getPrice(), true); ?></td>
            </tr>
        </table>
    </div>
    <?php
    $data['pricing_info'] = ob_get_clean();
    ob_start();
    ?>
    <div class="action-btn text-center">
        <a href="<?php echo Link::get_account_endpoint_url("listings"); ?>"
           class="btn btn-success"><?php esc_html_e('View all my listings', 'classified-listing'); ?></a>
    </div>
    <?php
    $data['action_btn'] = ob_get_clean();
    $data = apply_filters('rtcl_payment_receipt_html', $data, $payment);

    echo implode('', $data);
    ?>
</div>