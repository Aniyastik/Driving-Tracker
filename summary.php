<?php include("connect.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Drives - Aniya's Driving Tracker</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
      padding: 20px;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    header {
      text-align: center;
      margin-bottom: 30px;
    }

    h1 {
      color: #667eea;
      font-size: 2.5em;
      margin-bottom: 10px;
    }

    .subtitle {
      color: #666;
      font-size: 1.1em;
    }

    nav {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }

    nav a {
      padding: 12px 24px;
      background: #667eea;
      color: white;
      text-decoration: none;
      border-radius: 10px;
      transition: all 0.3s;
      font-weight: 600;
    }

    nav a:hover {
      background: #5568d3;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102,126,234,0.4);
    }

    .alert {
      padding: 15px 20px;
      margin-bottom: 25px;
      border-radius: 10px;
      font-weight: 500;
      animation: slideDown 0.5s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 2px solid #c3e6cb;
    }

    .alert-info {
      background: #d1ecf1;
      color: #0c5460;
      border: 2px solid #bee5eb;
    }

    .total-banner {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 25px;
      border-radius: 15px;
      text-align: center;
      margin-bottom: 30px;
      box-shadow: 0 8px 20px rgba(102,126,234,0.3);
    }

    .total-banner .value {
      font-size: 2.5em;
      font-weight: 700;
      margin: 10px 0;
    }

    .total-banner .label {
      font-size: 1.1em;
      opacity: 0.95;
    }

    .table-container {
      overflow-x: auto;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }

    thead {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    th {
      padding: 15px;
      text-align: left;
      font-weight: 600;
      white-space: nowrap;
    }

    td {
      padding: 15px;
      border-bottom: 1px solid #e0e0e0;
    }

    tbody tr {
      transition: all 0.3s;
    }

    tbody tr:hover {
      background: #f8f9fa;
      transform: scale(1.01);
    }

    .delete-btn {
      background: #dc3545;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s;
      font-size: 0.9em;
    }

    .delete-btn:hover {
      background: #c82333;
      transform: scale(1.05);
    }

    .badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85em;
      font-weight: 600;
    }

    .badge-distance {
      background: #e8eaf6;
      color: #667eea;
    }

    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #999;
    }

    .empty-icon {
      font-size: 6em;
      margin-bottom: 20px;
    }

    .empty-state h2 {
      color: #666;
      margin-bottom: 15px;
    }

    .empty-state a {
      display: inline-block;
      margin-top: 20px;
      padding: 14px 28px;
      background: #667eea;
      color: white;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s;
    }

    .empty-state a:hover {
      background: #5568d3;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .container {
        padding: 25px;
      }

      h1 {
        font-size: 2em;
      }

      table {
        font-size: 0.9em;
      }

      th, td {
        padding: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>📋 All Driving Experiences</h1>
      <p class="subtitle">Complete history of your drives</p>
    </header>

    <nav>
      <a href="dashboard.php">🏠 Dashboard</a>
      <a href="index.php">➕ Log New Drive</a>
      <a href="statistics.php">📈 Statistics</a>
    </nav>

    <?php
    if (isset($_GET['success'])) {
      echo '<div class="alert alert-success">✅ Driving experience saved successfully!</div>';
    }
    if (isset($_GET['deleted'])) {
      echo '<div class="alert alert-info">🗑️ Experience deleted successfully!</div>';
    }

    $sql = "
      SELECT 
        de.Experience_ID,
        de.TimeDeparture,
        de.TimeArrival,
        de.Duration,
        r.Road_Type,
        w.Weather_Condition,
        t.Traffic_Level,
        de.Distance,
        GROUP_CONCAT(m.Manoeuvre_Type SEPARATOR ', ') AS Manoeuvres
      FROM driving_experience de
      LEFT JOIN road r ON de.Road_ID = r.Road_ID
      LEFT JOIN weather w ON de.Weather_ID = w.Weather_ID
      LEFT JOIN traffic t ON de.Traffic_ID = t.Traffic_ID
      LEFT JOIN driving_experience_manoeuvres dem ON de.Experience_ID = dem.Experience_ID
      LEFT JOIN manoeuvres m ON dem.Manoeuvre_ID = m.Manoeuvre_ID
      GROUP BY de.Experience_ID
      ORDER BY de.TimeDeparture DESC
    ";

    $result = $conn->query($sql);
    $totalKm = 0;
    $driveCount = 0;

    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $totalKm += $row['Distance'];
        $driveCount++;
      }

      echo "<div class='total-banner'>
              <div class='label'>Total Distance Driven</div>
              <div class='value'>" . number_format($totalKm, 1) . " km</div>
              <div class='label'>Across $driveCount drive" . ($driveCount > 1 ? 's' : '') . "</div>
            </div>";

      echo "<div class='table-container'><table>
              <thead>
                <tr>
                  <th>📅 Date & Time</th>
                  <th>🛤️ Road</th>
                  <th>🌤️ Weather</th>
                  <th>🚦 Traffic</th>
                  <th>🛣️ Distance</th>
                  <th>⏱️ Duration</th>
                  <th>🎯 Manoeuvres</th>
                  <th>⚙️ Action</th>
                </tr>
              </thead>
              <tbody>";

      $result->data_seek(0);
      while ($row = $result->fetch_assoc()) {
        $dateTime = date('M d, Y - H:i', strtotime($row['TimeDeparture']));
        $manoeuvres = $row['Manoeuvres'] ? htmlspecialchars($row['Manoeuvres']) : "<em style='color:#999;'>None</em>";
        
        echo "<tr>
                <td><strong>{$dateTime}</strong></td>
                <td>{$row['Road_Type']}</td>
                <td>{$row['Weather_Condition']}</td>
                <td>{$row['Traffic_Level']}</td>
                <td><span class='badge badge-distance'>{$row['Distance']} km</span></td>
                <td>{$row['Duration']}</td>
                <td>{$manoeuvres}</td>
                <td>
                  <form method='POST' action='delete_experience.php' style='display:inline;' 
                        onsubmit='return confirm(\"Are you sure you want to delete this drive?\");'>
                    <input type='hidden' name='experience_id' value='{$row['Experience_ID']}'>
                    <button type='submit' class='delete-btn'>🗑️ Delete</button>
                  </form>
                </td>
              </tr>";
      }

      echo "</tbody></table></div>";
    } else {
      echo "<div class='empty-state'>
              <div class='empty-icon'>🚗</div>
              <h2>No Drives Recorded Yet</h2>
              <p>Start tracking your driving experiences to see them here!</p>
              <a href='index.php'>➕ Log Your First Drive</a>
            </div>";
    }

    $conn->close();
    ?>
  </div>
</body>
</html>