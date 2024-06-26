<!DOCTYPE html>
<html>
<head>
    <title>历史回复查询</title>
    <link rel="stylesheet" href="./hotpost.css">
</head>
<body>
    <h1>历史回复查询</h1>
    <form method="post">
        <label for="user_name">用户名：</label>
        <input type="text" name="user_name" id="user_name" required>
        <label for="sort_by">排序方式：</label>
        <select name="sort_by" id="sort_by">
            <option value="time">按时间顺序排序</option>
            <option value="like">按点赞数排序</option>
            <option value="reply">按回复数排序</option>
        </select>
        <input type="submit" value="查询">
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
    <div class="gradient-text">色相(Hue)</div>




    <form action="index.php" method="get">
        <input type="submit" value="返回到主页" class="return-btn">
    </form>
    <div style="text-align: center;">
    <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["user_name"];
    $sort_by = $_POST["sort_by"];

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

    // 准备查询语句
    $sql = "SELECT * FROM comment WHERE user_name = ?";
    switch ($sort_by) {
        case 'time':
            $sql .= " ORDER BY create_time DESC";
            break;
        case 'like':
            $sql .= " ORDER BY agree DESC";
            break;
        case 'reply':
            $sql .= " ORDER BY reply_num DESC";
            break;
        default:
            // 默认按时间顺序排序
            $sql .= " ORDER BY create_time DESC";
            break;
    }

    // 执行查询
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    // 显示结果
// 显示结果
if ($result->num_rows > 0) {
    echo "<h2>查询结果：</h2>";
    while ($row = $result->fetch_assoc()) {
        $sentiment = $row["sentiment"];
        // 根据情感分析结果动态计算颜色
        $color = "hsl(" . round($sentiment * 120) . ", 100%, 50%)"; // 使用HSL颜色模式，情感分析结果映射到色相值
        
        // 开始评论容器
        echo "<div class='comment'>";
        
        // 评论文本容器，只覆盖文本部分的背景色
        echo "<div class='comment-text'>";
        echo "<div style='background-color: $color; display: inline-block;'>";
        echo "<h3>{$row['text']}</h3>";
        echo "</div>";
        echo "</div>"; // 关闭评论文本容器

        // 其他信息，背景色为白色
        echo "<div class='comment-info'>";
        echo "<p>tid：{$row['tid']} pid：{$row['pid']} 用户：{$row['user_name']} (昵称：{$row['nick_name_new']} 楼层：{$row['floor']}) 回复数：{$row['reply_num']} 点赞数：{$row['agree']}  踩数：{$row['disagree']}</p>";
        $create_time_readable = date('Y-m-d H:i:s', $row['create_time']);
        echo "<p>创建时间：" . $create_time_readable . " </p>";
        echo "</div>";

        // 关闭评论容器
        echo "</div>";
        echo "<hr>";
    }
} else {
    echo "<p>未找到相关历史回复。</p>";
}



    // 关闭连接
    $stmt->close();
    $conn->close();
}
?>
</div>
</body>
</html>
