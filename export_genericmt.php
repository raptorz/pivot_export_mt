<?php

// $Id: export_genericmt.php 729 2007-10-17 19:16:37Z hansfn $

if (!file_exists("pv_core.php")) {
    die("FATAL ERROR - pv_core.php not found. <br><br>This import script must be placed in the pivot folder.");
} else {    
    require_once("pv_core.php");
}

set_time_limit(0);

$mt_exp_entries = array();

// -------
function start_conversion() {
    global $db, $mt_exp_entries;
	
    // open the db, make sure it's updated..
    if (!isset($db)) {
        $db = new db();
        // $db->generate_index();
    }
    $entries = $db->getlist($db->get_entries_count());
    foreach ($entries as $entry) {
        $data = $db->read_entry($entry['code']);
        write_mtentry($data);
    }
    header("Content-disposition: attachment; filename=pivot_mtexport.txt");
    header("Content-type: text/plain");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo implode("\n--------\n",$mt_exp_entries);
    echo ("\n--------\n");
}

// This is where the actual parsing of the entry happens..
function write_mtentry($entry) {
    global $mt_exp_entries;
    $text = array();
    // Handling the actual entry
    mt_exp_set_current_weblog($entry['category']);
    if ($entry['subtitle']!='') {
        $entry['title'] .= ' - '.$entry['subtitle'];
    }
    $text[] = 'TITLE: '.$entry['title'];
    $text[] = 'AUTHOR: '.$entry['user'];
    $text[] = 'DATE: '.mt_exp_fixdate($entry['date']);
    foreach ($entry['category'] as $category) {
        $text[] = 'CATEGORY: '.$category;
    }
    if ($entry['status'] != 'publish') {
        $text[] = 'STATUS: publish';
    } else {
        $text[] = 'STATUS: draft';
    }
    $text[] = 'ALLOW COMMENTS: '.$entry['allow_comments'];
    $text[] = 'CONVERT BREAKS: '.$entry['convert_lb'];
    $text[] = '-----';
    $text[] = 'BODY:';
    $text[] = parse_intro_or_body($entry['body']);
    $text[] = '-----';
    $text[] = 'EXTENDED BODY:';
    $text[] = parse_intro_or_body($entry['introduction']);
    $text[] = '-----';
    // Handling the comments
    if (!is_array($entry['comments'])) {
        $entry['comments'] = array();
    } 
    foreach ($entry['comments'] as $comment) {
        $text[] = 'COMMENT:';
        $text[] = 'AUTHOR: '.$comment['name'];
        if (!empty($comment['email'])) {
            $text[] = 'EMAIL: '.$comment['email'];
        }
        if (!empty($comment['url'])) {
            $text[] = 'URL: '.$comment['url'];
        }
        $text[] = 'IP: '.$comment['ip'];
        $text[] = 'DATE: '.mt_exp_fixdate($comment['date']);
        $text[] = $comment['comment'];
        $text[] = '-----';
    }
    // Handling the trackbacks
    if (!is_array($entry['trackbacks'])) {
        $entry['trackbacks'] = array();
    } 
    foreach ($entry['trackbacks'] as $trackback) {
        $text[] = 'PING:';
        $text[] = 'TITLE: '.$trackback['title'];
        $text[] = 'URL: '.$trackback['url'];
        $text[] = 'IP: '.$trackback['ip'];
        $text[] = 'BLOG NAME: '.$trackback['name'];
        $text[] = 'DATE: '.mt_exp_fixdate($trackback['date']);
        $text[] = $trackback['excerpt'];
        $text[] = '-----';
    }
    // Add this entry to export array
    $mt_exp_entries[] = implode("\n",$text);
}

/** 
 * Converts from Pivot to MT date
 *
 * Pivot date format is "2007-01-31-17-20".
 * MT date format is "01/31/2007 17:20:00".
 */
function mt_exp_fixdate($date) {
    list($year,$month,$day,$hour,$minute) = explode('-',$date);
    return "$month/$day/$year $hour:$minute:00";
}

/**
 * Sets the current weblog for a category - selects the first if multiple matches.
 */
function mt_exp_set_current_weblog($category) {
    global $Current_weblog;
    $in_weblogs = find_weblogs_with_cat($category);
    $Current_weblog = $in_weblogs[0];
}

function show_form() {
	$self = $_SERVER['PHP_SELF'];
	echo "<form method='get' action='$self'>";
	echo "<input type='hidden' name='action' value='export' />";
	echo "<input type='submit' value='Export!' /></form>";
}


// -------- Main ----------

?>

<?php

if (isset($_GET) && ($_GET['action'] == 'export')) {
    start_conversion();
} else {

    echo <<<EOM
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>The quick-and-dirty Pivot to MT export script</title>
</head>
<body>
<h1>Welcome to the quick-and-dirty Pivot to MT export script</h1>
<p>Use this to export entries from Pivot to the generic MT export format.
(<a href="http://www.movabletype.org/documentation/appendices/import-export-format.html">Info on the format</a>)</p>
EOM;

    show_form();
    
    echo <<<EOM
</body>
</html>
EOM;

}


?>

