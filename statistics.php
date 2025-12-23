<?php
require_once 'db_config.php';

// Get overall statistics
$totalDrives = $pdo->query("SELECT COUNT(*) FROM driving_experience")->fetchColumn();
$totalKm = $pdo->query("SELECT COALESCE(SUM(Distance), 0) FROM driving_experience")->fetchColumn();
$avgDistance = $totalDrives > 0 ? $totalKm / $totalDrives : 0;

// Get total driving time
$totalMinutes = 0;
$times = $pdo->query("SELECT Duration FROM driving_experience")->fetchAll();
foreach ($times as $time) {
    if ($time['Duration']) {
        list($h, $m, $s) = explode(':', $time['Duration']);
        $totalMinutes += ($h * 60) + $m + ($s / 60);
    }
}
$totalHours = floor($totalMinutes / 60);
$remainingMinutes = round($totalMinutes % 60);

// FIXED: Distance for each individual drive (not grouped by date)
$distanceOverTime = $pdo->query("
    SELECT 
        Experience_ID,
        DATE_FORMAT(TimeDeparture, '%b %d, %H:%i') as drive_label,
        TimeDeparture,
        Distance
    FROM driving_experience
    ORDER BY TimeDeparture ASC
    LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC);

// Weather distribution
$weatherStats = $pdo->query("
    SELECT w.Weather_Condition, COUNT(*) as count, SUM(de.Distance) as total_km
    FROM driving_experience de
    JOIN weather w ON de.Weather_ID = w.Weather_ID
    GROUP BY w.Weather_Condition
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Road type distribution
$roadStats = $pdo->query("
    SELECT r.Road_Type, COUNT(*) as count, SUM(de.Distance) as total_km
    FROM driving_experience de
    JOIN road r ON de.Road_ID = r.Road_ID
    GROUP BY r.Road_Type
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Traffic distribution
$trafficStats = $pdo->query("
    SELECT t.Traffic_Level, COUNT(*) as count
    FROM driving_experience de
    JOIN traffic t ON de.Traffic_ID = t.Traffic_ID
    GROUP BY t.Traffic_Level
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Manoeuvres stats
$manoeuvreStats = $pdo->query("
    SELECT m.Manoeuvre_Type, COUNT(*) as count
    FROM driving_experience_manoeuvres dem
    JOIN manoeuvres m ON dem.Manoeuvre_ID = m.Manoeuvre_ID
    GROUP BY m.Manoeuvre_Type
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Average distance by road type
$avgByRoad = $pdo->query("
    SELECT r.Road_Type, AVG(de.Distance) as avg_distance
    FROM driving_experience de
    JOIN road r ON de.Road_ID = r.Road_ID
    GROUP BY r.Road_Type
    ORDER BY avg_distance DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Longest and shortest drives
$longestDrive = $pdo->query("
    SELECT Distance, DATE(TimeDeparture) as drive_date, Duration
    FROM driving_experience
    ORDER BY Distance DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$shortestDrive = $pdo->query("
    SELECT Distance, DATE(TimeDeparture) as drive_date, Duration
    FROM driving_experience
    ORDER BY Distance ASC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics & Analytics - Driving Experience</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .chart-container {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .chart-container h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.4em;
            text-align: center;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        
        .records-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
        }
        
        .records-section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.6em;
        }
        
        .records-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .record-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #667eea;
        }
        
        .record-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        
        .record-value {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .record-detail {
            color: #666;
            font-size: 0.95em;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 5em;
            margin-bottom: 20px;
        }

        .no-data-message {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .charts-grid {
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
            <h1>📊 Statistics & Analytics</h1>
            <p class="subtitle">Detailed insights into your driving progress</p>
        </header>
        
        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="index.php">➕ Log New Drive</a>
            <a href="summary.php">📋 View All Drives</a>
        </nav>
        
        <?php if ($totalDrives > 0): ?>
            <!-- Quick Stats -->
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
                    <div class="stat-label">Total Time</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-value"><?php echo number_format($avgDistance, 1); ?></div>
                    <div class="stat-label">Avg KM per Drive</div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="charts-grid">
                <!-- Weather Distribution -->
                <div class="chart-container">
                    <h2>🌤️ Weather Conditions</h2>
                    <div class="chart-wrapper">
                        <?php if (!empty($weatherStats)): ?>
                            <canvas id="weatherChart"></canvas>
                        <?php else: ?>
                            <div class="no-data-message">No weather data available</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Road Type Distribution -->
                <div class="chart-container">
                    <h2>🛣️ Road Types</h2>
                    <div class="chart-wrapper">
                        <?php if (!empty($roadStats)): ?>
                            <canvas id="roadChart"></canvas>
                        <?php else: ?>
                            <div class="no-data-message">No road type data available</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Traffic Distribution -->
                <div class="chart-container">
                    <h2>🚦 Traffic Levels</h2>
                    <div class="chart-wrapper">
                        <?php if (!empty($trafficStats)): ?>
                            <canvas id="trafficChart"></canvas>
                        <?php else: ?>
                            <div class="no-data-message">No traffic data available</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Manoeuvres -->
                <div class="chart-container">
                    <h2>🎯 Manoeuvres Practiced</h2>
                    <div class="chart-wrapper">
                        <?php if (!empty($manoeuvreStats)): ?>
                            <canvas id="manoeuvresChart"></canvas>
                        <?php else: ?>
                            <div class="no-data-message">No manoeuvre data available</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Distance Over Time - FIXED -->
                <div class="chart-container" style="grid-column: 1 / -1;">
                    <h2>📈 Distance Per Drive (Last 15 Drives)</h2>
                    <div class="chart-wrapper">
                        <?php if (!empty($distanceOverTime)): ?>
                            <canvas id="distanceChart"></canvas>
                        <?php else: ?>
                            <div class="no-data-message">No distance data available</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Average Distance by Road Type -->
                <div class="chart-container" style="grid-column: 1 / -1;">
                    <h2>📊 Average Distance by Road Type</h2>
                    <div class="chart-wrapper">
                        <?php if (!empty($avgByRoad)): ?>
                            <canvas id="avgRoadChart"></canvas>
                        <?php else: ?>
                            <div class="no-data-message">No average distance data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Records Section -->
            <div class="records-section">
                <h2>🏆 Your Records</h2>
                <div class="records-grid">
                    <?php if ($longestDrive): ?>
                        <div class="record-card">
                            <h3>🥇 Longest Drive</h3>
                            <div class="record-value"><?php echo number_format($longestDrive['Distance'], 1); ?> km</div>
                            <div class="record-detail">
                                Date: <?php echo date('M d, Y', strtotime($longestDrive['drive_date'])); ?><br>
                                Duration: <?php echo $longestDrive['Duration']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($shortestDrive): ?>
                        <div class="record-card">
                            <h3>🎯 Shortest Drive</h3>
                            <div class="record-value"><?php echo number_format($shortestDrive['Distance'], 1); ?> km</div>
                            <div class="record-detail">
                                Date: <?php echo date('M d, Y', strtotime($shortestDrive['drive_date'])); ?><br>
                                Duration: <?php echo $shortestDrive['Duration']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="record-card">
                        <h3>⏰ Total Driving Time</h3>
                        <div class="record-value"><?php echo $totalHours; ?>h <?php echo $remainingMinutes; ?>m</div>
                        <div class="record-detail">
                            Across <?php echo $totalDrives; ?> driving sessions
                        </div>
                    </div>
                    
                    <div class="record-card">
                        <h3>🎪 Manoeuvres Practiced</h3>
                        <div class="record-value"><?php echo count($manoeuvreStats); ?></div>
                        <div class="record-detail">
                            Different types of manoeuvres
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <h2>No Statistics Available Yet</h2>
                <p>Start logging your driving experiences to see detailed analytics!</p>
                <br>
                <a href="index.php" style="display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 10px; font-weight: 600;">➕ Log Your First Drive</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Chart colors
        const colors = [
            '#667eea', '#764ba2', '#f093fb', '#4facfe',
            '#43e97b', '#fa709a', '#fee140', '#30cfd0',
            '#a8edea', '#fed6e3'
        ];
        
        // Weather Chart
        <?php if (!empty($weatherStats)): ?>
        try {
            const weatherData = {
                labels: <?php echo json_encode(array_column($weatherStats, 'Weather_Condition')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map('intval', array_column($weatherStats, 'count'))); ?>,
                    backgroundColor: colors.slice(0, <?php echo count($weatherStats); ?>)
                }]
            };
            
            new Chart(document.getElementById('weatherChart'), {
                type: 'doughnut',
                data: weatherData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        } catch(e) {
            console.error('Weather chart error:', e);
        }
        <?php endif; ?>
        
        // Road Type Chart
        <?php if (!empty($roadStats)): ?>
        try {
            const roadData = {
                labels: <?php echo json_encode(array_column($roadStats, 'Road_Type')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map('intval', array_column($roadStats, 'count'))); ?>,
                    backgroundColor: colors.slice(0, <?php echo count($roadStats); ?>)
                }]
            };
            
            new Chart(document.getElementById('roadChart'), {
                type: 'pie',
                data: roadData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        } catch(e) {
            console.error('Road chart error:', e);
        }
        <?php endif; ?>
        
        // Traffic Chart
        <?php if (!empty($trafficStats)): ?>
        try {
            new Chart(document.getElementById('trafficChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($trafficStats, 'Traffic_Level')); ?>,
                    datasets: [{
                        label: 'Number of Drives',
                        data: <?php echo json_encode(array_map('intval', array_column($trafficStats, 'count'))); ?>,
                        backgroundColor: '#667eea'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        } catch(e) {
            console.error('Traffic chart error:', e);
        }
        <?php endif; ?>
        
        // Manoeuvres Chart
        <?php if (!empty($manoeuvreStats)): ?>
        try {
            new Chart(document.getElementById('manoeuvresChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($manoeuvreStats, 'Manoeuvre_Type')); ?>,
                    datasets: [{
                        label: 'Times Practiced',
                        data: <?php echo json_encode(array_map('intval', array_column($manoeuvreStats, 'count'))); ?>,
                        backgroundColor: '#764ba2'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });
        } catch(e) {
            console.error('Manoeuvres chart error:', e);
        }
        <?php endif; ?>
        
        // Distance Over Time Chart - FIXED TO SHOW INDIVIDUAL DRIVES
        <?php if (!empty($distanceOverTime)): ?>
        try {
            new Chart(document.getElementById('distanceChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($distanceOverTime, 'drive_label')); ?>,
                    datasets: [{
                        label: 'Distance (km)',
                        data: <?php echo json_encode(array_map('floatval', array_column($distanceOverTime, 'Distance'))); ?>,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Distance: ' + context.parsed.y.toFixed(1) + ' km';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Distance (km)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Drive Date & Time'
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error('Distance chart error:', e);
        }
        <?php endif; ?>
        
        // Average Distance by Road Type
        <?php if (!empty($avgByRoad)): ?>
        try {
            new Chart(document.getElementById('avgRoadChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($avgByRoad, 'Road_Type')); ?>,
                    datasets: [{
                        label: 'Average Distance (km)',
                        data: <?php echo json_encode(array_map(function($v) {
                            return round(floatval($v), 1);
                        }, array_column($avgByRoad, 'avg_distance'))); ?>,
                        backgroundColor: colors.slice(0, <?php echo count($avgByRoad); ?>)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        } catch(e) {
            console.error('Average road chart error:', e);
        }
        <?php endif; ?>
    </script>
</body>
</html>