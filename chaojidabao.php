<!DOCTYPE html>
<html>
<head>
    <title>超级大爆查询</title>
    <link rel="stylesheet" href="./hotpost.css">
</head>
<body>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="sort_by">排序方式：</label>
        <select name="sort_by" id="sort_by">
            <option value="time">按时间顺序排序</option>
            <option value="like">按点赞数排序</option>
            <option value="reply">按回复数排序</option>
        </select>
        <input type="submit" value="提交">
    </form>


    <div class="gradient-text">情感分析结果：置信度分数</div>
    <div class="gradient-label1">
        <div class="label-left">0 负面</div>
        <div class="label-right">正面 1</div>
    </div>
    <div class="gradient-bar"></div>
    <div class="gradient-label">
        <span>0°</span>
        <span>120°</span>
    </div>
    <div class="gradient-text">色调(Hue)</div>
    

    <form action="index.php" method="get">
        <input type="submit" value="返回到主页" class="return-btn">
    </form>
    
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
    $sql = "SELECT * FROM chaojidabao";
    $sql .= $sort_sql; // 添加排序条件
    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
        echo "<h2>烂梗条目：</h2>";
        $index = 0;
        while ($row = $result->fetch_assoc()) {
            $sentiment = $row["sentiment"];
            // 根据情感分析结果动态计算颜色
            $color = "hsl(" . round($sentiment * 120) . ", 100%, 50%)"; // 使用HSL颜色模式，情感分析结果映射到色相值
            echo "<div class='comment' style='background-color: $color;'>"; // 添加颜色到评论容器
            echo "<h2>{$row['text']}</h2>";
            echo "<p>创建时间：{$row['create_time']} 点赞数：{$row['agree']} 回复数：{$row['reply_num']}</p>";
            echo "<p>户：{$row['user_name']} (昵称：{$row['nick_name_new']}</p>";
            echo "<hr>";
            $index++;
        }
    } else {
        echo "<p>没有找到烂梗。</p>";
    }
    // 关闭连接
    $conn->close();
    ?>
</body>
</html>
