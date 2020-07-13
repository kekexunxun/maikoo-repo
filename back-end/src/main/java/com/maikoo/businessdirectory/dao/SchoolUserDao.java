package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.SchoolUserDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface SchoolUserDao {
    @ResultMap("schoolUserResultMap")
    @Select("select idx from grtu_school_user_apply  where user_id=#{userId} and group_id=#{groupId} and `status`=2")
    SchoolUserDO selectByUserId(long userId,long groupId);

    SchoolUserDO selectByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @ResultMap("schoolUserResultMap")
    @Select("SELECT idx FROM grtu_school_user WHERE group_id = #{groupId} AND user_id = #{userId} AND status = 1")
    SchoolUserDO selectIdxByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @Select("SELECT idx FROM grtu_school_user WHERE status = 1 AND group_id = #{groupId}")
    List<Long> selectIdsByGroupId(long groupId);

    List<SchoolUserDO> selectByIds(@Param("ids") List<Long> ids);

    List<SchoolUserDO> selectInformationByIds(@Param("ids") List<Long> ids);

    @Update("UPDATE " +
            " grtu_school_user " +
            " SET " +
            " `status`=2," +
            " processed_remove_user_id=#{processedRemoveSchoolUserDO.userDO.userId}," +
            " quited_at=UNIX_TIMESTAMP(NOW())" +
            " WHERE " +
            " user_id=#{userDO.userId} " +
            " AND group_id=#{schoolGroupDO.groupId} " +
            " AND status = 1")
    int delete(SchoolUserDO schoolUserDO);


    @Update("UPDATE grtu_school_user " +
            " SET " +
            " NAME = #{name} ," +
            " gender=#{gender}," +
            " type=#{type}," +
            " mobile=#{mobile}," +
            " company=#{company}," +
            " position=#{position}," +
            " brief = #{brief}, " +
            " graduated_at=#{graduatedAt}," +
            " joined_at=UNIX_TIMESTAMP(NOW()) " +
            " where " +
            " idx = #{idx}")
    int update(SchoolUserDO schoolUserDO);

    @Insert("INSERT " +
            "INTO " +
                "grtu_school_user " +
                "(user_id, NAME, gender, type, mobile, company, position, brief, group_id,school_name,graduated_at,status,joined_at) " +
            "VALUES " +
                "(#{userDO.userId}, #{name}, #{gender}, #{type}, #{mobile}, #{company}, #{position}, #{brief}, #{schoolGroupDO.groupId}, #{schoolGroupDO.schoolName}, #{graduatedAt}, 1,UNIX_TIMESTAMP(NOW(3)))")
    int insert(SchoolUserDO schoolUserDO);



    @Select("select group_id from gr_school_group where user_id = #{userId} ")
    List<Long> selectIdsByAdminUserId(long userId);



    @Select("select count(*) from grtu_school_user where group_id=#{groupId}")
    int selectIsExistUserInThisGroup(long groupId);

    List<SchoolUserDO> selectUserInformationListByGroupId(@Param("groupId") long groupId);

    List<SchoolUserDO> selectUserListInfoExportExcel(long groupId);

    SchoolUserDO userInfoAdmin(@Param("userId")long userId, @Param("groupId")long groupId);

    @Select("SELECT name FROM grtu_school_user WHERE user_id=#{userId} AND group_id=#{groupId} AND status = 1 ")
    String getUserName(@Param("userId") long userId, @Param("groupId") long groupId);

    List<SchoolUserDO>searchUserInfo(SchoolUserDO schoolUserDO);


    @Select("SELECT user_id FROM grtu_school_user WHERE status = 1 AND group_id = #{groupId}")
    List<Long> selectUserIdByGroupId(long groupId);

}
