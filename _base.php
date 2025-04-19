<?php

// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
// TODO
session_start();
// ============================================================================
// General Page Functions
// ============================================================================

// Database connection parameters
$db_host = 'localhost';
$db_name = 'test';
$db_user = 'root';
$db_pass = '';
// Connect to database
$_db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// Global user object
$_user = $_SESSION['user'] ?? null;

// Authorization
function auth(...$roles) {
    global $_user;
    if ($_user) {
        if ($roles) {
            if (in_array($_user, $roles)) {
                return;
            }
        } else {
            return;
        }
    }
    redirect('login.php');
}

// Is GET request?
if (!function_exists('is_get')) {
    function is_get() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}


 // Obtain GET parameter
function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function encode($value) {
    return htmlentities($value);
}

// Generate <input type='text'>
function html_text($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false) {
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}

// ============================================================================
// Global Constants and Variables
// ============================================================================

$_countrycode = [
    '+1' => 'USA(+1)',
    '+44' => 'UK(+44)',
    '+61' => 'Australia(+61)',
    '+91' => 'India(+91)',
    '+81' => 'Japan(+81)',
    '+60' => 'Malaysia(+60)'
];

$_expert = [
    'Pendant' => 'Pendant Expert',
    'Ring' => 'Ring Expert',
    'Brooch' => 'Brooch Expert',
    'Earring' => 'Earring Expert',
    'Watch' => 'Watch Expert'
];