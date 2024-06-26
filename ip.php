<!DOCTYPE html>
<html>
<head>
    <title>回贴中IP地区用户数和回帖频次统计</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./hotpost.css">
    <!-- 引入 Chart.js 库 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>回贴中IP地区用户数和回帖频次统计</h1>
    <form method="post">
        <input type="submit" value="统计">
    </form>
    <form action="index.php" method="get">
        <input type="submit" value="返回到主页" class="return-btn">
    </form>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 连接数据库
        $servername = "localhost";
        $username = "root";
        $password = "123456";
        $dbname = "se";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // 检查连接
        if ($conn->connect_error) {
            die("连接失败: " . $conn->connect_error);
        }

        
        
        // 准备查询语句，排除空 ip，按用户数降序排列
$sql_unique_users = "SELECT ip, COUNT(DISTINCT user_name) as unique_users, COUNT(*) as post_frequency FROM comment WHERE ip <> '' GROUP BY ip ORDER BY unique_users DESC";

// 执行唯一用户数和发帖频次查询
$result_unique_users = $conn->query($sql_unique_users);

// 处理唯一用户数和发帖频次结果
$unique_users_data = [];
$post_frequency_data = [];
$total_unique_users = 0;
$total_post_frequency = 0;

while ($row = $result_unique_users->fetch_assoc()) {
    $unique_users_data[$row['ip']] = $row['unique_users'];
    $post_frequency_data[$row['ip']] = $row['post_frequency'];
    $total_unique_users += $row['unique_users'];
    $total_post_frequency += $row['post_frequency'];
}

// 取前五名和其他
$labels_unique_users = [];
$data_unique_users = [];
$data_post_frequency = [];
$data_unique_users_percentage = [];
$data_post_frequency_percentage = [];
$other_unique_users = 0;
$other_post_frequency = 0;
$count = 0;

foreach ($unique_users_data as $ip => $unique_users) {
    if ($count < 5) {
        $labels_unique_users[] = $ip;
        $data_unique_users[] = $unique_users;
        $data_unique_users_percentage[] = round(($unique_users / $total_unique_users) * 100, 2);
        $data_post_frequency[] = $post_frequency_data[$ip];
        $data_post_frequency_percentage[] = round(($post_frequency_data[$ip] / $total_post_frequency) * 100, 2);
    } else {
        $other_unique_users += $unique_users;
        $other_post_frequency += $post_frequency_data[$ip];
    }
    $count++;
}

if ($other_unique_users > 0) {
    $labels_unique_users[] = '其他';
    $data_unique_users[] = $other_unique_users;
    $data_unique_users_percentage[] = round(($other_unique_users / $total_unique_users) * 100, 2);
    $data_post_frequency[] = $other_post_frequency;
    $data_post_frequency_percentage[] = round(($other_post_frequency / $total_post_frequency) * 100, 2);
}

// 输出表格
echo '<div style="width: 800px; margin: 20px auto;">';
echo '<h2>IP 地址唯一用户数和发帖频次表格</h2>';
echo '<table border="1">';
echo '<thead>';
echo '<tr><th>IP</th><th>用户数</th><th>用户数百分比 (%)</th><th>发帖频次</th><th>发帖频次百分比 (%)</th></tr>';
echo '</thead>';
echo '<tbody>';

foreach ($unique_users_data as $ip => $unique_users) {
    $unique_users_percentage = round(($unique_users / $total_unique_users) * 100, 2);
    $post_frequency_percentage = round(($post_frequency_data[$ip] / $total_post_frequency) * 100, 2);
    
    echo "<tr>";
    echo "<td>{$ip}</td>";
    echo "<td>{$unique_users}</td>";
    echo "<td>{$unique_users_percentage}</td>";
    echo "<td>{$post_frequency_data[$ip]}</td>";
    echo "<td>{$post_frequency_percentage}</td>";
    echo "</tr>";
}

echo '</tbody>';
echo '</table>';
echo '</div>';


