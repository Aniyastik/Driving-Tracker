<?php
require_once 'db_config.php';

// Get overall statistics
$totalDrives = $pdo->query("SELECT COUNT(*) FROM driving_experience")->fetchColumn();
$totalKm = $pdo->query("SELECT SUM(Distance) FROM driving_experience")->fetchColumn();
$avgDistance = $totalDrives > 0 ? $totalKm / $totalDrives : 0;

// Get total driving time
$totalMinutes = 0;
$times = $pdo->query("SELECT Duration FROM driving_experience")->fetchAll();
foreach ($times as $time) {
    list($h, $m, $s) = explode(':', $time['Duration']);
    $totalMinutes += ($h * 60) + $m;
}
$totalHours = floor($totalMinutes / 60);
$remainingMinutes = $totalMinutes % 60;

// Recent drives
$recentDrives = $pdo->query("
    SELECT 
        de.Experience_ID,
        de.TimeDeparture,
        de.Distance,
        r.Road_Type,
        w.Weather_Condition
    FROM driving_experience de
    LEFT JOIN road r ON de.Road_ID = r.Road_ID
    LEFT JOIN weather w ON de.Weather_ID = w.Weather_ID
    ORDER BY de.TimeDeparture DESC
    LIMIT 5
")->fetchAll();

// Most practiced manoeuvres
$topManoeuvres = $pdo->query("
    SELECT m.Manoeuvre_Type, COUNT(*) as count
    FROM driving_experience_manoeuvres dem
    JOIN manoeuvres m ON dem.Manoeuvre_ID = m.Manoeuvre_ID
    GROUP BY m.Manoeuvre_Type
    ORDER BY count DESC
    LIMIT 5
")->fetchAll();

// Weather distribution
$weatherDist = $pdo->query("
    SELECT w.Weather_Condition, COUNT(*) as count
    FROM driving_experience de
    JOIN weather w ON de.Weather_ID = w.Weather_ID
    GROUP BY w.Weather_Condition
    ORDER BY count DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Driving Experience</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            margin-bottom: 40px;
        }
        
        h1 {
            color: #667eea;
            font-size: 2.8em;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.2em;
        }
        
        nav {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        
        nav a {
            padding: 14px 28px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 1.05em;
        }
        
        nav a:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(102,126,234,0.3);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3em;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2.8em;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 1.1em;
            opacity: 0.95;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        
        .section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.6em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .recent-drives-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .drive-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        
        .drive-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }
        
        .drive-date {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .drive-details {
            color: #666;
            font-size: 0.95em;
        }
        
        .drive-km {
            color: #667eea;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: white;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .list-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transform: translateX(3px);
        }
        
        .list-label {
            font-weight: 500;
            color: #333;
        }
        
        .list-value {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 4em;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 500px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚗 Aniya's Driving Dashboard</h1>
            <p class="subtitle">Welcome back! Here's your driving progress</p>
        </header>
        
        <nav>
            <a href="index.php">➕ Log New Drive</a>
            <a href="summary.php">📋 View All Drives</a>
            <a href="statistics.php">📈 Statistics & Charts</a>
        </nav>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-value"><?php echo $totalDrives; ?></div>
                <div class="stat-label">Total Drives</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🛣️</div>
                <div class="stat-value"><?php echo number_format($totalKm, 1); ?></div>
                <div class="stat-label">Total Kilometers</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏱️</div>
                <div class="stat-value"><?php echo $totalHours; ?>h <?php echo $remainingMinutes; ?>m</div>
                <div class="stat-label">Total Driving Time</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?php echo number_format($avgDistance, 1); ?></div>
                <div class="stat-label">Avg KM per Drive</div>
            </div>
        </div>
        
        <div class="content-grid">
            <!-- Recent Drives -->
            <div class="section">
                <h2>🕐 Recent Drives</h2>
                <?php if (empty($recentDrives)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🚗</div>
                        <p>No drives logged yet.<br>Start tracking your progress!</p>
                    </div>
                <?php else: ?>
                    <div class="recent-drives-list">
                        <?php foreach ($recentDrives as $drive): ?>
                            <div class="drive-item">
                                <div class="drive-date">
                                    <?php echo date('F j, Y - g:i A', strtotime($drive['TimeDeparture'])); ?>
                                </div>
                                <div class="drive-details">
                                    <?php echo htmlspecialchars($drive['Road_Type']); ?> • 
                                    <?php echo htmlspecialchars($drive['Weather_Condition']); ?> • 
                                    <span class="drive-km"><?php echo number_format($drive['Distance'], 1); ?> km</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Top Manoeuvres -->
            <div class="section">
                <h2>🎯 Most Practiced Manoeuvres</h2>
                <?php if (empty($topManoeuvres)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🎪</div>
                        <p>No manoeuvres recorded yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($topManoeuvres as $man): ?>
                        <div class="list-item">
                            <span class="list-label"><?php echo htmlspecialchars($man['Manoeuvre_Type']); ?></span>
                            <span class="list-value"><?php echo $man['count']; ?>x</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Weather Distribution -->
            <div class="section">
                <h2>🌤️ Weather Conditions</h2>
                <?php if (empty($weatherDist)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">☀️</div>
                        <p>No weather data available.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($weatherDist as $weather): ?>
                        <div class="list-item">
                            <span class="list-label"><?php echo htmlspecialchars($weather['Weather_Condition']); ?></span>
                            <span class="list-value"><?php echo $weather['count']; ?> drives</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>