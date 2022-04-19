<?php

function lang_quizz_list() {
    ?>
    <link type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/cute-testimonials/style-admin.css" rel="stylesheet" />
    <div class="wrap">
        <h2><?php echo QUIZZ_PLUGIN_NAME; ?></h2>
        <div class="tablenav top">
            <div class="alignleft actions">
                <a href="<?php echo admin_url('admin.php?page=lang_quizz_create'); ?>"><?php echo QUIZZ_PLUGIN_CREATE; ?></a>
            </div>
            <br class="clear">
        </div>
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . "lang_quizz";

        $rows = $wpdb->get_results("SELECT id,name,image,notes from $table_name");
        ?>
        <table class='wp-list-table widefat fixed striped posts'>
            <tr>
                <th class="manage-column ss-list-width">ID</th>
                <th class="manage-column ss-list-width">Name</th>
                <th class="manage-column ss-list-width">Image</th>
                <th class="manage-column ss-list-width">Notes</th>
                <th>&nbsp;</th>
            </tr>
            <?php foreach ($rows as $row) { ?>
                <tr>
                    <td class="manage-column ss-list-width"><?php echo $row->id; ?></td>
                    <td class="manage-column ss-list-width"><?php echo stripslashes($row->name); ?></td>
                    <td class="manage-column ss-list-width"><img src="<?php echo $row->image ?>" class="image" style="margin-top:10px;width:200px;" /></td>
                    <td class="manage-column ss-list-width"><?php echo stripslashes($row->notes); ?></td>
                    <td><a href="<?php echo admin_url('admin.php?page=lang_quizz_update&id=' . $row->id); ?>">Edit</a></td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php
}
