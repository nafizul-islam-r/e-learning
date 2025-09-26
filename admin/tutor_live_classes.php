<?php
include '../components/connect.php';
session_start();

if(!isset($_COOKIE['tutor_id'])){
   header('location:login.php');
   exit();
}

$tutor_id = $_COOKIE['tutor_id'];

// Handle form submit
if(isset($_POST['create'])){
   $id = uniqid('lc_'); 
   $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
   $start_time = $_POST['start_time'];
   $duration = (int)$_POST['duration'];

   // generate Jitsi room + URL
   $room_name = uniqid('room_');
   $join_url = "https://meet.jit.si/".$room_name;

   $stmt = $conn->prepare("INSERT INTO live_classes (id, tutor_id, title, start_time, duration, room_name, join_url) VALUES (?,?,?,?,?,?,?)");
   $stmt->execute([$id, $tutor_id, $title, $start_time, $duration, $room_name, $join_url]);

   $success = "Live class created!";
}
?>

<!DOCTYPE html>
<html>
<head>
   <title>Live Classes</title>
</head>
<body>
   <h1>Live Classes</h1>

   <?php if(!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

   <!-- Create Form -->
   <form method="post">
      <label>Title:</label>
      <input type="text" name="title" required><br><br>

      <label>Start Time:</label>
      <input type="datetime-local" name="start_time" required><br><br>

      <label>Duration (minutes):</label>
      <input type="number" name="duration" min="5" max="240" required><br><br>

      <button type="submit" name="create">Create Class</button>
   </form>

   <hr>

   <!-- Show existing classes -->
   <h2>Your Classes</h2>
   <table border="1" cellpadding="8">
      <tr>
         <th>Title</th>
         <th>Start</th>
         <th>Duration</th>
         <th>Join Link</th>
      </tr>
      <?php
      $stmt = $conn->prepare("SELECT * FROM live_classes WHERE tutor_id = ? ORDER BY start_time DESC");
      $stmt->execute([$tutor_id]);
      while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
         echo "<tr>
               <td>{$row['title']}</td>
               <td>{$row['start_time']}</td>
               <td>{$row['duration']} min</td>
               <td><a href='{$row['join_url']}' target='_blank'>Join</a></td>
               </tr>";
      }
      ?>
   </table>
</body>
</html>
