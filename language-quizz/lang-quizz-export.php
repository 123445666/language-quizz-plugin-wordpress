<?php

function lang_quizz_export_json()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "lang_quizz";
    $rows = $wpdb->get_results("SELECT id,name,image,notes from $table_name");

    // use $wpdb to query the WP database?
    $result = ["result" => ["data" => $rows]];

    $json = json_encode($result);

    //write json to file
    if (file_put_contents(QUIZZ_PLUGIN_DATA_UPLOADED_DIR . "quizz-data.json", $json)) {
        echo "JSON file created successfully...";
        echo QUIZZ_PLUGIN_DATA_UPLOADED_URL . "quizz-data.json";
        return $json;
    } else
        echo "Oops! Error creating json file...";
}
