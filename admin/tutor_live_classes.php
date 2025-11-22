<?php

include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:../login.php');
   exit();
}

// --- HANDLING MESSAGES AFTER REDIRECT ---
if(isset($_GET['msg'])){
   if($_GET['msg'] == 'added'){
      $message[] = 'New live class created!';
   }
   if($_GET['msg'] == 'deleted'){
      $message[] = 'Class schedule deleted!';
   }
}

if(isset($_POST['submit'])){

   $id = unique_id();
   $title = $_POST['title'];
   $title = filter_var($title, FILTER_SANITIZE_STRING);
   $date = $_POST['date'];
   
   // Time Handling
   $hour = $_POST['hour'];
   $minute = $_POST['minute'];
   $ampm = $_POST['ampm'];
   
   $time_12h = "$hour:$minute $ampm";
   $time_24h = date("H:i:s", strtotime($time_12h));
   $start_time = date('Y-m-d H:i:s', strtotime("$date $time_24h"));
   
   $duration = $_POST['duration'];
   $duration = filter_var($duration, FILTER_SANITIZE_STRING);

   $room_name = preg_replace('/[^A-Za-z0-9]/', '', $title) . '_' . unique_id();
   $join_url = "https://meet.jit.si/" . $room_name;
   
   $add_class = $conn->prepare("INSERT INTO `live_classes`(id, tutor_id, title, start_time, duration, join_url) VALUES(?,?,?,?,?,?)");
   $add_class->execute([$id, $tutor_id, $title, $start_time, $duration, $join_url]);
   
   // FIX: Redirect to prevent duplicate submission on reload
   header('Location: tutor_live_classes.php?msg=added');
   exit();
}

