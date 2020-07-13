package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.ClassUserDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

@Mapper
public interface ClassUserDao {
    @Insert("INSERT " +
            "INTO " +
                "grtu_class_user(user_id, name, gender, type, mobile, company, position, brief, group_id, school_name, class_name, joined_at) " +
            "VALUES(#{userDO.userId}, #{name}, #{gender}, #{type}, #{mobile}, #{company}, #{position}, #{brief}, #{classGroupDO.groupId}, #{classGroupDO.schoolName}, #{classGroupDO.className}, UNIX_TIMESTAMP(NOW(3)))")
    int insert(ClassUserDO classUserDO);

    @Update("UPDATE grtu_class_user " +
            " SET NAME = #{name},  " +
            "   gender = #{gender},  " +
            "   type = #{type},  " +
            "   mobile = #{mobile},  " +
            "   company = #{company},  " +
            "   position = #{position},  " +
            "   brief = #{brief},  " +
            "   joined_at = UNIX_TIMESTAMP(NOW())  " +
            " WHERE    " +
            "   idx = #{idx}")
    int update(ClassUserDO classUserDO);

    @Update("UPDATE " +
                "grtu_class_user " +
            "SET " +
                "status = 2, " +
                "processed_remove_user_id = #{processedRemoveClassUserDO.userDO.userId}, " +
                "quited_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
                "user_id = #{userDO.userId} " +
                "AND group_id = #{classGroupDO.groupId} " +
                "AND status = 1 ")
    int delete(ClassUserDO classUserDO);

    @ResultMap("classUserResultMap")
    @Select("select idx from grtu_class_user_apply  where user_id=#{userId} and group_id=#{groupId} and `status`=2")
    ClassUserDO selectByUserId(long userId,long groupId);

    /**
     *
     * @param groupId
     * @param userId
     * @return
     */
    ClassUserDO selectByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @ResultMap("classUserResultMap")
    @Select("SELECT idx FROM grtu_class_user WHERE group_id = #{groupId} AND user_id = #{userId} AND status = 1")
    ClassUserDO selectIdxByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @Select("SELECT idx FROM grtu_class_user WHERE status = 1 AND group_id = #{groupId}")
    List<Long> selectIdsByGroupId(long groupId);

    /**
     * 群管理员获取申请记录
     *
     * @param userId
     * @return
     */
    @Select("select group_id from gr_class_group where user_id = #{userId} ")
    List<Long> selectIdsByAdminUserId(long userId);


    List<ClassUserDO> selectByIds(@Param("ids") List<Long> ids);

    List<ClassUserDO> selectInformationByIds(@Param("ids") List<Long> ids);

    @Select("select idx from grtu_class_user where user_id=#{userId} and group_id=#{groupId}")
    ClassUserDO isExistUser(@Param("groupId") long groupId, @Param("userId") long userId);



    @Select("select count(*) from grtu_class_user where group_id=#{groupId}")
    int selectIsExistUserInThisGroup(long groupId);

    List<ClassUserDO> selectUserInformationListByGroupId(@Param("groupId") long groupId);


    List<ClassUserDO> selectUserListInfoExportExcel(long groupId);

    ClassUserDO userInfoAdmin(@Param("userId")long userId,@Param("groupId")long groupId);

    @Select("SELECT name FROM grtu_class_user WHERE user_id =#{userId} AND group_id =#{groupId} AND status = 1")
    String getUserName(@Param("userId") long userId, @Param("groupId") long groupId);

    List<ClassUserDO> searchUserInfo(ClassUserDO classUserDO);

    @Select("SELECT user_id FROM grtu_class_user WHERE status = 1 AND group_id = #{groupId}")
    List<Long> selectUserIdByGroupId(long groupId);


}
