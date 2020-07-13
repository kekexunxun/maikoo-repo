package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.CommunityUserDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface CommunityUserDao {
    @ResultMap("communityUserResultMap")
    @Select("select idx from grtu_community_user_apply  where user_id=#{userId} and group_id=#{groupId} and `status`=2")
    CommunityUserDO selectByUserId(long userId,long groupId);

    @Update("UPDATE " +
            " grtu_community_user " +
            " SET " +
            " `status`=2," +
            " processed_remove_user_id=#{processedRemoveCommunityUserDO.userDO.userId}," +
            " quited_at=UNIX_TIMESTAMP(NOW())" +
            " WHERE " +
            " user_id=#{userDO.userId} " +
            " AND group_id=#{communityGroupDO.groupId} " +
            " AND status = 1")
    int delete(CommunityUserDO CommunityUserDO);

    @ResultMap("communityUserResultMap")
    @Select("SELECT " +
            " gcu.idx as idx, " +
            " uu.user_id as user_user_id, " +
            " uu.avatar_url AS user_avatar_url, " +
            " gcu.position AS position, " +
            " gcu.company AS company, " +
            " gcu.type AS type, " +
            " gcu.mobile AS mobile, " +
            " gcu.brief AS brief, " +
            " gcg.community_name AS community_group_group_name, " +
            " gcu.building AS building, " +
            " gcu.room AS room,  " +
            " gcg.user_id AS community_group_user_user_id  " +
            " FROM " +
            " grtu_community_user gcu " +
            " LEFT JOIN u_user uu ON uu.user_id = gcu.user_id " +
            " LEFT JOIN gr_community_group gcg ON gcu.group_id = gcg.group_id " +
            "WHERE " +
            " gcu.idx = #{groupId}  " +
            " AND gcu.user_id = #{userId}")
    CommunityUserDO information(@Param("userId") long userId ,@Param("groupId") long groupId );

    CommunityUserDO selectByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @ResultMap("communityUserResultMap")
    @Select("SELECT idx FROM grtu_community_user WHERE group_id = #{groupId} AND user_id = #{userId} AND status = 1")
    CommunityUserDO selectIdxByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @Select("SELECT idx FROM grtu_community_user WHERE status = 1 AND group_id = #{groupId}")
    List<Long> selectIdsByGroupId(long groupId);

    List<CommunityUserDO> selectByIds(@Param("ids") List<Long> ids);

    List<CommunityUserDO> selectInformationByIds(@Param("ids") List<Long> ids);

    @Insert("INSERT INTO grtu_community_user ( user_id,group_id, position, company, mobile, brief, gender, NAME, type, building, room ) " +
            " VALUES " +
            " (#{userDO.userId},#{communityGroupDO.groupId},#{position},#{company},#{mobile},#{brief},#{gender},#{name},#{type},#{building},#{room})")
    int insertUser(CommunityUserDO communityUserDO);

    @Update("UPDATE grtu_community_user  " +
            " SET position = #{postion}, company=#{company}, mobile=#{mobile}, brief=#{brief}, " +
            "gender=#{gender}, NAME=#{name}, type=#{type}, building=#{building}, room=#{room} " +
            "where " +
            "user_id=#{userDO.userId} and group_id=#{communityGroupDO.groupId}")
    int updateUser(CommunityUserDO communityUserDO);

    @Insert("INSERT " +
            "INTO " +
                "grtu_community_user " +
                "(user_id, NAME, gender, type, mobile, company, position, brief, group_id, community_name, building, room, joined_at ) " +
            "VALUES " +
                "(#{userDO.userId}, #{name}, #{gender}, #{type}, #{mobile}, #{company}, #{position}, #{brief}, #{communityGroupDO.groupId}, #{communityGroupDO.communityName}, #{building},#{room}, UNIX_TIMESTAMP(NOW(3)))")
    int insert(CommunityUserDO communityUserDO);

    @Update("UPDATE grtu_community_user " +
            "  SET " +
            "  NAME = #{name} ," +
            "  gender=#{gender}," +
            "  type=#{type}," +
            "  mobile=#{mobile}," +
            "  company=#{company}," +
            "  position=#{position}," +
            "  building=#{building}," +
            "  room=#{room}," +
            "  brief = #{brief}," +
            "  joined_at=UNIX_TIMESTAMP(NOW()) " +
            " where " +
            "  idx = #{idx}")
    int update(CommunityUserDO communityUserDO);





    @Select("select group_id from gr_community_group where user_id = #{userId} ")
    List<Long> selectIdsByAdminUserId(long userId);




    @Select("select count(*) from grtu_community_user where group_id=#{groupId}")
    int selectIsExistUserInThisGroup(long groupId);

    List<CommunityUserDO> selectUserInformationListByGroupId(@Param("groupId") long groupId);

    List<CommunityUserDO> selectUserListInfoExportExcel(long groupId);

    CommunityUserDO userInfoAdmin(@Param("userId")long userId, @Param("groupId")long groupId);

    @Select("SELECT name FROM grtu_community_user where user_id=#{userId} AND group_id=#{groupId} AND status = 1")
    String getUserName(@Param("userId") long userId, @Param("groupId") long groupId);

    List<CommunityUserDO> searchUserInfo(CommunityUserDO communityUserDO);

    @Select("SELECT user_id FROM grtu_community_user WHERE status = 1 AND group_id = #{groupId}")
    List<Long> selectUserIdByGroupId(long groupId);
}