if(isset($_POST['delete_class'])){
   $delete_id = $_POST['class_id'];
   $delete_class = $conn->prepare("DELETE FROM `live_classes` WHERE id = ? AND tutor_id = ?");
   $delete_class->execute([$delete_id, $tutor_id]);
   
   // FIX: Redirect to prevent duplicate submission
   header('Location: tutor_live_classes.php?msg=deleted');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manage Live Classes</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <link rel="stylesheet" href="../css/style.css">

   <style>
      /* --- 1. SMOOTH NOTIFICATION STYLES --- */
      .message {
         position: fixed;
         top: 2rem;
         right: 2rem;
         z-index: 1100; 
         background-color: var(--white);
         padding: 1.5rem 2rem;
         border-radius: .5rem;
         box-shadow: 0 .5rem 1rem rgba(0,0,0,.2);
         border-left: .5rem solid #8e44ad; 
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 1.5rem;
         min-width: 300px;
         /* Entrance Animation */
         animation: slideIn 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards; 
         
         /* Smooth Exit Transition settings */
         opacity: 1;
         transform: translateY(0);
         transition: opacity 1.5s ease, transform 1.5s ease; /* Slow fade out (1.5s) */
      }

      /* Class added by JS to trigger fade out */
      .message.hiding {
         opacity: 0;
         transform: translateY(-30px); /* Floats up while fading */
      }

      .message span { font-size: 1.6rem; color: var(--black); }
      .message i { font-size: 2rem; color: #e74c3c; cursor: pointer; }
      .message i:hover { color: var(--black); }

      @keyframes slideIn {
         from { transform: translateX(120%); opacity: 0; }
         to { transform: translateX(0); opacity: 1; }
      }

      /* --- 2. LAYOUT STYLES --- */
      .admin-grid-section {
         padding: 2rem;
         width: 100%;
         min-height: calc(100vh - 10rem); 
         display: flex;
         flex-direction: column;
         justify-content: center;
         align-items: center;
      }

      .main-heading {
         text-align: center;
         font-size: 3rem; 
         color: #8e44ad;
         margin-bottom: 4rem;
         font-weight: bold;
      }

      .grid-container {
         display: grid;
         grid-template-columns: 1fr 1fr; 
         gap: 3rem; 
         width: 100%;
         max-width: 1600px; 
      }

      .card-box {
         background-color: var(--white);
         border-radius: 1rem;
         padding: 4rem; 
         box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
         border-left: 0.6rem solid #8e44ad;
         height: 100%; 
         display: flex;
         flex-direction: column;
      }

      .card-content-wrapper {
         flex: 1;
         display: flex;
         flex-direction: column;
      }

      .card-heading {
         text-align: center;
         font-size: 2.2rem;
         color: var(--black);
         margin-bottom: 3rem;
         position: relative;
         padding-bottom: 1rem;
      }
      .card-heading::after {
         content: '';
         position: absolute;
         bottom: 0;
         left: 50%;
         transform: translateX(-50%);
         width: 60px;
         height: 4px;
         background: #8e44ad;
         border-radius: 5px;
      }

      /* Form Styles */
      .form-label { font-size: 1.6rem; color: var(--light-color); margin-bottom: 1rem; display: block; }
      .form-control { width: 100%; border-radius: 0.5rem; padding: 1.4rem; font-size: 1.6rem; color: var(--black); background-color: var(--light-bg); margin-bottom: 2.5rem; border: 1px solid transparent; }
      .form-control:focus { border-color: #8e44ad; background-color: var(--white); }
      .time-select-group { display: flex; gap: 1rem; margin-bottom: 2.5rem; }
      .time-select { flex: 1; border-radius: 0.5rem; padding: 1.4rem; font-size: 1.6rem; color: var(--black); background-color: var(--light-bg); border: 1px solid transparent; cursor: pointer; }
      .time-select:focus { border-color: #8e44ad; background-color: var(--white); }
      .btn-purple { width: 100%; background-color: #8e44ad; color: white; padding: 1.4rem; border-radius: 0.5rem; font-size: 1.8rem; cursor: pointer; transition: 0.3s; margin-top: auto; }
      .btn-purple:hover { background-color: #732d91; }

      /* List Styles */
      .flex-header { display: flex; background-color: #8e44ad; padding: 1.5rem; border-radius: .5rem; margin-bottom: 1.5rem; }
      .flex-header div { flex: 1; color: #fff; font-size: 1.5rem; text-align: center; font-weight: bold; }
      .flex-row { display: flex; align-items: center; background-color: var(--light-bg); padding: 1.5rem; border-radius: .5rem; margin-bottom: 1.5rem; }
      .flex-row div { flex: 1; font-size: 1.5rem; color: var(--black); text-align: center; }
      
      /* Buttons */
      .action-group { display: flex; gap: 1rem; justify-content: center; align-items: center; }
      .btn-delete, .btn-join-admin { padding: 0.8rem 1.5rem; border-radius: 0.5rem; cursor: pointer; font-size: 1.4rem; border: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: background-color 0.3s; text-decoration: none; color: white; }
      .btn-delete { background-color: #e74c3c; }
      .btn-delete:hover { background-color: #c0392b; }
      .btn-join-admin { background-color: #27ae60; }
      .btn-join-admin:hover { background-color: #219150; }

      /* Empty State */
      .empty-state-container { text-align: center; padding: 4rem; flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; }
      .empty-icon-container { width: 120px; height: 120px; background: #f3e6ff; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-bottom: 2rem; flex-shrink: 0; }
      .empty-icon-container i { font-size: 5rem; color: #e9d5ff; }

      @media (max-width: 1100px) { .grid-container { grid-template-columns: 1fr; } }
   </style>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="admin-grid-section">

   <h1 class="main-heading">Manage Live Classes</h1>

   <div class="grid-container">

      <div class="card-box">
         <h2 class="card-heading">Create a New Class</h2>
         
         <div class="card-content-wrapper">
            <form action="" method="post" style="height: 100%; display: flex; flex-direction: column;">
               <label class="form-label"><i class="fas fa-chalkboard"></i> Class Title</label>
               <input type="text" name="title" placeholder="e.g., Math Chapter 1 Revision" required class="form-control">

               <label class="form-label"><i class="far fa-calendar-alt"></i> Class Date</label>
               <input type="date" name="date" required class="form-control" min="<?= date('Y-m-d'); ?>">

               <label class="form-label"><i class="far fa-clock"></i> Class Time</label>
               
               <div class="time-select-group">
                  <select name="hour" class="time-select" required>
                     <option value="" disabled selected>Hour</option>
                     <?php for($i=1; $i<=12; $i++): $val = sprintf("%02d", $i); ?>
                        <option value="<?= $val; ?>"><?= $val; ?></option>
                     <?php endfor; ?>
                  </select>
                  
                  <select name="minute" class="time-select" required>
                      <option value="" disabled selected>Min</option>
                      <?php for($i=0; $i<60; $i+=5): $val = sprintf("%02d", $i); ?>
                         <option value="<?= $val; ?>"><?= $val; ?></option>
                      <?php endfor; ?>
                  </select>

                  <select name="ampm" class="time-select" required>
                     <option value="AM">AM</option>
                     <option value="PM">PM</option>
                  </select>
               </div>

               <label class="form-label"><i class="fas fa-hourglass-half"></i> Duration (minutes)</label>
               <input type="number" name="duration" placeholder="e.g., 60" required class="form-control" min="1">

               <input type="submit" value="Create Class" name="submit" class="btn-purple">
            </form>
         </div>
      </div>

      <div class="card-box">
         <h2 class="card-heading">Your Scheduled Classes</h2>
         
         <div class="card-content-wrapper">
            <?php
               $select_classes = $conn->prepare("SELECT * FROM live_classes WHERE tutor_id = ? ORDER BY start_time ASC");
               $select_classes->execute([$tutor_id]);
               if($select_classes->rowCount() > 0){
            ?>
               <div class="flex-header">
                  <div style="flex:2; text-align:left;">Title</div> 
                  <div>Start</div>
                  <div>Dur.</div>
                  <div>Action</div>
               </div>

               <?php
                  while($fetch_class = $select_classes->fetch(PDO::FETCH_ASSOC)){
                     $start_formatted = date('d M, h:i A', strtotime($fetch_class['start_time']));
               ?>
               <div class="flex-row">
                  <div style="flex:2; text-align:left; font-weight:bold; font-size: 1.4rem;"><?= $fetch_class['title']; ?></div>
                  <div style="font-size: 1.4rem;"><?= $start_formatted; ?></div>
                  <div style="font-size: 1.4rem;"><?= $fetch_class['duration']; ?>m</div>
                  
                  <div class="action-group">
                     <a href="<?= $fetch_class['join_url']; ?>" target="_blank" class="btn-join-admin">
                        <i class="fas fa-video"></i> Join
                     </a>
                     <form action="" method="post">
                        <input type="hidden" name="class_id" value="<?= $fetch_class['id']; ?>">
                        <button type="submit" name="delete_class" class="btn-delete" onclick="return confirm('Delete this class schedule?');">
                           <i class="fas fa-trash"></i> Delete
                        </button>
                     </form>
                  </div>
               </div>
               <?php } ?>
            
            <?php } else { ?>
                <div class="empty-state-container">
                  <div class="empty-icon-container">
                     <i class="fa-solid fa-clipboard-list"></i>
                  </div>
                  <p style="font-size: 1.8rem; color: #777;">No live classes scheduled yet.</p>
               </div>
            <?php } ?>
         </div>
      </div>

   </div>

</section>

<?php include '../components/footer.php'; ?>

<script src="../js/admin_script.js"></script>

<script>
   document.addEventListener('DOMContentLoaded', () => {
      const messages = document.querySelectorAll('.message');
      
      if(messages.length > 0){
         // 1. Wait 3 seconds
         setTimeout(() => {
            messages.forEach(msg => {
               // 2. Add 'hiding' class to trigger CSS transition (opacity and move up)
               msg.classList.add('hiding');
               
               // 3. Wait 1.5s (matching CSS transition) then remove from DOM
               setTimeout(() => {
                  msg.remove();
               }, 1500);
            });
         }, 3000);
      }
      
      // Also clear the URL parameter so if they refresh manually, the message doesn't reappear
      if (window.history.replaceState) {
         const url = new URL(window.location);
         url.searchParams.delete('msg');
         window.history.replaceState(null, '', url.toString());
      }
   });
</script>

</body>
</html>