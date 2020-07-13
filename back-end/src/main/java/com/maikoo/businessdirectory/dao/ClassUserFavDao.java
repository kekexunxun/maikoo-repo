package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.ClassUserFavDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface ClassUserFavDao {

    @Insert("INSERT INTO grtu_class_user_fav(user_id, class_user_idx, created_at) VALUES(#{userId}, #{classUserDO.idx}, UNIX_TIMESTAMP(NOW()))")
    int insert(ClassUserFavDO classUserFavDO);

    @Delete("DELETE FROM grtu_class_user_fav WHERE user_id = #{userId} AND class_user_idx = #{classUserDO.idx}")
    int deleteByUserIdAndClassUserIdx(ClassUserFavDO classUserFavDO);

    @ResultMap("classUserFavResultMap")
    @Select("SELECT " +
                "idx " +
            "FROM " +
                "grtu_class_user_fav " +
            "WHERE " +
                "class_user_idx = #{groupUserId} " +
                "AND user_id = #{userId}")
    ClassUserFavDO selectByGroupUserIdAndUserId(@Param("groupUserId") long groupUserId, @Param("userId") long userId);

    @Select("SELECT class_user_idx FROM class_user_fav WHERE user_id = #{userId}")
    List<Long> selectGroupUserIdsByUserId(long userId);

    @Select("SELECT idx FROM grtu_class_user_fav WHERE user_id = #{userId}")
    List<Long> selectIdsByUserId(long userId);

    List<ClassUserFavDO> selectByIds(@Param("ids") List<Long> ids);
}
