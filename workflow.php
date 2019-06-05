<?php


$flowJson =  getenv ( "MANTIS_WORKFLOW_JSON" );

if($flowJson){
    $flowJson = json_decode($flowJson, true);
}else {
    exit;
}

foreach ($flowJson as $level => &$config) {
    $config["tag"] = "$level:".$config['name'];
    $config["frTag"] = "$level:".$config['traduction_name'];
    if (!isset($config["workflowName"])) {
        $config["workflowName"] = strtoupper($config['name']);
    }
}

$files = [
    "config/config_inc.php" => [],
    "config/custom_constants_inc.php" => ["<?php"],
    "config/custom_strings_inc.php" => ["<?php"],
    "my_view_inc.php" => []
];


$files["config/config_inc.php"][] = '$g_status_enum_string = \''. join( array_map(function($item) use ($flowJson) {
    return $flowJson[$item]["tag"];
},array_keys($flowJson)) ,",") .'\';';


$files["config/custom_strings_inc.php"][] = '$s_status_enum_string = \''. join( array_map(function($item) use ($flowJson) {
    return str_replace("'", "\'", $flowJson[$item]["frTag"] );
},array_keys($flowJson)) ,",") .'\';';



$g_status_enum_workflow = [];
$g_status_colors=[];
foreach ($flowJson as $level => &$config) {
    $files["config/config_inc.php"][] = '$g_status_enum_workflow['. $config['workflowName'] . ']=\'' . join(",",array_map(function($item) use ($flowJson) {
        return $flowJson[$item]["tag"];
    },$config["to"])).'\';';

    $files["config/config_inc.php"][] = '$g_status_colors[\''. $config['name'] . '\']=\''.strtoupper($config["color"]).'\';' ;
    $files["config/config_inc.php"][] = '$g_bug_'. $config['name'] . '_status_threshold='. $config["workflowName"] .';';


    $files["config/custom_constants_inc.php"][] = 'define(\''.$config["workflowName"].'\','.$config["level"].');';

    $files["config/custom_strings_inc.php"][] =  '$s_'.$config['name'].'_bug_button="'.str_replace("'", "\'", $config['bug_button']).'";';
    $files["config/custom_strings_inc.php"][] =  '$s_'.$config['name'].'_bug_title="'.str_replace("'", "\'", $config['bug_title']).'";';
    $files["config/custom_strings_inc.php"][] =  '$s_email_notification_title_for_status_bug_'.$config['name'].'="'.str_replace("'", "\'", $config['email_notification']).'";';
    $files["config/custom_strings_inc.php"][] =  '$s_my_view_title_'.$config['name'].'="'.str_replace("'", "\'", $config['my_view_title']).'";';

    $files["my_view_inc.php"][] = '$t_bug_'.$config['name'].'_status_threshold=config_get("bug_'.$config['name'].'_status_threshold");';
    $files["my_view_inc.php"][] = '$c_filter["'.$config['name'].'"]=array( FILTER_PROPERTY_CATEGORY_ID => Array( "0" => META_FILTER_ANY ), FILTER_PROPERTY_SEVERITY => Array( "0" => META_FILTER_ANY), FILTER_PROPERTY_STATUS => Array( "0" => $t_bug_'.$config['name'].'_status_threshold), FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed, FILTER_PROPERTY_REPORTER_ID => Array( "0" => META_FILTER_ANY), FILTER_PROPERTY_HANDLER_ID => Array( "0" => META_FILTER_ANY), FILTER_PROPERTY_RESOLUTION => Array( "0" => META_FILTER_ANY), FILTER_PROPERTY_BUILD => Array( "0" => META_FILTER_ANY), FILTER_PROPERTY_VERSION => Array( "0" => META_FILTER_ANY), FILTER_PROPERTY_HIDE_STATUS => Array( "0" => $t_hide_status_default), FILTER_PROPERTY_MONITOR_USER_ID => Array( "0" => META_FILTER_ANY));';

    $files["my_view_inc.php"][] = '$t_url_link_parameters["'.$config['name'].'"]=FILTER_PROPERTY_STATUS . "=" . $t_bug_'.$config['name'].'_status_threshold . "&" . FILTER_PROPERTY_HIDE_STATUS . "=" . $t_bug_'.$config['name'].'_status_threshold;';

}


foreach ($files as $file => $strs) {

    $myfile = fopen($file, "a") or die("Unable to open file!");

    foreach ($strs as $str) {
        fwrite($myfile, $str."\n");
    }

    fwrite($myfile, "\n");

    fclose($myfile);


}
