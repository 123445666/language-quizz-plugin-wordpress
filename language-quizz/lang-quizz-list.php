<?php

function lang_quizz_list()
{
?>
    <link type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/language-quizz/assets/styles/style-admin.css" rel="stylesheet" />
    <div class="wrap">
        <h2><?php echo QUIZZ_PLUGIN_NAME; ?></h2>
        <div class="tablenav top">
            <div class="alignleft actions">
                <a href="<?php echo admin_url('admin.php?page=lang_quizz_create'); ?>"><?php echo QUIZZ_PLUGIN_CREATE; ?></a>
            </div>
            <div class="alignleft actions">
                <a href="<?php echo admin_url('admin.php?page=lang_quizz_export_json'); ?>"><?php echo QUIZZ_PLUGIN_EXPORT_JSON; ?></a>
            </div>
            <br class="clear">
        </div>
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . "lang_quizz";

        $pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;
        $limit = 10; // number of rows in page
        $offset = ($pagenum - 1) * $limit;
        $total = $wpdb->get_var("SELECT COUNT(`id`) FROM $table_name");
        $num_of_pages = ceil($total / $limit);
        $entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC LIMIT $offset, $limit");
        $page_links = paginate_links(array(
            'base' => add_query_arg('pagenum', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;', 'text-domain'),
            'next_text' => __('&raquo;', 'text-domain'),
            'total' => $num_of_pages,
            'current' => $pagenum
        ));

        if ($page_links) {
            echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        }

        ?>
        <table class='wp-list-table widefat fixed striped posts'>
            <tr>
                <th class="manage-column ss-list-width">ID</th>
                <th class="manage-column ss-list-width">Name</th>
                <th class="manage-column ss-list-width">Image</th>
                <th>&nbsp;</th>
            </tr>
            <?php foreach ($entries as $row) { ?>
                <tr>
                    <td class="manage-column ss-list-width"><?php echo $row->id; ?></td>
                    <td class="manage-column ss-list-width"><?php echo stripslashes($row->name); ?></td>
                    <td class="manage-column ss-list-width"><img src="<?php echo $row->image ?>" class="image" style="margin-top:10px;width:200px;" /></td>
                    <td><a href="<?php echo admin_url('admin.php?page=lang_quizz_update&id=' . $row->id); ?>">Edit</a></td>
                </tr>
            <?php } ?>
        </table>
        <?php
        if ($page_links) {
            echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
        }
        ?>
        <div class="tablenav top">
            <div class="alignleft actions">
                <a href="<?php echo admin_url('admin.php?page=lang_quizz_create'); ?>"><?php echo QUIZZ_PLUGIN_CREATE; ?></a>
            </div>
            <div class="alignleft actions">
                <a href="<?php echo admin_url('admin.php?page=lang_quizz_export_json'); ?>"><?php echo QUIZZ_PLUGIN_EXPORT_JSON; ?></a>
            </div>
            <br class="clear">
        </div>
    </div>
<?php
}
