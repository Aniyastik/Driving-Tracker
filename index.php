<?php include('connect.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log New Drive - Aniya's Driving Tracker</title>
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
      max-width: 600px;
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
      font-size: 2.2em;
      margin-bottom: 10px;
    }

    .subtitle {
      color: #666;
      font-size: 1em;
    }

    nav {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }

    nav a {
      padding: 10px 20px;
      background: #667eea;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      transition: all 0.3s;
      font-weight: 600;
      font-size: 0.9em;
    }

    nav a:hover {
      background: #5568d3;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102,126,234,0.4);
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
      font-size: 0.95em;
    }

    input[type="date"],
    input[type="time"],
    input[type="number"],
    select {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 1em;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s;
    }

    input:focus,
    select:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
    }

    .manoeuvres-section {
      margin-bottom: 25px;
    }

    .manoeuvres-section label {
      margin-bottom: 15px;
    }

    .checkbox-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 10px;
      margin-top: 10px;
    }

    .checkbox-item {
      background: #f8f9fa;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .checkbox-item:hover {
      background: #e8eaf6;
      border-color: #667eea;
    }

    .checkbox-item input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #667eea;
    }

    .checkbox-item input[type="checkbox"]:checked + span {
      font-weight: 600;
      color: #667eea;
    }

    .checkbox-item span {
      font-size: 0.9em;
      color: #333;
    }

    button[type="submit"] {
      width: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 15px;
      border-radius: 10px;
      font-size: 1.1em;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 10px;
    }

    button[type="submit"]:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(102,126,234,0.4);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    @media (max-width: 600px) {
      .container {
        padding: 25px;
      }

      h1 {
        font-size: 1.8em;
      }

      .form-row {
        grid-template-columns: 1fr;
      }

      .checkbox-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>🚗 Log New Drive</h1>
      <p class="subtitle">Record your driving experience</p>
    </header>

    <nav>
      <a href="dashboard.php">📊 Dashboard</a>
      <a href="summary.php">📋 All Drives</a>
      <a href="statistics.php">📈 Statistics</a>
    </nav>

    <form action="insert_experience.php" method="POST">
      <div class="form-group">
        <label>📅 Date:</label>
        <input type="date" name="date" required max="<?php echo date('Y-m-d'); ?>">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>🕐 Departure Time:</label>
          <input type="time" name="departure" required>
        </div>

        <div class="form-group">
          <label>🕐 Arrival Time:</label>
          <input type="time" name="arrival" required>
        </div>
      </div>

      <div class="form-group">
        <label>🛣️ Distance (km):</label>
        <input type="number" step="0.1" name="distance" required min="0.1" placeholder="e.g., 15.5">
      </div>

      <div class="form-group">
        <label>🛤️ Road Type:</label>
        <select name="Road_ID" required>
          <option value="">Select road type...</option>
          <?php
          $roads = mysqli_query($conn, "SELECT * FROM road ORDER BY Road_Type");
          while ($r = mysqli_fetch_assoc($roads)) {
              echo "<option value='{$r['Road_ID']}'>{$r['Road_Type']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>🌤️ Weather Condition:</label>
        <select name="Weather_ID" required>
          <option value="">Select weather...</option>
          <?php
          $weathers = mysqli_query($conn, "SELECT * FROM weather ORDER BY Weather_Condition");
          while ($w = mysqli_fetch_assoc($weathers)) {
              echo "<option value='{$w['Weather_ID']}'>{$w['Weather_Condition']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>🚦 Traffic Level:</label>
        <select name="Traffic_ID" required>
          <option value="">Select traffic level...</option>
          <?php
          $traffic = mysqli_query($conn, "SELECT * FROM traffic ORDER BY Traffic_Level");
          while ($t = mysqli_fetch_assoc($traffic)) {
              echo "<option value='{$t['Traffic_ID']}'>{$t['Traffic_Level']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="manoeuvres-section">
        <label>🎯 Manoeuvres Performed:</label>
        <div class="checkbox-grid">
          <?php
          $manoeuvres = mysqli_query($conn, "SELECT * FROM manoeuvres ORDER BY Manoeuvre_Type");
          while ($m = mysqli_fetch_assoc($manoeuvres)) {
              $id = "man_" . $m['Manoeuvre_ID'];
              echo "
              <label class='checkbox-item'>
                <input type='checkbox' name='manoeuvres[]' value='{$m['Manoeuvre_ID']}' id='$id'>
                <span>{$m['Manoeuvre_Type']}</span>
              </label>";
          }
          ?>
        </div>
      </div>

      <button type="submit">💾 Save Driving Experience</button>
    </form>
  </div>
</body>
</html>