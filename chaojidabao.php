<!DOCTYPE html>
<html>
<head>
    <title>超级大爆查询</title>
    <link rel="stylesheet" href="./hotpost.css">
</head>
<body>
    <form action="<?php ?>" method="post">
        <input type="submit" value="查询">
    </form>


<!--     <div class="gradient-text">情感分析结果：置信度分数</div>
    <div class="gradient-label1">
        <div class="label-left">0 负面</div>
        <div class="label-right">正面 1</div>
    </div>
    <div class="gradient-bar"></div>
    <div class="gradient-label">
        <span>0°</span>
        <span>120°</span>
    </div>
    <div class="gradient-text">色相(Hue)</div> -->
    

    <form action="index.php" method="get">
        <input type="submit" value="返回到主页" class="return-btn">
    </form>
    <div style="text-align: center;">
    <?php
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

    // 获取排序选项
    $sort_by = isset($_POST["sort_by"]) ? $_POST["sort_by"] : "time";
    $sort_sql = "";
    switch ($sort_by) {
        case 'like':
            $sort_sql = " ORDER BY agree DESC";
            break;
        case 'reply':
            $sort_sql = " ORDER BY reply_num DESC";
            break;
        case 'time':
        default:
            $sort_sql = " ORDER BY create_time DESC";
            break;
    }


// 查询数据库
$sql = "SELECT text, MAX(sentiment) AS sentiment, COUNT(*) AS repeat_count FROM chaojidabao GROUP BY `text`";
$sql .= " ORDER BY repeat_count DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>烂梗条目：</h2>";
    while ($row = $result->fetch_assoc()) {
        $sentiment = $row["sentiment"];
        // 根据情感分析结果动态计算颜色
        $color = "hsl(" . round($sentiment * 120) . ", 100%, 50%)"; // 使用HSL颜色模式，情感分析结果映射到色相值 */

        // 开始评论容器
        echo "<div class='comment'>";
        
        // 评论文本容器，只覆盖文本部分的背景色
        echo "<div class='comment-text'>";
/*         echo "<div style='background-color: $color; display: inline-block;'>"; */
        echo "<div  display: inline-block;'>";
        echo "<h3>{$row['text']}</h3>";
        echo "</div>";
        echo "</div>"; // 关闭评论文本容器
        
        // 其他信息，背景色为白色
        echo "<div class='comment-info'>";
        echo "<p>重复次数：{$row['repeat_count']}</p>";

        echo "</div>";
    
        // 关闭评论容器
        echo "</div>";
        echo "<hr>";
    }
} else {
    echo "<p>没有找到烂梗。</p>";
}
    // 关闭连接
    $conn->close();
    ?>
    </div>
</body>
</html>
