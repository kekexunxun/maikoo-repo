package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.NewsDO;
import com.maikoo.superminercions.model.query.NewsQuery;
import org.apache.ibatis.annotations.*;

import java.util.List;

@Mapper
public interface NewsDao {
    @ResultMap("newsDOResultMap")
    @Select("SELECT " +
                "id, " +
                "title, " +
                "content, " +
                "image_uri, " +
                "created_at " +
            "FROM " +
                "comm_news " +
            "WHERE " +
                "id = #{id}")
    NewsDO selectOne(long id);

    @ResultMap("newsDOResultMap")
    @Select("SELECT " +
                "id, " +
                "title, " +
                "content, " +
                "created_at " +
            "FROM " +
                "comm_news " +
            "ORDER BY created_at DESC " +
            "LIMIT 1")
    NewsDO selectLastOne();

    List<NewsDO> selectByIds(@Param("ids") List<Long> ids);

    @Select("SELECT " +
                "id " +
            "FROM " +
                "comm_news " +
            "ORDER BY created_at DESC")
    List<Long> selectPageIds();


    @ResultMap("newsDOResultMap")
    @Select("SELECT " +
            "* " +
            "FROM " +
            "comm_news " +
            "ORDER BY created_at DESC")
    List<NewsDO> selectAllNews();

    @Delete("delete from comm_news where id =#{newsId}") int removeNews(int newsId);

    @Insert("INSERT into comm_news " +
            "(title, " +
            "image_uri, " +
            "content, " +
            "created_at )" +
            " VALUES " +
            "(#{newsTitle},#{newsImg},#{newsContent},UNIX_TIMESTAMP(NOW()))")
    int addNews(NewsQuery newsQuery);

    @Update("UPDATE " +
                "comm_news " +
            "SET " +
                "is_showed = #{status}, " +
                "created_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{newsId} ")
    int updateNewsStatus(@Param("newsId") int newsId, @Param("status") int status);
}
