<?php
require '../_base.php';

if (is_post()) {
    $expert     = req('expert');
    $name       = req('name');
    $email      = req('Email');
    $phone      = req('phone');
    //Validate Expert
    if ($expert == '') {
        $_err['name'] = 'Required';
    } else if (!array_key_exists($expert, $_expert)) {
        $_err['phone'] = 'Invalid value';
    }
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
<div class="cont-slider" data-aos="fade-up">
        <div class="list">
            <div class="cont-item active">
                <img src="/images/banner-image/cont_img2.webp">
                <div class="content">
                    <p>Hush &amp; Shine</p>
                    <h2>Pure Elegance</h2>
                    <p>
                        Crafted with precision, our jewelry embodies grace and sophistication for every occasion.
                    </p>
                </div>
            </div>
            <div class="cont-item">
                <img src="/images/banner-image/cont_img3.webp">
                <div class="content">
                    <p>Hush &amp; Shine</p>
                    <h2>Quiet Luxury</h2>
                    <p>
                        Subtle yet striking, each piece enhances your style with effortless charm and refinement.
                    </p>
                </div>
            </div>
            <div class="cont-item">
                <img src="/images/banner-image/cont_img4.webp">
                <div class="content">
                    <p>Hush &amp; Shine</p>
                    <h2>Radiant Beauty</h2>
                    <p>
                        Designed to shine, our collections celebrate individuality and the art of fine craftsmanship.
                    </p>
                </div>
            </div>
            <div class="cont-item">
                <img src="/images/banner-image/cont_img5.webp">
                <div class="content">
                    <p>Hush &amp; Shine</p>
                    <h2>Enduring Quality</h2>
                    <p>
                        Made from the finest materials, our jewelry is created to last a lifetime and beyond.
                    </p>
                </div>
            </div>
            <div class="cont-item">
                <img src="/images/banner-image/cont_img6.webp">
                <div class="content">
                    <p>Hush &amp; Shine</p>
                    <h2>Inspired by You</h2>
                    <p>
                        Every design reflects your elegance, empowering confidence with every wear.
                    </p>
                </div>
            </div>
        </div>
        <div class="arrows">
            <button id="prev"><</button>
            <button id="next">></button>
        </div>
        <div class="thumbnail">
            <div class="cont-item active">
                <img src="/images/banner-image/cont_img2.webp">
            </div>
            <div class="cont-item">
                <img src="/images/banner-image/cont_img3.webp">
            </div>
            <div class="cont-item">
                <img src="/images/banner-image/cont_img4.webp">
            </div>
            <div class="cont-item">
                <img src="/images/banner-image/cont_img5.webp">
            </div>
            <div class="cont-item">
                <img src="/images/banner-image/cont_img6.webp">
            </div>
        </div>
    </div>

<div class="aboutus" data-aos="fade-up">
    <h3 class="aboutus_title" data-aos="fade-down">About Us</h3>
    <div class="aboutus_content">
        <div class="aboutus_item" data-aos="fade-right">
            <img src="/images/product_img/earring4/heart_ear2.webp" alt="Jewelry Box">
            <div class="aboutus_text" data-aos="fade-left">
                <h3>Jewelry is More Than an Accessory</h3>
                <p>
                    At Hush &amp; Shine, we believe that jewelry is more than just an accessory—it&rsquo;s a 
                    reflection of your personality, a symbol of love, and a timeless keepsake. Our passion 
                    lies in crafting elegant, high-quality pieces that blend sophistication with meaning.
                </p>
            </div>
        </div>

        <div class="aboutus_item" data-aos="fade-left" data-aos-delay="200">
            <div class="aboutus_text" data-aos="fade-right">
                <h3>Designed for Every Style & Occasion</h3>
                <p>
                Each creation is designed to complement your unique style, whether it&rsquo;s a delicate everyday 
                piece or a bold statement for special occasions. We aim to create jewelry that adds a touch of 
                elegance to your moments, making them even more special.
                </p>
            </div>
            <img src="/images/product_img/ring3/perettidiamond_ring2.webp" alt="Gold Necklace">
        </div>

        <div class="aboutus_item" data-aos="fade-left" data-aos-delay="300">
            <img src="/images/product_img/ring5/Red_ring1.jpg" alt="Luxury Jewelry">
            <div class="aboutus_text" data-aos="fade-right">
                <h3>Ethically Sourced, Expertly Crafted</h3>
                <p>
                We are committed to using ethically sourced diamonds, premium metals, 
                and expert craftsmanship to ensure that every piece is not just beautiful, but 
                also responsibly made. Our artisans bring together traditional techniques and modern 
                innovation to craft jewelry that stands the test of time. Whether you&rsquo;re looking for 
                a meaningful gift, a custom design, or the perfect engagement ring, we are here to make every moment shine.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Team Section -->
<div class="about-container">
    <div class="about-header" data-aos="fade-up">
        <h1>Meet Our Team of Experts</h1>
        <p>Professional Craftsmen with Years of Experience</p>
    </div>

    <div class="team-section" data-aos="fade-up">
        <div class="team-grid">
            <div class="team-card">
                <div class="team-image">
                    <img src="/images/team_img/tungsahur.png" alt="Pendant Expert">
                </div>
                <div class="team-info">
                    <h3>Tung Tung Tung Sahur</h3>
                    <p class="team-role">Pendant Expert</p>
                    <p class="team-desc">With 15 years of experience in pendant design and craftsmanship.</p>
                </div>
            </div>

            <div class="team-card">
                <div class="team-image">
                    <img src="/images/team_img/tralalelotralala.png" alt="Brooch Expert">
                </div>
                <div class="team-info">
                    <h3>Tralalero Tralala</h3>
                    <p class="team-role">Brooch Expert</p>
                    <p class="team-desc">Specializing in vintage and contemporary brooch designs.</p>
                </div>
            </div>

            <div class="team-card">
                <div class="team-image">
                    <img src="/images/team_img/boneca.png" alt="Watch Expert">
                </div>
                <div class="team-info">
                    <h3>Boneca Ambalabu</h3>
                    <p class="team-role">Watch Expert</p>
                    <p class="team-desc">Master watchmaker with expertise in luxury timepieces.</p>
                </div>
            </div>

            <div class="team-card">
                <div class="team-image">
                    <img src="/images/team_img/BombardiroCrocodilo.png" alt="Ring Expert">
                </div>
                <div class="team-info">
                    <h3>Bombardiro Crocodiro</h3>
                    <p class="team-role">Ring Expert</p>
                    <p class="team-desc">Specialized in engagement and wedding ring customization.</p>
                </div>
            </div>

            <div class="team-card">
                <div class="team-image">
                    <img src="/images/team_img/capuccino.png" alt="Earring Expert">
                </div>
                <div class="team-info">
                    <h3>Capuccino Assassino</h3>
                    <p class="team-role">Earring Expert</p>
                    <p class="team-desc">Expert in contemporary earring designs and trends.</p>
                </div>
            </div>

            <div class="team-card">
                <div class="team-image">
                    <img src="/images/team_img/IU.png" alt="Head Gemologist">
                </div>
                <div class="team-info">
                    <h3>Lee Ji Eun</h3>
                    <p class="team-role">Head Gemologist</p>
                    <p class="team-desc">Certified gemologist with expertise in stone grading.</p>
                </div>
            </div>

            <div class="team-card">
                <div class="team-image">
                    <img src="/images/team_img/chaeunwoo.png" alt="Lead Designer">
                </div>
                <div class="team-info">
                    <h3>Cha Eun Woo</h3>
                    <p class="team-role">Lead Designer</p>
                    <p class="team-desc">Award-winning jewelry designer and creative director.</p>
                </div>
            </div>

            <div class="team-card">
                <div class="team-image">
                    <img src="/images/team_img/superidol.png" alt="Quality Control Manager">
                </div>
                <div class="team-info">
                    <h3>SuperIdol</h3>
                    <p class="team-role">Quality Control Manager</p>
                    <p class="team-desc">Ensures highest standards in all our pieces.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="contact-us" data-aos="fade-up">
    <div class="contact-container">
        <h1 data-aos="fade-down">Contact Us</h1>
        <div class="contact-columns">
            <div class="cont-form-column" data-aos="zoom-in">
                <div class="expert">
                    <h3>Contact a Jewelry Expert</h3>
                    <form method="post" class="cont-form">
                        <div class="cont-form-group">
                            <label for="expert">Expert Type</label>
                            <div class="cont-expert-input">
                                <?= html_select('expert', $_expert, ['class' => 'cont-form-control']) ?>
                                <?= err('expert') ?>
                            </div>
                        </div>

                        <div class="cont-form-group">
                            <label for="name">Name</label>
                            <?= html_text('name', 'class="cont-form-control" maxlength="100"') ?>
                            <?= err('name') ?>
                        </div>

                        <div class="cont-form-group">
                            <label for="email">Email</label>
                            <?= html_text('email', 'class="cont-form-control" maxlength="100"') ?>
                            <?= err('email') ?>
                        </div>

                        <div class="cont-form-group">
                            <label for="phone">Phone Number</label>
                            <div class="cont-phoneinput">
                                <!-- <?= html_select('phone', $_countrycode, ['class' => 'cont-country-code']) ?>
                                <?= html_text('phone', 'class="cont-form-control" maxlength="15"') ?> -->

                                <div class="phone-country-code">
                                    <?= html_select('phone', $_countrycode, ['class' => 'cont-country-code']) ?>
                                </div>
                                <div class="phone-num">
                                    <?= html_text('phone', 'class="cont-form-control" maxlength="15"') ?>
                                </div>
                            </div>
                            <?= err('phone') ?>
                        </div>

                        <div class="cont-formbuttons">
                            <button class="cont-btn-primary">Submit</button>
                            <button type="reset" class="cont-btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="contact-methods">
                <div class="contact-method">
                    <i class="fas fa-envelope"></i>
                    <a href="">hush&shine123@gmail.com</a>
                 </div>
                <div class="contact-method">
                    <i class="fas fa-phone"></i>
                    <a href="">012-345-6789</a>
                 </div>
                 <div class="contact-method">
                    <i class="fas fa-location-dot"></i>
                    <a href="">Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang, Setapak, 53300 Kuala Lumpur, Federal Territory of Kuala Lumpur</a>
                 </div>
            </div>
        </div>
    </div>
</div>

<section class="faq-container">
        <h2 class="faq-heading">Frequently Asked Questions</h2>
        
        <div class="faq-item">
            <div class="faq-question">
                <span>How do I create an account?</span>
            </div>
            <div class="faq-answer">
                <p>To create an account, click on the "Sign Up" button at the top right corner of the page and follow the registration process.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>What payment methods do you accept?</span>
            </div>
            <div class="faq-answer">
                <p>We accept all major credit cards including Visa, MasterCard, and American Express. We also support PayPal payments.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>How can I reset my password?</span>
            </div>
            <div class="faq-answer">
                <p>Click on "Forgot Password" on the login page and follow the instructions sent to your registered email address.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>Do you offer refunds?</span>
            </div>
            <div class="faq-answer">
                <p>Yes, we offer a 30-day money-back guarantee for all our premium plans. Contact our support team for assistance.</p>
            </div>
        </div>
</section>

<div class="shop" data-aos="fade-up">
    <div class="shop-content">
        <div class="map-text" data-aos="fade-right">
            <h1>Find Us At:</h1>
            <p>Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), 
                Jalan Genting Kelang, Setapak, 53300 Kuala Lumpur, 
                Federal Territory of Kuala Lumpur</p>
        </div>

        <div class="map-container" data-aos="zoom-in">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.5377912546205!2d101.72398754032146!3d3.2152605527542732!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc3843bfb6a031%3A0x2dc5e067aae3ab84!2sTunku%20Abdul%20Rahman%20University%20of%20Management%20and%20Technology%20(TAR%20UMT)!5e0!3m2!1sen!2smy!4v1742222020351!5m2!1sen!2smy"
                width="400" height="400" allowfullscreen="" loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <div class="business-hours">
            <h1>Business Hours:</h1>
            <p>Mon-Fri: 9 AM - 6 PM</p>
            <p>Sat-Sun: 10 AM - 4 PM</p>
        </div>
    </div>
</div>

<?php
include '../_foot.php';