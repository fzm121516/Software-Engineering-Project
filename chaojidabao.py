import pymysql

# 连接到MySQL数据库
db = pymysql.connect(
    host='localhost',  # 主机名（或IP地址）
    port=3306,         # 端口号，默认为3306
    user='root',       # 用户名
    password='123456', # 密码
    database='se',     # 数据库名
    charset='utf8mb4'  # 设置字符编码
)

cursor = db.cursor()

cursor.execute("DROP TABLE IF EXISTS chaojidabao")
cursor.execute("""
    CREATE TABLE IF NOT EXISTS chaojidabao (
        tid BIGINT,
        pid BIGINT,
        text TEXT,
        user_id BIGINT,
        user_name VARCHAR(255),
        nick_name_new VARCHAR(255),
        floor INT,
        reply_num INT,
        agree INT,
        disagree INT,
        create_time BIGINT,
        ip TEXT,
        sentiment FLOAT
    )
""")
# 查询comment表中text重复次数大于三的评论
cursor.execute("""
    SELECT text, COUNT(*) AS cnt
    FROM comment
    GROUP BY text
    HAVING cnt > 2
""")


# 将查询结果插入到chaotically表中
for row in cursor.fetchall():
    text = row[0]
    print(text)
    cursor.execute("INSERT INTO chaojidabao SELECT * FROM comment WHERE text = %s", (text,))

# 提交更改并关闭连接
db.commit()
cursor.close()
db.close()
