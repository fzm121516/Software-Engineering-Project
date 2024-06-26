<!DOCTYPE html>
<html>
<head>
    <title>贴吧热点帖子查询</title>
    <link rel="stylesheet" href="./hotpost.css">
</head>
<body>
    <h1>贴吧热点帖子查询</h1>
    <form method="post">
        <label for="count">查询数量：</label>
        <input type="number" name="count" id="count" required min="1">
        <label for="sort">排序方式：</label>
        <select name="sort" id="sort">
            <option value="reply">按回复数查询</option>
            <option value="like">按点赞数查询</option>
            <option value="last_reply">按最后回复时间查询</option>
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
    <div class="gradient-text">色调(Hue)</div>


    <form action="index.php" method="get">
        <input type="submit" value="返回到主页" class="return-btn">
    </form>
    
    <div style="text-align: center;">
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $count = intval($_POST["count"]);
        $sort = $_POST["sort"];

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

        // 查询数据库
        $sql = "SELECT * FROM hotpost ORDER BY ";

        switch ($sort) {
            case "reply":
                $sql .= "reply_num DESC";
                break;
            case "like":
                $sql .= "agree DESC";
                break;
            case "last_reply":
                $sql .= "last_time DESC";
                break;
            default:
                $sql .= "reply_num DESC";
                break;
        }

        $sql .= " LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $count);
        $stmt->execute();
        $result = $stmt->get_result();


        if ($result->num_rows > 0) {
            echo "<h2>查询结果：</h2>";
            $index = 0;
            while ($post = $result->fetch_assoc()) {
                $sentiment = $post["sentiment"];
                // 根据情感分析结果动态计算颜色
                $color = "hsl(" . round($sentiment * 120) . ", 100%, 50%)"; // 使用HSL颜色模式，情感分析结果映射到色相值


                echo "<form method='post' action='comment.php'>";
                echo "<input type='hidden' name='tid' style='background-color: $color;' value='{$post['tid']}'>";
                echo "<button type='submit' style='background-color: $color;' name='submit'>{$post['text']}</button>";
                echo "</form>";
               

                echo "<p>tid：{$post['tid']} 用户：{$post['user_name']} (昵称：{$post['nick_name_new']}，等级：{$post['user_level']} 查看数：{$post['view_num']} 回复数：{$post['reply_num']} 点赞数：{$post['agree']}  踩数：{$post['disagree']}</p>";

                $color2 = "hsl(" . round($post['sentiment_mean'] * 120) . ", 100%, 50%)";
                echo "<p style='background-color: {$color2}; display: inline-block;'>主题以及所有回复的情感分析置信度均值：{$post['sentiment_mean']}</p>";
                
                
                #echo "<p>创建时间：{$post['create_time']}，最后回复时间：{$post['last_time']}</p>";
                echo "<hr>";
                $index++;
            }
        } else {
            echo "<p>未找到相关历史回复。</p>";
        }
        $stmt->close();
        $conn->close();
    }
    ?>
    </div>
</body>
</html>