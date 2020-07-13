package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.CommunityGroupDO;
import com.maikoo.businessdirectory.model.TimeFrequentQuery;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface CommunityGroupDao {
    @Options(useGeneratedKeys = true, keyProperty = "groupId")
    @Insert("INSERT " +
            "INTO " +
                "gr_community_group" +
                    "(group_name, group_avatar_url, group_brief, group_addr_code, " +
                    "group_addr_detail, community_name, user_id, is_enable, created_at) " +
            "VALUES" +
                "(#{groupName}, #{groupAvatarUrl}, #{groupBrief}, #{groupAddrCode}, " +
                "#{groupAddrDetail}, #{communityName}, #{userDO.userId}, 1, UNIX_TIMESTAMP(NOW(3)))")
    int insert(CommunityGroupDO communityGroupDO);

    @Update("Update " +
                "gr_community_group " +
            "SET " +
                "group_name = #{groupName}, " +
                "group_avatar_url = #{groupAvatarUrl}, " +
                "group_brief = #{groupBrief}, " +
                "group_addr_code = #{groupAddrCode}, " +
                "group_addr_detail = #{groupAddrDetail}, " +
                "community_name = #{communityName}, " +
                "updated_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
                "group_id = #{groupId}")
    int update(CommunityGroupDO communityGroupDO);

    @Update("Update " +
                "gr_community_group " +
            "SET " +
                "poster_url = #{posterUrl}, " +
                "qr_code_url = #{qrCodeUrl}, " +
                "updated_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
                "group_id = #{groupId}")
    int updateShareUrl(CommunityGroupDO communityGroupDO);

    @ResultMap("communityGroupResultMap")
    @Select("SELECT " +
                "group_id " +
            "FROM " +
                "gr_community_group " +
            "WHERE " +
                "user_id = #{userId} " +
                "AND group_id = #{groupId}")
    CommunityGroupDO isExistedByUserIdAndGroupId(@Param("userId") long userId, @Param("groupId") long groupId);

    @Update("UPDATE " +
            "gr_community_group " +
            "SET " +
            "is_enable = 0, " +
            "dismissed_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
            "group_id = #{id}")
    int dismiss(long id);

    @ResultMap("communityGroupResultMap")
    @Select("SELECT " +
                "group_id, " +
                "group_name, " +
                "group_avatar_url, " +
                "group_brief, " +
                "group_addr_code, " +
                "group_addr_detail, " +
                "community_name, " +
                "is_enable, " +
                "created_at, " +
                "dismissed_at, " +
                "poster_url," +
                "qr_code_url," +
                "user_id AS user_user_id " +
            "FROM " +
                "gr_community_group " +
            "WHERE " +
                "group_id = #{id}")
    CommunityGroupDO selectOne(long id);

    @Update("update gr_community_group " +
            " set " +
            " user_id =#{userId} " +
            " where " +
            " group_id=#{groupId}")
    int changeOwner(@Param("userId") long userId,@Param("groupId") long groupId);

    List<Long> selectIdsByKeyAndUserId(@Param("key") String key, @Param("userId") long userId);

    List<CommunityGroupDO> selectByIds(@Param("ids") List<Long> ids);

    Integer analysisGroupData(TimeFrequentQuery timeFrequentQuery);

    Integer analysisGroupUserData(TimeFrequentQuery timeFrequentQuery);

    List<CommunityGroupDO> selectAll();

    @Select("select group_name from gr_community_group where group_id =#{groupId}")
    String getGroupName(long groupId);
}
