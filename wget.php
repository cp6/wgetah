<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secret_key = 'PnSrenvN9mse6QukkRF3RN9edWXdFZnk';//Secret key that must be inputted at the form
    //Can leave empty if page is behind authentication
    if (isset($_POST['key'])) {
        $form_key = $_POST['key'];
    } else {
        $form_key = '';
    }

    if (empty($secret_key) && empty($form_key)) {
        $proceed = true;//No key set OR sent
    } elseif (!empty($secret_key) && empty($form_key)) {
        $proceed = false;//Key set but NOTHING sent
    } elseif ($form_key == $secret_key) {
        $proceed = true;//Key sent is the right one
    } else {
        $proceed = false;
    }

    if ($proceed) {
        if (isset($_POST['skip_ssl'])) {
            $ssl = "--no-check-certificate";
        } else {
            $ssl = "";
        }
        if (isset($_POST['url']) && isset($_POST['save_as'])) {
            if (isset($_POST['user']) && !empty($_POST['user']) && isset($_POST['pass']) && !empty($_POST['pass'])) {
                if (isset($_POST['is_ftp'])) {
                    shell_exec("wget $ssl --ftp-user {$_POST['user']} --ftp-password {$_POST['pass']} -O {$_POST['save_as']} {$_POST['url']} > wgetJob.txt  2>&1");
                } else {
                    shell_exec("wget $ssl --user {$_POST['user']} --password {$_POST['pass']} -O {$_POST['save_as']} {$_POST['url']} > wgetJob.txt  2>&1");
                }
            } else {
                shell_exec("wget $ssl -O {$_POST['save_as']} {$_POST['url']} > wgetJob.txt  2>&1");
            }
        }
    }
} else {
    echo wgetProgress('wgetJob.txt');
}

function wgetProgress(string $filename)
{
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    $file = file($filename);
    $file_count = count($file);
    for ($i = max(0, ($file_count - 2)); $i < ($file_count - 1); $i++) {//Get second last line
        $line_split = explode(' ', trim(preg_replace('/\s\s+/', ' ', preg_replace('!\s+!', ' ', $file[$i]))));
        $percent = str_replace('%', '', $line_split[6]);
        if ($percent == 'saved') {
            return json_encode(array('percent' => 100, 'speed' => '', 'remaining' => 0));
        } else {
            $speed = $line_split[7];
            $remaining = $line_split[8];
            return json_encode(array('percent' => intval($percent), 'speed' => $speed, 'remaining' => $remaining));
        }
    }
}
