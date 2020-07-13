package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.ClassUserApplyDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface ClassUserApplyDao {

    @Insert("INSERT INTO " +
            "grtu_class_user_apply " +
            "( user_id, NAME, gender, type, mobile, company, position, brief, group_id, `status`, applied_at ) " +
            "VALUES " +
            "( #{userDO.userId},#{name},#{gender} ,#{type} ,#{mobile} ,#{company} ,#{position} ,#{brief} ,#{classGroupDO.groupId} ,1 ,UNIX_TIMESTAMP(NOW()))")
    int insert(ClassUserApplyDO classUserApplyDO);


    /**
     * 通过群用户ID获取申请记录
     *
     * @param ids
     * @return
     */
    List<ClassUserApplyDO> selectApplyRecordByIds(@Param("ids") List<Long> ids);

    List<ClassUserApplyDO> selectReviewRecordByIds(@Param("ids") List<Long> ids);

    List<ClassUserApplyDO> selectReviewRecordByGroupUserId(long userId);

    ClassUserApplyDO selectByApplyId(@Param("applyId") long applyId);

    @Select("SELECT " +
                "idx " +
            "FROM " +
                "grtu_class_user_apply " +
            "WHERE " +
                "group_id = #{groupId} " +
                "AND user_id = #{userId} " +
                "AND status = 1")
    Long selectNotReviewedApplyByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @Update("UPDATE " +
                "grtu_class_user_apply " +
            "SET " +
                "processed_user_id = #{processedUserId}, " +
                "status = #{status}, " +
                "processed_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "idx = #{idx} ")
    int updateStatus(ClassUserApplyDO classUserApplyDO);

    @ResultMap("classUserApplyResultMap")
    @Select("Select" +
            "    group_id as class_group_group_id " +
            " FROM " +
            "    grtu_class_user_apply " +
            "WHERE " +
            "    idx=#{applyId} ")
    ClassUserApplyDO selectGroupIdByApplyId(long applyId);

    /**
     * 查询用户是否有申请消息
     * @param userId
     * @return
     */
    @Select("SELECT " +
            "CASE " +
            " count( * )  " +
            " WHEN 0 THEN " +
            " 'false' ELSE 'true'  " +
            " END AS result  " +
            "FROM " +
            " grtu_class_user_apply  " +
            "WHERE " +
            " user_id = #{userId}  " +
            " AND processed_at > ( SELECT apply_requested_at FROM u_user WHERE user_id = #{userId} )")
    boolean userHasNewMessage(@Param("userId") long userId);

    /**
     * 查询管理员是否有审核消息
     * @param userId
     * @return
     */
    @Select("SELECT " +
            "CASE " +
            " count( * )  " +
            " WHEN 0 THEN " +
            " 'false' ELSE 'true'  " +
            " END AS result  " +
            "FROM " +
            " grtu_class_user_apply  " +
            "WHERE " +
            " `status` = 1  " +
            " AND group_id IN ( SELECT group_id FROM gr_class_group WHERE user_id = #{userId} AND is_enable = 1 )")
    boolean adminHasNewMessage(@Param("userId") long userId);

    /**
     * 获取用户自身的申请记录
     *
     * @param userId
     * @return
     */
    @Select("select idx from  grtu_class_user_apply where user_id =#{userId}")
    List<Long> selectIdsByUserId(long userId);

    ClassUserApplyDO isApplyUser(@Param("userId") long userId, @Param("groupId") long groupId);

    List<ClassUserApplyDO> selectNotReviewByDateTime(@Param("beginTimestamp") long beginTimestamp, @Param("endTimestamp") long endTimestamp);
}
