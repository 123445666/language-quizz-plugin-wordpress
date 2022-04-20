<?php

function lang_quizz_update()
{
  global $wpdb;
  $table_name = $wpdb->prefix . "lang_quizz";
  $id = $_GET["id"];
  $name = $_POST["name"];
  $image = $_FILES["image"];
  $oldimage = $_POST["old-image"];
  $tmp_name = $image["tmp_name"];
  $image_name = $oldimage;

  $notes = $_POST["notes"];
  //update
  if (isset($_POST['update'])) {
    if ($tmp_name != "") {
      $file_name   =   pathinfo($image['name'], PATHINFO_FILENAME) . "." . strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
      //$imgtype     =   strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
      $image_name  =   QUIZZ_PLUGIN_IMAGES_UPLOADED_URL . $file_name;

      move_uploaded_file($tmp_name, QUIZZ_PLUGIN_IMAGES_UPLOADED_DIR . $file_name);
    }

    $wpdb->update(
      $table_name, //table
      array('name' => $name, 'image' => $image_name, 'notes' => $notes), //data
      array('ID' => $id), //where
      array('%s'), //data format
      array('%s') //where format
    );
  }
  //delete
  else if (isset($_POST['delete'])) {
    $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id = %s", $id));
  } else { //selecting value to update	
    $testimonial = $wpdb->get_results($wpdb->prepare("SELECT id,name,image,notes from $table_name where id=%s", $id));
    foreach ($testimonial as $t) {
      $name = $t->name;
      $image = $t->image;
      $notes = $t->notes;
    }
  }
?>
  <link type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/language-quizz/css/style-admin.css" rel="stylesheet" />
  <div class="wrap">
    <h2><?php echo QUIZZ_PLUGIN_EDIT; ?></h2>

    <?php if ($_POST['delete']) { ?>
      <div class="updated">
        <p><?php wp_redirect(admin_url('admin.php?page=lang_quizz_list')); ?></p>
      </div>
      <a href="<?php echo admin_url('admin.php?page=lang_quizz_list') ?>">&laquo; Back to <?php echo QUIZZ_PLUGIN_NAME; ?> list</a>

    <?php } else if ($_POST['update']) { ?>
      <div class="updated">
        <p><?php echo QUIZZ_PLUGIN_MESSAGE_EDIT; ?></p>
      </div>
      <a href="<?php echo admin_url('admin.php?page=lang_quizz_list') ?>">&laquo; Back to <?php echo QUIZZ_PLUGIN_NAME; ?> list</a>

    <?php } else { ?>
      <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
        <table class='wp-list-table widefat fixed'>
          <tr>
            <th>Name</th>
            <td><input type="text" name="name" value="<?php echo stripslashes($name); ?>" /></td>
          </tr>
          <tr>
            <th>Image</th>
            <td>
              <!-- <input type="button" value="Upload Image" class="button-primary" id="upload_image" />
              <input type="hidden" name="attachment_id" class="wp_attachment_id" value="<?php echo $image; ?>" /> </br>
              <img src="<?php echo wp_get_attachment_url($image) ?>" class="image" style="margin-top:10px;width:200px;" /> -->
              <input type="file" id="upload_image" name="image" value="<?php echo $image; ?>" class="ss-field-width" />
              <input type="hidden" name="old-image" value="<?php echo $image ?>" />
              <img src="<?php echo $image ?>" class="image" style="margin-top:10px;width:200px;" />
            </td>
          </tr>
          <tr>
            <th>Notes</th>
            <td>
              <!-- <textarea rows="4" cols="100" name="notes"><?php echo stripslashes($notes); ?></textarea> -->
              <?php wp_editor(stripslashes($notes), 'notes', $settings = array('textarea_name' => 'notes')); ?>
            </td>
          </tr>
        </table>
        <input type='submit' name="update" value='Save' class='button'> &nbsp;&nbsp;
        <input type='submit' name="delete" value='Delete' class='button' onclick="return confirm('Are you sure?')">
      </form>
    <?php } ?>

  </div>
<?php
}
