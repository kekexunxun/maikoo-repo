package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.SlideshowDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

@Mapper
public interface SlideshowDao {

    @ResultMap("slideshowDOResultMap")
    @Select("SELECT " +
                "image_uri, " +
                "comm_news_id, " +
                "page_type " +
            "FROM " +
                "comm_slideshow " +
            "WHERE " +
                "is_enabled = 1")
    List<SlideshowDO> selectByEnabled();

    @ResultMap("slideshowDOResultMap")
    @Select("SELECT " +
                "cs.id, " +
                "cs.image_uri, " +
                "cs.rank, " +
                "cs.is_enabled, " +
                "cn.title AS comm_news_title " +
            "FROM " +
                "comm_slideshow cs " +
            "LEFT JOIN " +
                "comm_news cn " +
                    "ON cs.comm_news_id = cn.id")
    List<SlideshowDO> selectAll();

    @Select("SELECT COUNT(id) FROM comm_slideshow WHERE is_enabled = 1")
    int selectEnabledCount();

    @Insert("INSERT " +
            "INTO " +
                "comm_slideshow" +
                "(image_uri, comm_news_id, page_type, is_enabled) " +
            "VALUES " +
                "(#{imageUri}, #{newsDO.id}, #{pageType}, #{isEnabled})")
    int insert(SlideshowDO slideshowDO);

    @Update("UPDATE " +
                "comm_slideshow " +
            "SET " +
                "image_uri = #{imageUri}, " +
                "page_type = #{pageType}, " +
                "comm_news_id = #{newsDO.id} " +
            "WHERE " +
                "id = #{id}")
    int updateSlideshow(SlideshowDO slideshowDO);

    @Update("UPDATE " +
                "comm_slideshow  " +
            "SET " +
                "is_enabled = #{isEnabled} " +
            "WHERE " +
                "id =#{id}")
    int updateSlideshowStatus(SlideshowDO slideshowDO);

    @Delete("DELETE FROM comm_slideshow WHERE id = #{id}")
    int delete(long id);
}
