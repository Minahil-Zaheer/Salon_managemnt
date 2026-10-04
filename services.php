<?php
session_start();
$book_link = isset($_SESSION['user_id']) ? 'appointments.php' : 'login.php';
$service_list = array(
    array('Haircut', 'A clean, personalized cut shaped to suit your face, hair texture, and everyday routine.', '30 min', '$30', 'A brief consultation, hair sectioning, a tailored cut, and a finished look.', 'A style refresh or a change in shape and length.'),
    array('Hair Styling', 'A polished blowout or finished style for an everyday refresh or a special occasion.', '45 min', '$45', 'A quick style consultation, heat protection, blow-dry or styling, and finishing touches.', 'Events, celebrations, or a smooth everyday style.'),
    array('Hair Coloring', 'A color consultation followed by a carefully applied shade refresh or full color service.', '90 min', '$85', 'A shade discussion, preparation, color application, rinse, and basic finish.', 'Guests refreshing their color or exploring a new shade. Complex color work may need a longer appointment.'),
    array('Facial', 'A relaxing cleanse, gentle exfoliation, and hydrating mask selected for your skin needs.', '60 min', '$60', 'A skin check-in, cleanse, gentle exfoliation, mask, and moisturizer.', 'A calm skin refresh. Tell your therapist about sensitivities before treatment.'),
    array('Manicure', 'Nail shaping, cuticle care, hand care, and your choice of a classic polish finish.', '40 min', '$25', 'Nail shaping, cuticle care, hand moisturizer, and classic polish.', 'A tidy-up or a simple polish refresh.'),
    array('Pedicure', 'A soothing foot soak, nail care, exfoliation, and a fresh polish finish.', '50 min', '$35', 'A foot soak, nail shaping, cuticle care, gentle exfoliation, and classic polish.', 'Routine foot care and a relaxing break.'),
    array('Makeup', 'A personalized makeup look for events, celebrations, or a professional photo session.', '60 min', '$70', 'A look discussion, skin preparation, makeup application, and final touch-up.', 'Special occasions, celebrations, or photos. Bring reference images if you have a preferred look.')
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Salon Services - Elegance Salon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    .public-top { background: rgba(250,246,240,.96); border-bottom: 1px solid var(--es-line); }
    .service-detail-card { background: #fff; border: 1px solid var(--es-line); border-radius: 1rem; padding: 1.5rem; height: 100%; box-shadow: var(--es-shadow); }
    .service-detail-card .service-icon { width: 48px; height: 48px; border-radius: 50%; background: rgba(197,157,95,.15); color: var(--es-gold-dark); display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; }
    .service-detail-card button { color: inherit; }
  </style>
</head>
<body>
  <header class="public-top py-3">
    <div class="container d-flex justify-content-between align-items-center">
      <a class="navbar-brand" href="index.php"><span class="brand-icon"><i class="bi bi-flower1"></i></span>Elegance <span class="text-accent">Salon</span></a>
      <a class="btn btn-outline-accent btn-sm" href="index.php"><i class="bi bi-house me-1"></i>Home</a>
    </div>
  </header>

  <main>
    <section class="page-title-band">
      <div class="container py-5">
        <span class="eyebrow text-white-50">Care made personal</span>
        <h1 class="display-4 mt-2 text-white">Our Services</h1>
        <p class="lead text-white-50 mb-0">Choose a treatment, see what is included, and plan a visit that suits you.</p>
      </div>
    </section>

    <section class="container py-5">
      <div class="row g-4">
        <?php foreach ($service_list as $service) { ?>
          <div class="col-md-6 col-lg-4">
            <article class="service-detail-card d-flex flex-column">
              <span class="service-icon mb-3"><i class="bi bi-stars"></i></span>
              <h2 class="h4"><?php echo $service[0]; ?></h2>
              <p class="text-muted flex-grow-1"><?php echo $service[1]; ?></p>
              <div class="d-flex justify-content-between small mb-3"><span><?php echo $service[2]; ?></span><strong>From <?php echo $service[3]; ?></strong></div>
              <button type="button" class="btn btn-outline-accent" data-service-title="<?php echo $service[0]; ?>" data-service-description="<?php echo $service[1]; ?>" data-service-duration="<?php echo $service[2]; ?>" data-service-price="<?php echo $service[3]; ?>" data-service-includes="<?php echo $service[4]; ?>" data-service-suitable="<?php echo $service[5]; ?>" data-bs-toggle="modal" data-bs-target="#serviceInfoModal">View details</button>
            </article>
          </div>
        <?php } ?>
      </div>
    </section>
  </main>

  <div class="modal fade" id="serviceInfoModal" tabindex="-1" aria-labelledby="serviceInfoTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header"><h2 class="modal-title h5" id="serviceInfoTitle">Service details</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body">
          <p id="serviceInfoDescription" class="mb-3"></p>
          <h3 class="h6 mt-3">What is included</h3>
          <p id="serviceInfoIncludes" class="text-muted"></p>
          <h3 class="h6 mt-3">Good to know</h3>
          <p id="serviceInfoSuitable" class="text-muted"></p>
          <div class="d-flex justify-content-between"><span>Estimated time</span><strong id="serviceInfoDuration"></strong></div>
          <div class="d-flex justify-content-between mt-2"><span>Starting price</span><strong id="serviceInfoPrice"></strong></div>
          <p class="small text-muted mt-3 mb-0">Prices are starting estimates. Final timing and price can vary with hair length, product use, or the treatment selected.</p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><a class="btn btn-accent" id="serviceBookLink" href="<?php echo $book_link; ?>">Book an appointment</a></div>
      </div>
    </div>
  </div>

  <footer class="site-footer mt-auto"><div class="container py-4 d-flex flex-column flex-md-row justify-content-between gap-2"><span>Elegance Salon</span><a href="index.php" class="text-white">Back to home</a></div></footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('[data-service-title]').forEach(function (button) {
      button.addEventListener('click', function () {
        document.getElementById('serviceInfoTitle').textContent = button.getAttribute('data-service-title');
        document.getElementById('serviceInfoDescription').textContent = button.getAttribute('data-service-description');
        document.getElementById('serviceInfoDuration').textContent = button.getAttribute('data-service-duration');
        document.getElementById('serviceInfoPrice').textContent = button.getAttribute('data-service-price');
        document.getElementById('serviceInfoIncludes').textContent = button.getAttribute('data-service-includes');
        document.getElementById('serviceInfoSuitable').textContent = button.getAttribute('data-service-suitable');
        document.getElementById('serviceBookLink').href = '<?php echo $book_link; ?>?service=' + encodeURIComponent(button.getAttribute('data-service-title'));
      });
    });
  </script>
</body>
</html>
