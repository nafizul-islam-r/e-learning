<?php
include 'components/connect.php';
session_start();

if (!isset($_COOKIE['user_id'])) {
    header('location:login.php');
    exit();
}

$user_id = $_COOKIE['user_id'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Live Classes</title>
</head>

<body>
    <h1>Upcoming Live Classes</h1>

    <?php
    date_default_timezone_set('Asia/Dhaka'); // adjust to your timezone
    
    $now = date('Y-m-d H:i:s');

    // Fetch upcoming or ongoing classes
    $stmt = $conn->prepare("SELECT lc.*, t.name AS tutor_name FROM live_classes lc 
                        JOIN tutors t ON t.id = lc.tutor_id
                        ORDER BY lc.start_time ASC");
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($classes) > 0) {
        echo "<table border='1' cellpadding='8'>
            <tr>
              <th>Title</th>
              <th>Tutor</th>
              <th>Start</th>
              <th>Duration (min)</th>
              <th>Action</th>
            </tr>";
        foreach ($classes as $class) {
            $class_start = $class['start_time'];
            $class_end = date('Y-m-d H:i:s', strtotime($class_start . ' +' . $class['duration'] . ' minutes'));

            // determine what to show
            if ($now < $class_start) {
                $action = "<button disabled>Starts at " . $class_start . "</button>";
            } elseif ($now >= $class_start && $now <= $class_end) {
                $action = "<a href='{$class['join_url']}' target='_blank'>Join</a>";
            } else {
                $action = "Class ended";
            }

            echo "<tr>
                <td>{$class['title']}</td>
                <td>{$class['tutor_name']}</td>
                <td>{$class_start}</td>
                <td>{$class['duration']}</td>
                <td>{$action}</td>
              </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No live classes scheduled.</p>";
    }
    ?>


</body>

</html>