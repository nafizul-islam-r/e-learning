<?php
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
   header('location:login.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Live Classes</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   
   <link rel="stylesheet" href="css/style.css">

   <style>
      /* --- FIX: Center Alignment & Height --- */
      .schedule {
         padding: 2rem;
         width: 100%;
         /* 1. Force height to fill screen minus header/footer */
         min-height: calc(100vh - 15rem);
         
         /* 2. Flexbox to Center the card Vertically and Horizontally */
         display: flex;
         flex-direction: column;
         justify-content: center; /* Centers vertically */
         align-items: center;     /* Centers horizontally */
      }

      /* --- Card Container --- */
      .schedule-card {
         background-color: var(--white);
         border-radius: 1rem;
         padding: 4rem 3rem; /* Increased padding slightly for better look */
         
         /* 3. STRONGER SHADOW HERE */
         box-shadow: 0 2rem 4rem rgba(0,0,0,0.15); 
         
         border-left: 0.6rem solid #8e44ad; 
         width: 100%;
         max-width: 1000px; /* Limit width so it looks nice in center */
         display: block; 
      }

      .heading-wrapper {
         text-align: center;
         margin-bottom: 3rem;
      }
      .main-title {
         font-size: 2.5rem;
         color: var(--black);
         font-weight: 700;
         position: relative;
         display: inline-block;
         padding-bottom: 1rem;
      }
      .main-title::after {
         content: '';
         position: absolute;
         bottom: 0;
         left: 50%;
         transform: translateX(-50%);
         width: 60px;
         height: 4px;
         background-color: #8e44ad;
         border-radius: 5px;
      }

      .flex-header {
         display: flex;
         background-color: #8e44ad;
         padding: 1.5rem;
         border-radius: 0.8rem;
         margin-bottom: 1.5rem;
      }
      .flex-header div {
         flex: 1;
         color: #fff;
         font-size: 1.6rem;
         font-weight: 600;
         text-align: center;
      }
      .flex-header .col-large { flex: 2; text-align: left; padding-left: 1rem; }

      .flex-row {
         display: flex;
         align-items: center;
         background-color: var(--white);
         padding: 1.5rem;
         border-bottom: 1px solid #eee;
         transition: .2s ease;
      }
      .flex-row:hover { background-color: #f9f9f9; }
      .flex-row div {
         flex: 1;
         font-size: 1.5rem;
         color: var(--light-color);
         text-align: center;
      }
      .flex-row .col-large { 
         flex: 2; 
         text-align: left; 
         padding-left: 1rem; 
         color: var(--black); 
         font-weight: 600;
      }
      
      .tutor-name {
         display: block;
         font-size: 1.3rem;
         color: #777;
         font-weight: normal;
         margin-top: 0.5rem;
      }

      .btn-join {
         display: inline-block;
         background-color: #8e44ad;
         color: white;
         padding: 0.8rem 2rem;
         border-radius: 0.5rem;
         font-size: 1.4rem;
         text-decoration: none;
         transition: 0.3s;
      }
      .btn-join:hover { background-color: #732d91; }
      
      .btn-waiting {
         display: inline-block;
         background-color: #f1c40f;
         color: #fff;
         padding: 0.8rem 1.5rem;
         border-radius: 0.5rem;
         font-size: 1.3rem;
         cursor: not-allowed;
      }
      
      .status-ended {
         color: #e74c3c;
         font-weight: bold;
         font-size: 1.4rem;
      }

      .empty-state {
         text-align: center;
         padding: 4rem 2rem;
      }
      .empty-icon {
         height: 80px;
         width: 80px;
         background-color: #e9d5ff; 
         color: #8e44ad;
         line-height: 80px;
         font-size: 3.5rem;
         border-radius: 50%;
         margin: 0 auto 2rem auto;
      }
      .empty-text {
         font-size: 1.8rem;
         color: #666;
         margin-bottom: 3rem;
      }
      .separator {
         height: 1px;
         background-color: #eee;
         width: 90%;
         margin: 0 auto;
      }

      @media (max-width: 768px) {
         .flex-header { display: none; }
         .flex-row {
            flex-direction: column;
            gap: 1rem;
            border: 1px solid #eee;
            margin-bottom: 1rem;
            border-radius: .5rem;
            padding: 2rem;
         }
         .flex-row div, .flex-row .col-large { 
            text-align: center; 
            width: 100%; 
            padding: 0;
         }
         .col-start::before { content: 'Start: '; font-weight: bold; color: #333; }
         .col-dur::before { content: 'Duration: '; font-weight: bold; color: #333; }
      }
   </style>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="schedule">

   <div class="schedule-card">

      <div class="heading-wrapper">
         <h1 class="main-title">Upcoming Live Classes</h1>
      </div>

      <?php
      date_default_timezone_set('Asia/Dhaka'); 
      $now = date('Y-m-d H:i:s');

      $stmt = $conn->prepare("SELECT lc.*, t.name AS tutor_name FROM live_classes lc 
                              JOIN tutors t ON t.id = lc.tutor_id
                              ORDER BY lc.start_time ASC");
      $stmt->execute();
      $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (count($classes) > 0) {
      ?>
         <div class="flex-header">
            <div class="col-large">Title & Tutor</div>
            <div>Start Time</div>
            <div>Duration</div>
            <div>Action</div>
         </div>

         <div class="class-list">
            <?php
            foreach ($classes as $class) {
               $class_start = $class['start_time'];
               $class_end = date('Y-m-d H:i:s', strtotime($class_start . ' +' . $class['duration'] . ' minutes'));
               $display_date = date('d M, h:i A', strtotime($class_start));

               if ($now < $class_start) {
                  $action = "<button class='btn-waiting'>Starts " . date('h:i A', strtotime($class_start)) . "</button>";
               } elseif ($now >= $class_start && $now <= $class_end) {
                  $action = "<a href='{$class['join_url']}' target='_blank' class='btn-join'><i class='fas fa-video'></i> Join Live</a>";
               } else {
                  $action = "<span class='status-ended'>Ended</span>";
               }
            ?>
               <div class="flex-row">
                  <div class="col-large">
                     <?= htmlspecialchars($class['title']); ?>
                     <span class="tutor-name">by <?= htmlspecialchars($class['tutor_name']); ?></span>
                  </div>
                  <div class="col-start"><?= $display_date; ?></div>
                  <div class="col-dur"><?= $class['duration']; ?> min</div>
                  <div><?= $action; ?></div>
               </div>
            <?php } ?>
         </div>

      <?php } else { ?>

         <div class="empty-state">
            <div class="empty-icon">
               <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <p class="empty-text">No live classes scheduled yet.</p>
            <div class="separator"></div>
         </div>

      <?php } ?>

   </div>

</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>