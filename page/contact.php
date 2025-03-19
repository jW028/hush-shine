<?php
require '../_base.php';

/*if (is_post()) {
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
}*/



$_title = 'Contact Us';
include '../_head.php';
?>
<div class="contact-banner">
    <img src="/images/contact-banner.png">
    <div class="contact-banner-text">
        <h1>Hush & Shine Jewelry</h1>
        <p>There&rsquo;s no question too small or request
          too big for our Tiffany client advisors. 
          From choosing an engagement ring or gift to 
          providing in-store or virtual appointments, 
          we&rsquo;re always at your service. </p>
    </div>
</div>

<div class="contact-us">
    <div class="contact-container">
        <h1>Contact Us</h1>
        <div class="contact-links">
            <a href="">Contact a Jewelry Expert</a>
            <a href="">Email hush&shine123@gmail.com</a>
            <a href="">Call 012-345-6789</a>
        </div>
    </div>

    <div class="businesshours">
        <h1>Business Hours :</h1><p>Mon-Fri: 9 AM - 6 PM </p>
        <p>  Sat-Sun: 10 AM - 4 PM</p>
    </div>
</div>

<div class="aboutus">
    <h1>About Us</h1>
    <p>
        At <strong>Hush &amp; Shine</strong>, 
        we believe that jewelry is more than just an 
        accessory—it&rsquo;s a reflection of your personality, 
        a symbol of love, and a timeless keepsake.  
        Our passion lies in crafting elegant, 
        high-quality pieces that blend sophistication with meaning. 
        Each creation is designed to complement your unique style, whether it's a delicate everyday piece or a bold statement for special occasions.  
    </p>

    <p>
        We are committed to using ethically sourced diamonds, 
        premium metals, and expert craftsmanship to ensure that 
        every piece is not just beautiful, but also responsibly made.  
        Our artisans bring together traditional techniques 
        and modern innovation to create jewelry that stands 
        the test of time. Whether you&rsquo;re looking 
        for a meaningful gift, a custom design, or the 
        perfect engagement ring, we are here to make every 
        moment shine.  
    </p>

    <p>
        Discover the elegance of <strong>Hush &amp; 
        Shine</strong>—where luxury meets artistry, 
        and every piece tells a story.  
    </p>

</div>
<div class="map-questions">
    <div class="map">
        <h1>Find Us At:</h1>
        <p>Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang, Setapak, 53300 Kuala Lumpur, Federal Territory of Kuala Lumpur</p>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.5377912546205!2d101.72398754032146!3d3.2152605527542732!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc3843bfb6a031%3A0x2dc5e067aae3ab84!2sTunku%20Abdul%20Rahman%20University%20of%20Management%20and%20Technology%20(TAR%20UMT)!5e0!3m2!1sen!2smy!4v1742222020351!5m2!1sen!2smy" 
            width="400" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    <div class="questions">
            
    </div>
</div>

<!--<form method="post" class="form">
    <label for="name">Name</label>
    <?= html_text('name', 'maxlength="100"') ?>
    <?= err('name') ?>

    <label for="email">Email</label>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>

    <label for="phone">Phone Number</label>
    <div class="phoneinput">
        <?= html_select('phone', $_countrycode) ?>
        <?= html_text('phone', 'maxlength="15"') ?>
    </div>
    
    <?= err('phone') ?>
    <div class="formbuttons">
        <button>Submit</button>
        <button type="reset">Reset</button>
    </div>
</form>-->

<?php
include '../_foot.php';