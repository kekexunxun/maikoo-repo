package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.CommunityUserFavDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface CommunityUserFavDao {

    @Insert("INSERT INTO grtu_community_user_fav(user_id, community_user_idx, created_at) VALUES(#{userId}, #{communityUserDO.idx}, UNIX_TIMESTAMP(NOW()))")
    int insert(CommunityUserFavDO communityUserFavDO);

    @Delete("DELETE FROM grtu_community_user_fav WHERE user_id = #{userId} AND community_user_idx = #{communityUserDO.idx}")
    int deleteByUserIdAndClassUserIdx(CommunityUserFavDO communityUserFavDO);

    @ResultMap("communityUserFavResultMap")
    @Select(value = "SELECT " +
            " idx  " +
            "FROM " +
            " grtu_community_user_fav gcuf  " +
            "WHERE " +
            " community_user_idx = #{groupUserId} " +
            " AND user_id = #{userId}")
    CommunityUserFavDO selectByGroupUserIdAndUserId(@Param("groupUserId") long groupUserId, @Param("userId") long userId);

    @Select("SELECT community_user_idx FROM community_user_fav WHERE user_id = #{userId}")
    List<Long> selectGroupUserIdsByUserId(long userId);

    @Select("SELECT idx FROM grtu_community_user_fav WHERE user_id = #{userId}")
    List<Long> selectIdsByUserId(long userId);

    List<CommunityUserFavDO> selectByIds(@Param("ids") List<Long> ids);
}
