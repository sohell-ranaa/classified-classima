<?php

use Rtcl\Helpers\Functions;

?>
<div class="wrap rtcl" id="rtcl-listing-types-wrap">
    <h1><?php esc_html_e("Listing Types", 'classified-listing') ?></h1>
    <div class="rtcl-listing-types-wrapper row">
        <div id="input-new-type-wrapper" class="col-md-4 col-12">
            <form id="input-new-type-form">
                <div class="form-group">
                    <label><?php esc_html_e("Add new type", "classified-listing"); ?></label>
                    <input type="text" name="type" id="add-input-type" class="form-control">
                </div>
                <div class="form-group">
                    <button class="btn btn-success" type="submit"
                            id="rtcl-add-btn"><?php esc_html_e("Add new type", "classified-listing"); ?></button>
                </div>
            </form>
        </div>
        <div class="col-md-8 col-12" id="rtcl-listing-type-wrap">
            <?php
            $types = Functions::get_listing_types();
            if (!empty($types)) {
                $typeHtml = '';
                foreach ($types as $typeId => $type) {
                    $typeHtml .= sprintf('<li class="list-group-item listing-type" data-id="%1$s">
                                                    <div class="type-details d-flex">
                                                        <div class="type-info">
                                                            <div class="type-info-id">%1$s</div>
                                                            <div class="type-info-name">%2$s</div>
                                                        </div>
                                                        <div class="action ml-auto"><span class="btn btn-success btn-sm edit">Edit</span><span class="btn btn-danger btn-sm delete">Delete</span></div>
                                                    </div>
                                                    <div class="edit-action">
                                                        <form class="row input-update-type-form">
                                                            <div class="form-group col-6">
                                                                <label>ID</label>
                                                                <input type="text" name="id" class="form-control" value="%1$s" >
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label>Type</label>
                                                                <input type="text" name="name" class="form-control" value="%2$s" >
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <button type="submit" class="btn btn-primary w-100">Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                  </li>',
                        $typeId,
                        $type
                    );
                }
                printf('<ul id="listing-types" class="list-group">%s</ul>', $typeHtml);
            } else {
                esc_html_e("No listing type found", "classified-listing");
            }

            ?>
        </div>
    </div>
</div>