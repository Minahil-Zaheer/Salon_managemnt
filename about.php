<?php
session_start();
require_once 'config.php';
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS site_content (
  content_key VARCHAR(60) NOT NULL PRIMARY KEY,
  content_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$aboutParagraph = 'At Elegance Salon, every visit starts with listening. Our team helps you choose a service that suits your style, comfort, and goals, then takes care of the details in a calm and friendly setting.';
$aboutResult = mysqli_query($conn, "SELECT content_value FROM site_content WHERE content_key = 'about_paragraph' LIMIT 1");
if ($aboutResult && ($aboutRow = mysqli_fetch_assoc($aboutResult))) {
  $aboutParagraph = $aboutRow['content_value'];
}
?> 
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About Elegance Salon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>

<body>
  <header class="main-nav py-3">
    <div class="container d-flex justify-content-between align-items-center">
      <a class="navbar-brand" href="index.php"><span class="brand-icon"><i class="bi bi-flower1"></i></span>Elegance <span class="text-accent">Salon</span></a>
      <a class="btn btn-outline-accent btn-sm" href="index.php"><i class="bi bi-house me-1"></i>Home</a>
    </div>
  </header>
  <main>
    <section class="page-title-band">
      <div class="container py-5"><span class="eyebrow text-white-50">Our story</span>
        <h1 class="display-4 mt-2 text-white">About Elegance Salon</h1>
        <p class="lead text-white-50 mb-0">A welcoming place to feel cared for, confident, and refreshed.</p>
      </div>
    </section>
    <section class="container py-5">
      <div class="row align-items-center g-5">
        <div class="col-lg-6"><img src="https://sspark.genspark.ai/i/NtCnFrbBhRhV1vHN" alt="The welcoming interior of Elegance Salon" class="img-fluid rounded-4 shadow"></div>
        <div class="col-lg-6">
          <span class="eyebrow">Beauty with a personal touch</span>
          <h2 class="display-6 mt-2">A little time for yourself</h2>
          <p><?php echo htmlspecialchars($aboutParagraph, ENT_QUOTES, 'UTF-8'); ?></p>
          <p>From hair and color to nail care, facials, and makeup, we want every guest to leave feeling refreshed and confident. We use professional products and keep cleanliness and thoughtful service at the center of every appointment.</p>
          <p>We believe a salon visit should feel easy from the moment you arrive. You can ask questions, share what you like, and work with your stylist or therapist on a result that feels right for you.</p>
        </div>
      </div>
      <div class="row g-4 mt-4">
        <div class="col-md-4">
          <div class="feature-card h-100"><span class="feature-icon"><i class="bi bi-heart"></i></span>
            <h3 class="h5">Personal care</h3>
            <p class="small mb-0">We take time to understand what you want from each visit and explain the options before we begin.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card h-100"><span class="feature-icon"><i class="bi bi-stars"></i></span>
            <h3 class="h5">Thoughtful service</h3>
            <p class="small mb-0">Our team gives each guest attention throughout the consultation, service, and finishing touches.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card h-100"><span class="feature-icon"><i class="bi bi-shield-check"></i></span>
            <h3 class="h5">Comfort and cleanliness</h3>
            <p class="small mb-0">A tidy, welcoming environment and careful hygiene are part of the experience.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-5 bg-soft border-top border-bottom" style="border-color:var(--es-line)!important">
      <div class="container py-3">
        <div class="text-center mb-5">
          <span class="eyebrow">Your visit</span>
          <h2 class="display-6 mt-2">Care from the first conversation</h2>
          <p class="text-muted mx-auto" style="max-width:650px">We want you to know what to expect and feel comfortable speaking up at every step.</p>
          <div class="divider-accent mx-auto"></div>
        </div>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card h-100 p-4"><span class="eyebrow">01</span>
              <h3 class="h5 mt-2">We listen</h3>
              <p class="mb-0">Tell us what you have in mind, what you want to maintain, and anything you would like us to keep in mind. We can talk through the service before getting started.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 p-4"><span class="eyebrow">02</span>
              <h3 class="h5 mt-2">We personalize</h3>
              <p class="mb-0">Your stylist or therapist adapts the service to your preferences and explains practical choices such as finish, color direction, or treatment feel.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 p-4"><span class="eyebrow">03</span>
              <h3 class="h5 mt-2">We finish with care</h3>
              <p class="mb-0">Before you leave, we make time to check the result, answer questions, and share simple care tips that can help you enjoy it at home.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container py-5">
      <div class="row g-5 align-items-center">
        <div class="col-lg-5">
          <span class="eyebrow">Our promise</span>
          <h2 class="display-6 mt-2">A welcoming place for every guest</h2>
        </div>
        <div class="col-lg-7">
          <p>Whether you are visiting for a quick refresh or planning a full occasion look, we aim to make the experience relaxed and clear. You should feel welcome to ask about the service, timing, products, and starting price before your appointment.</p>
          <p class="mb-0">Your preferences matter. If you have a sensitivity, a particular comfort need, or a result you want to avoid, let the team know during your consultation so we can discuss the best way to proceed.</p>
        </div>
      </div>
    </section>
  </main>
  <footer class="site-footer mt-auto">
    <div class="container py-4 d-flex flex-column flex-md-row justify-content-between gap-2"><span>Elegance Salon</span><a href="index.php" class="text-white">Back to home</a></div>
  </footer>
</body>

</html>