<?php
// based on: https://kywch.github.io/jsPsych-in-Qualtrics/save-php/
// WARNING: the below config can cause a serious security issue.
// Please read https://portswigger.net/web-security/cors/access-control-allow-origin
// Once you are done testing, you should limit the access
//header('Access-Control-Allow-Origin: https://ssd.az1.qualtrics.com');
header('Access-Control-Allow-Origin: *');

if (isset($_POST['data']) == false) { 
    echo('Please provide data with a POST-Request with the data parameter.'); 
    exit; 
}

// data contains the full json/csv data to be saved
$data = $_POST['data'];

// write the file to disk
// NOTE: you must make the data directory accessible to the www-data user:
// chown -R www-data:www-data data
file_put_contents("/var/www/html/data/data.sav", $data);

exit;
?>