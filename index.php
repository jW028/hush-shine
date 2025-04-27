<?php
require '_base.php';
//-----------------------------------------------------------------------------






// ----------------------------------------------------------------------------
$_title = '';
include '_head.php';
?>
<div class = "slider">
    <div class = "slides">
        <input type="radio" name = "radio-btn" id = "radio1" checked>
        <input type="radio" name = "radio-btn" id = "radio2">
        <input type="radio" name = "radio-btn" id = "radio3">
        <input type="radio" name = "radio-btn" id = "radio4">
        
        <div class="slide first">
            <img src="images/banner-image/banner-1.webp" alt="">
        </div>
        <div class="slide">
            <img src="images/banner-image/banner-2.webp" alt="">
        </div>
        <div class="slide">
            <img src="images/banner-image/banner-3.webp" alt="">
        </div>
        <div class="slide">
            <img src="images/banner-image/banner-4.webp" alt="">
        </div>

        <div class="auto-nav">
            <div class="auto-btn1"></div>
            <div class="auto-btn2"></div>
            <div class="auto-btn3"></div>
            <div class="auto-btn4"></div>
        </div>
    </div>
    <div class="manual-nav">
        <label for = "radio1" class = "manual-btn"></label>
        <label for = "radio2" class = "manual-btn"></label>
        <label for = "radio3" class = "manual-btn"></label>
        <label for = "radio4" class = "manual-btn"></label>
    </div>
</div>

<section class="index-category" data-aos="fade-up">
    <div class="index-container">
        <h2 class="cat-title"data-aos="fade-up">SHOP BY CATEGORY</h2>
        <div class="categories">
            <a href="/page/products.php?category=CT04" data-cat="CT04" class="category-link category" data-aos="zoom-in" data-aos-delay="100">
                <div class="circle">
                    <img src="images/prod_icon/Earring_icon.webp" alt="Earrings">
                </div>
                <button class="category-btn">
                    Earrings
                </button>
            </a>

            <a href="/page/products.php?category=CT01   " data-cat="CT01" class="category-link category" data-aos="zoom-in" data-aos-delay="200">
                <div class="circle">
                    <img src="images/prod_icon/Necklace_icon.webp" alt="Necklaces">
                </div>
                <button class="category-btn gray">
                    Necklaces
                </button>
            </a>

            <a href="/page/products.php?category=CT02" data-cat="CT02" class="category-link category" data-aos="zoom-in" data-aos-delay="300">
                <div class="circle">
                    <img src="images/prod_icon/Bracelet_icon.webp" alt="Bracelet">
                </div>
                <button class="category-btn">
                    Bracelets
                </button>
            </a>

            <a href="/page/products.php?category=CT03" data-cat="CT03" class="category-link category" data-aos="zoom-in" data-aos-delay="400">
                <div class="circle">
                    <img src="images/prod_icon/Rings_icon.webp" alt="Rings">
                </div>
                <button class="category-btn gray">
                    Rings
                </button>
            </a>

            <a href="/page/products.php?category=CT05" data-cat="CT05" class="category-link category" data-aos="zoom-in" data-aos-delay="500">
                <div class="circle">
                    <img src="images/prod_icon/Watches_icon.webp" alt="Watches">
                </div>
                <button class="category-btn">
                    Watches
                </button>
            </a>
        </div>
    </div>
</section>

<div class="carousel-container">
  <div class="image-section" data-aos="fade-right" data-aos-delay="200">
    <div class="slides-wrapper">
      <div class="hover-slide active">
        <img src="/images/product_img/Red_ring1.jpg" alt="">
      </div>
      <div class="hover-slide">
        <img src="/images/product_img/heart_ear2.webp" alt="">
      </div>
      <div class="hover-slide">
        <img src="/images/product_img/pad_pendant2.webp" alt="">
      </div>
      <div class="hover-slide">
        <img src="/images/product_img/perettidiamond_ring2.webp" alt="">
      </div>
    </div>
  </div>

  <div class="text-section"data-aos="fade-left" data-aos-delay="200">
    <h2 class="section-title">Why choose us</h2>
    
    <div class="text-controls">
      <div class="text-item active" data-index="0">
          <p> → Balances subtle luxury with radiant beauty.</p>
      </div>
      
      <div class="text-item" data-index="1">
          <p> → Evokes intimacy, elegance, and timeless style</p>
      </div>
      
      <div class="text-item" data-index="2">
          <p>→ Appeals to minimalists and statement lovers.</p>
      </div>

      <div class="text-item" data-index="3">
          <p>→ Exquisite craftsmanship with effortless sophistication.</p>
      </div>

    </div>
  </div>
</div>

<div class="banner-wrapper"data-aos="fade-right" data-aos-delay="200">
    <div class="content-left">
        <h1>Timeless Elegance</h1>
        <p>Hush & Shine blends quiet luxury with striking brilliance, offering finely 
            crafted jewelry that&apos;s both sophisticated and effortlessly graceful.</p>
        <a href="/page/contact.php">
            <button class="cta-button">About Us</button>
        </a>
    </div>
    <div class="video-right"data-aos="fade-left" data-aos-delay="500">
        <video id="ban"  autoplay muted loop playsinline
               disablepictureinpicture preload="auto"
               src="/page/video/index_video.mp4">
        </video>
    </div>
</div>

<!-- YouTube Video Section -->
<div class="youtube-section" data-aos="fade-up">
    <div class="youtube-container">
        <h2>Watch Our Story</h2>
        <div class="video-wrapper">
            <iframe 
                width="560" 
                height="315" 
                src="https://www.youtube.com/embed/ivzPXht4C5E?si=-ABdoIwU8Z31xiiM" 
                title="YouTube video player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                referrerpolicy="strict-origin-when-cross-origin" 
                allowfullscreen>
            </iframe>
        </div>
    </div>
</div>

<?php
include '_foot.php';


