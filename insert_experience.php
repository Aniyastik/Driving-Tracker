<?php
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $date = $_POST['date'];
    $departure = $_POST['departure'];
    $arrival = $_POST['arrival'];
    $distance = $_POST['distance'];
    $weather_id = $_POST['Weather_ID'];
    $road_id = $_POST['Road_ID'];
    $traffic_id = $_POST['Traffic_ID'];

    // Combine date with times
    $departure_dt = "$date $departure:00";
    $arrival_dt   = "$date $arrival:00";

    // Validate that arrival is after departure
    $dep = new DateTime($departure_dt);
    $arr = new DateTime($arrival_dt);
    
    if ($arr <= $dep) {
        die("<div style='text-align:center; padding:40px; font-family:Poppins;'>
        <h2 style='color:#dc3545;'>❌ Error</h2>
        <p>Arrival time must be after departure time!</p>
        <a href='index.php' style='color:#667eea; text-decoration:none; font-weight:600;'>← Go Back</a>
        </div>");
    }

    $stmt = $conn->prepare("INSERT INTO driving_experience 
        (TimeDeparture, TimeArrival, Duration, Distance, Road_ID, Weather_ID, Traffic_ID)
        VALUES (?, ?, TIMEDIFF(?, ?), ?, ?, ?, ?)");

    $stmt->bind_param("ssssdiii", 
        $departure_dt, 
        $arrival_dt, 
        $arrival_dt, 
        $departure_dt, 
        $distance, 
        $road_id, 
        $weather_id, 
        $traffic_id
    );

    if ($stmt->execute()) {
        $experience_id = $conn->insert_id;

        // Insert manoeuvres
        if (!empty($_POST['manoeuvres'])) {
            $stmt2 = $conn->prepare(
                "INSERT INTO driving_experience_manoeuvres (Experience_ID, Manoeuvre_ID) VALUES (?, ?)"
            );
            foreach ($_POST['manoeuvres'] as $man_id) {
                $stmt2->bind_param("ii", $experience_id, $man_id);
                $stmt2->execute();
            }
            $stmt2->close();
        }
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Success - Drive Logged!</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .success-card {
      background: white;
      border-radius: 20px;
      padding: 50px 60px;
      text-align: center;
      max-width: 500px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      animation: slideUp 0.5s ease;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .success-icon {
      font-size: 5em;
      margin-bottom: 20px;
      animation: bounce 1s ease;
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }

    h1 {
      color: #667eea;
      font-size: 2em;
      margin-bottom: 15px;
    }

    p {
      color: #666;
      font-size: 1.1em;
      margin-bottom: 35px;
      line-height: 1.6;
    }

    .button-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn {
      padding: 14px 28px;
      background: #667eea;
      color: white;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s;
      display: inline-block;
      border: none;
      cursor: pointer;
      font-size: 1em;
    }

    .btn:hover {
      background: #5568d3;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102,126,234,0.4);
    }

    .btn-secondary {
      background: #764ba2;
    }

    .btn-secondary:hover {
      background: #643d8a;
    }

    @media (max-width: 600px) {
      .success-card {
        padding: 40px 30px;
      }

      h1 {
        font-size: 1.6em;
      }

      .button-group {
        flex-direction: column;
      }

      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class='success-card'>
    <div class='success-icon'>✅</div>
    <h1>Drive Logged Successfully!</h1>
    <p>Your driving experience has been saved.<br>Great job tracking your progress!</p>
    <div class='button-group'>
      <a href='index.php' class='btn'>➕ Log Another Drive</a>
      <a href='dashboard.php' class='btn btn-secondary'>📊 View Dashboard</a>
      <a href='summary.php' class='btn'>📋 All Drives</a>
    </div>
  </div>
</body>
</html>
<?php
    } else {
        echo "<div style='text-align:center; padding:40px; font-family:Poppins;'>
        <h2 style='color:#dc3545;'>❌ Error</h2>
        <p>Failed to save your driving experience: {$stmt->error}</p>
        <a href='index.php' style='color:#667eea; text-decoration:none; font-weight:600;'>← Go Back</a>
        </div>";
    }

    $stmt->close();
}
$conn->close();
?>