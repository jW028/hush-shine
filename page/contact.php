<?php
require '../_base.php';

if (is_post()) {
    $name       = req('name');
    $email      = req('Email');
    $phone      = req('phone');

    //Validate name
    if ($name == '') {
        $_err['name'] = 'Required';
    } else if (strlen($name) > 100) {
        $_err['name'] = 'Invalid value';
    }

    //Validate Email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
        $_err['email'] = 'Invalid format';
    }

    //Validate Phone Number
    if ($phone == '') {
        $_err['phone'] = 'Required';
    } else if (!array_key_exists($phone, $_countrycode) && !preg_match("/^[0-9]{7,15}$/", $phone)) {
        $_err['phone'] = 'Invalid value';
    }
}



$_title = 'Contact Us';
include '../_head.php';
?>

<div class="InformationContainer">
    <h1>Contact Us</h1>
    <p><strong>Address: </strong> 123 SuperIdol Lane, Setapak, Kuala Lumpur</p>
    <p><strong>Phone  :</strong> 012-345-6789</p>
    <p><strong>Email  :</strong> sigma@gmail.com</p>
    <p><strong>Business Hours :</strong> Mon-Fri: 9 AM - 6 PM, Sat-Sun: 10 AM - 4 PM</p>
</div>

<form method="post" class="form">
    <label for="name">Name</label>
    <?= html_text('name', 'maxlength="100"') ?>
    <?= err('name') ?>
    <label for="email">Email</label>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>
    <label for="phone">Phone Number</label>
    <?= html_select('phone', $_countrycode) ?>
    <?= html_text('phone', 'maxlength="15"') ?>
    <?= err('phone') ?>

    <button>Submit</button>
    <button type="reset">Reset</button>
</form>

<?php
include '../_foot.php';