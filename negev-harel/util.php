<?php

function has_keys($arr, $required_keys)
{
    $existing_keys = array_keys($arr);
    foreach ($required_keys as $required) {
        if (!in_array($required, $existing_keys)) {
            return false;
        }
    }
    return true;
}

function get_if_set($arr, $key, $default = '')
{
    if (!isset($arr[$key])) {
        return $default;
    } // else

    if (empty($arr[$key])) {
        return $default;
    } // else

    return $arr[$key];
}

function reqdir($subfloder, $ext = "php", $args = array())
{
    $back_trace = debug_backtrace();
    $calling_folder = dirname($back_trace[0]["file"]);
    //include al files under settings-globa directory
    $subfloder = ($subfloder != false) ? "/$subfloder" : "";
    $subfloder_path = $calling_folder . $subfloder;
    $return = "";
    $exclude = get_if_set($args, 'exclude');
    $exclude = is_array($exclude) ? $exclude : false;

    if (!is_dir($subfloder_path)) { //do not try to scan dir if it is not a directory
        return false;
    }

    $order = get_if_set($args, 'order', array());
    if (is_array($order) && !empty($order)) {
        foreach ($order as $file) {
            //error_log(print_r($file ,true));
            $return .= reqdir_file($subfloder_path, "$file.$ext", $ext, $args);
        }
    } else {
        $order = array();
    }

    foreach (scandir($subfloder_path) as $filename) {
        if ($exclude && (in_array($filename, $exclude) || in_array(str_replace(".$ext", '', $filename), $exclude))) {
            continue;
        }
        if (!in_array($filename, $order)) {
            $return .= reqdir_file($subfloder_path, $filename, $ext, $args);
        }
    }
    if (!empty($args["return"])) {
        return $return;
    }
}

function reqdir_file($subfloder_path, $filename, $ext, $args)
{
    $file_path = $subfloder_path . '/' . $filename;
    if (!is_file($file_path)) { //do not try to include what is not a file
        return false;
    }
    if (!filetype($file_path) === "file") { //dont try to include directories
        return false;
    }

    $pathinfo = pathinfo($file_path);
    if ($pathinfo["extension"] !== $ext) { //include only php files
        return false;
    }
    if (substr($pathinfo["filename"], 0, 1) === ".") { //don't include dot files
        return false;
    }
    if (!empty($args["return"])) {
        return file_get_contents($file_path);
    } else {
        require_once $file_path;
    }
}
