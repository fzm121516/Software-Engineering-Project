<!DOCTYPE html>
<html>
<head>
    <title>热点回复查询</title>
    <link rel="stylesheet" href="./hotpost.css">
</head>
<body>
    <h1>回贴查询</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="sort_by">排序方式：</label>
        <select name="sort_by" id="sort_by">
            <option value="floor">按楼层排序</option>
            <option value="reply">按回复数排序</option>
            <option value="like">按点赞数排序</option>
        </select>
        <input type="hidden" name="tid" value="<?php echo isset($_POST['tid']) ? $_POST['tid'] : ''; ?>">
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
    <div class="gradient-text">色相(Hue)</div>


    <form action="hotpost.php" method="get">
        <input type="submit" value="返回到上一级" class="return-btn">
    </form>
    <form action="index.php" method="get">
        <input type="submit" value="返回到主页" class="return-btn">
    </form>
    <div style="text-align: center;">
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $tid = $_POST["tid"];

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
        $sort_by = $_POST["sort_by"] ?? "";
        $sort_sql = "";
        if ($sort_by === "reply") {
            $sort_sql = " ORDER BY reply_num DESC";
        } elseif ($sort_by === "like") {
            $sort_sql = " ORDER BY agree DESC";
        } elseif ($sort_by === "floor") {
            $sort_sql = " ORDER BY floor ASC";
        }

        // 查询数据库
        $sql = "SELECT * FROM comment WHERE tid = ?";
        $sql .= $sort_sql; // Adding sorting condition
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tid);
        $stmt->execute();
        $result = $stmt->get_result();
        
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
        echo "<p>未找到回帖。</p>";
    }
    
    

        $stmt->close();
        $conn->close();
    }
    ?>
    </div>
</body>
</html>
