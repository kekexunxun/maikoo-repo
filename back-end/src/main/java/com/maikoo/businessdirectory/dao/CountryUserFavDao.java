package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.CountryUserFavDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface CountryUserFavDao {

    @Insert("INSERT INTO grtu_country_user_fav(user_id, country_user_idx, created_at) VALUES(#{userId}, #{countryUserDO.idx}, UNIX_TIMESTAMP(NOW()))")
    int insert(CountryUserFavDO countryUserFavDO);

    @Delete("DELETE FROM grtu_country_user_fav WHERE user_id = #{userId} AND country_user_idx = #{countryUserDO.idx}")
    int deleteByUserIdAndClassUserIdx(CountryUserFavDO countryUserFavDO);

    @ResultMap("countryUserFavResultMap")
    @Select("SELECT " +
            " idx  " +
            "FROM " +
            " grtu_country_user_fav gcuf  " +
            "WHERE " +
            " country_user_idx = #{groupUserId} " +
            " AND user_id = #{userId}")
    CountryUserFavDO selectByGroupUserIdAndUserId(@Param("groupUserId") long groupUserId, @Param("userId") long userId);

    @Select("SELECT country_user_idx FROM country_user_fav WHERE user_id = #{userId}")
    List<Long> selectGroupUserIdsByUserId(long userId);

    @Select("SELECT idx FROM grtu_country_user_fav WHERE user_id = #{userId}")
    List<Long> selectIdsByUserId(long userId);

    List<CountryUserFavDO> selectByIds(@Param("ids") List<Long> ids);
}