echo '<div style="width: 800px; margin: 20px auto; text-align: center;">';
echo '<p>IP地址归属地唯一用户数分布：</p>';
echo '</div>';

        // 输出饼状图（唯一用户数）
        echo '<div style="width: 400px; margin: 20px auto;">';
        echo '<canvas id="pieChartUniqueUsers"></canvas>';
        echo '</div>';

        // 生成 JavaScript 脚本绘制饼状图（唯一用户数）
        echo '<script>';
        echo 'var ctxUniqueUsers = document.getElementById("pieChartUniqueUsers").getContext("2d");';
        echo 'var myPieChartUniqueUsers = new Chart(ctxUniqueUsers, {';
        echo '    type: "pie",';
        echo '    data: {';
        echo '        labels: ' . json_encode($labels_unique_users) . ',';
        echo '        datasets: [{';
        echo '            label: "唯一用户数",';
        echo '            data: ' . json_encode($data_unique_users) . ',';
        echo '            backgroundColor: [';
        echo '                "#FF6384",';
        echo '                "#36A2EB",';
        echo '                "#FFCE56",';
        echo '                "#4BC0C0",';
        echo '                "#9966FF",';
        echo '                "#FF9F40"';
        echo '            ],';
        echo '            borderColor: [';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF"';
        echo '            ],';
        echo '            borderWidth: 1';
        echo '        }]';
        echo '    },';
        echo '    options: {';
        echo '        responsive: true,';
        echo '        plugins: {';
        echo '            legend: {';
        echo '                position: "top",';
        echo '            },';
        echo '            tooltip: {';
        echo '                callbacks: {';
        echo '                    label: function(tooltipItem) {';
        echo '                        return tooltipItem.label + ": " + tooltipItem.raw.toFixed(2) + "%";';
        echo '                    }';
        echo '                }';
        echo '            }';
        echo '        }';
        echo '    }';
        echo '});';
        echo '</script>';

        echo '<div style="width: 800px; margin: 20px auto; text-align: center;">';
        echo '<p>IP地址归属地发帖频次分布：</p>';
        echo '</div>';
        
        // 输出饼状图（发帖频次）
        echo '<div style="width: 400px; margin: 20px auto;">';
        echo '<canvas id="pieChartPostFrequency"></canvas>';
        echo '</div>';

        // 生成 JavaScript 脚本绘制饼状图（发帖频次）
        echo '<script>';
        echo 'var ctxPostFrequency = document.getElementById("pieChartPostFrequency").getContext("2d");';
        echo 'var myPieChartPostFrequency = new Chart(ctxPostFrequency, {';
        echo '    type: "pie",';
        echo '    data: {';
        echo '        labels: ' . json_encode($labels_unique_users) . ',';
        echo '        datasets: [{';
        echo '            label: "发帖频次",';
        echo '            data: ' . json_encode($data_post_frequency) . ',';
        echo '            backgroundColor: [';
        echo '                "#FF6384",';
        echo '                "#36A2EB",';
        echo '                "#FFCE56",';
        echo '                "#4BC0C0",';
        echo '                "#9966FF",';
        echo '                "#FF9F40"';
        echo '            ],';
        echo '            borderColor: [';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF",';
        echo '                "#FFFFFF"';
        echo '            ],';
        echo '            borderWidth: 1';
        echo '        }]';
        echo '    },';
        echo '    options: {';
        echo '        responsive: true,';
        echo '        plugins: {';
        echo '            legend: {';
        echo '                position: "top",';
        echo '            },';
        echo '            tooltip: {';
        echo '                callbacks: {';
        echo '                    label: function(tooltipItem) {';
        echo '                        return tooltipItem.label + ": " + tooltipItem.raw.toFixed(2) + "%";';
        echo '                    }';
        echo '                }';
        echo '            }';
        echo '        }';
        echo '    }';
        echo '});';
        echo '</script>';

        // 关闭连接
        $conn->close();
    }
    ?>

</body>
</html>
