package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.NoticeDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.ResultMap;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

public interface NoticeDao {

    @Insert("INSERT INTO comm_notice(title, comm_news_id) VALUES(#{title}, #{newsId});")
    int insert(NoticeDO noticeDO);

    @Update("UPDATE comm_notice SET title = #{title}, comm_news_id = #{newsId} WHERE id = #{id};")
    int update(NoticeDO noticeDO);

    @ResultMap("noticeDOResultMap")
    @Select("SELECT id, title, comm_news_id FROM comm_notice WHERE id = #{id}")
    NoticeDO selectOne(long id);

    @ResultMap("noticeDOResultMap")
    @Select("SELECT id, title, comm_news_id FROM comm_notice ORDER BY id DESC LIMIT 1")
    NoticeDO selectLastOne();
}
