import asyncio
import pymysql
from cemotion import Cemotion
import aiotieba
from aiotieba import ThreadSortType, PostSortType

async def main():
    db = pymysql.connect(
        host='localhost',  # 主机名（或IP地址）
        port=3306,  # 端口号，默认为3306
        user='root',  # 用户名
        password='123456',  # 密码
        charset='utf8mb4'  # 设置字符编码
    )

    cursor = db.cursor()
    db.select_db("se")

    # 创建hotpost表
    cursor.execute("DROP TABLE IF EXISTS hotpost")
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS hotpost (
            tid BIGINT PRIMARY KEY,
            user_id BIGINT,
            user_name VARCHAR(255),
            nick_name_new VARCHAR(255),
            user_level INT,
            create_time BIGINT,
            last_time BIGINT,
            text TEXT,
            title TEXT,
            view_num INT,
            reply_num INT,
            agree INT,
            disagree INT,
            sentiment FLOAT,
            sentiment_mean FLOAT
        )
    """)

    # 创建comment表
    cursor.execute("DROP TABLE IF EXISTS comment")
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS comment (
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
            sentiment FLOAT
        )
    """)

    async with aiotieba.Client() as client:
        threads = await client.get_threads("哈尔滨工业大学", pn=1, rn=100, sort=ThreadSortType.REPLY)

        c = Cemotion()  # Create Cemotion instance for sentiment analysis

        for thread in threads[0:5]:
            postsentiment = c.predict(thread.text)
            cursor.execute("""
                INSERT INTO hotpost (tid, user_id, user_name, nick_name_new, user_level, create_time, last_time, text, title, view_num, reply_num, agree, disagree,sentiment)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (thread.tid, thread.user.user_id, thread.user.user_name, thread.user.nick_name_new, thread.user.level, thread.create_time, thread.last_time, thread.text, thread.title, thread.view_num, thread.reply_num, thread.agree, thread.disagree,postsentiment))
            db.commit()

            posts = await client.get_posts(thread.tid, pn=1, rn=100,
                                           sort=PostSortType.ASC,
                                           only_thread_author=False,
                                           with_comments=False, comment_sort_by_agree=True, comment_rn=50)

            for post in posts:
                if post.text.strip():  # Check if text is not empty
                    sentiment = c.predict(post.text)  # Predict sentiment using Cemotion
                    cursor.execute("""
                        INSERT INTO comment (tid, pid, text, user_id, user_name, nick_name_new, floor, reply_num, agree, disagree, create_time, sentiment)
                        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                    """, (post.tid, post.pid, post.text, post.user.user_id, post.user.user_name, post.user.nick_name_new, post.floor, post.reply_num, post.agree, post.disagree, post.create_time, sentiment))
                    db.commit()

            # Update hotpost sentiment with the average sentiment of its comments
            cursor.execute("""
                UPDATE hotpost SET sentiment_mean = (SELECT AVG(sentiment) FROM comment WHERE tid = %s) WHERE tid = %s
            """, (thread.tid, thread.tid))
            db.commit()

    cursor.close()
    db.close()

loop = asyncio.get_event_loop()
loop.run_until_complete(main())
