package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.SchoolUserFavDO;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.ResultMap;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.*;
import java.util.List;

public interface SchoolUserFavDao {

    @Insert("INSERT INTO grtu_school_user_fav(user_id, school_user_idx, created_at) VALUES(#{userId}, #{schoolUserDO.idx}, UNIX_TIMESTAMP(NOW()))")
    int insert(SchoolUserFavDO schoolUserFavDO);

    @Delete("DELETE FROM grtu_school_user_fav WHERE user_id = #{userId} AND school_user_idx = #{schoolUserDO.idx}")
    int deleteByUserIdAndClassUserIdx(SchoolUserFavDO schoolUserFavDO);

    @ResultMap("schoolUserFavResultMap")
    @Select("SELECT " +
                "idx " +
            "FROM " +
                "grtu_school_user_fav " +
            "WHERE " +
                "school_user_idx = #{groupUserId} " +
                "AND user_id = #{userId}")
    SchoolUserFavDO selectByGroupUserIdAndUserId(@Param("groupUserId") long groupUserId, @Param("userId") long userId);

    @Select("SELECT school_user_idx FROM school_user_fav WHERE user_id = #{userId}")
    List<Long> selectGroupUserIdsByUserId(long userId);

    @Select("SELECT idx FROM grtu_school_user_fav WHERE user_id = #{userId}")
    List<Long> selectIdsByUserId(long userId);

    List<SchoolUserFavDO> selectByIds(@Param("ids") List<Long> ids);
}
