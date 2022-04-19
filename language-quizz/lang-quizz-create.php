<?php

function lang_quizz_create()
{
  $id = $_POST["id"];
  $name = $_POST["name"];
  $image = $_FILES["image"];
  $tmp_name = $image["tmp_name"];
  $image_name = "";
  // $image = $_POST["attachment_id"];
  $notes = $_POST["notes"];
  //insert

  if (isset($_POST['insert'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . "lang_quizz";
    if ($tmp_name != "") {
      $file_name   =   pathinfo($image['name'], PATHINFO_FILENAME) . "." . strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
      //$imgtype     =   strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
      $image_name  =   QUIZZ_PLUGIN_IMAGES_UPLOADED_URL . $file_name;

      move_uploaded_file($tmp_name, QUIZZ_PLUGIN_IMAGES_UPLOADED_DIR . $file_name);
    }
    $wpdb->insert(
      $table_name, //table
      array('name' => $name, 'image' => $image_name, 'notes' => $notes), //data
      array('%s', '%s') //data format			
    );
    $message .= QUIZZ_PLUGIN_NAME . " inserted";
  }
?>
  <link type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/language-quizz/style-admin.css" rel="stylesheet" />
  <div class="wrap">
    <h2><?php echo QUIZZ_PLUGIN_CREATE; ?></h2>
    <?php if (isset($message)) : ?><div class="updated">
        <p><?php echo $message; ?></p>
      </div><?php endif; ?>
    <?php if (!isset($_POST['insert'])) { ?>
      <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
        <table class='wp-list-table widefat fixed'>
          <tr>
            <th class="ss-th-width">Name</th>
            <td><input type="text" name="name" value="<?php echo $name; ?>" class="ss-field-width" /></td>
          </tr>
          <tr>
            <th class="ss-th-width">Image</th>
            <td>
              <!-- <input type="button" value="Upload Image" class="button-primary" id="upload_image" />
              <input type="<?php echo (empty($image) ? "hidden" : "text"); ?>" name="attachment_id" class="wp_attachment_id" value="<?php echo $image; ?>" /> </br>
              <img src="" class="image" style="display:none;margin-top:10px;width:200px;" /> -->
              <input type="file" id="upload_image" name="image" value="<?php echo $image; ?>" class="ss-field-width" />
            </td>
          </tr>
          <tr>
            <th class="ss-th-width">Notes</th>
            <td><textarea rows="4" cols="100" name="notes" value="<?php echo $notes; ?>" class="ss-field-width"></textarea></td>
          </tr>
        </table>
        <input type='submit' name="insert" value='Save' class='button'>
      </form>
    <?php } else { ?>
      <a href="<?php echo admin_url('admin.php?page=lang_quizz_list') ?>">&laquo; Back to <?php echo QUIZZ_PLUGIN_NAME; ?> list</a>
    <?php } ?>

  </div>
<?php
}
