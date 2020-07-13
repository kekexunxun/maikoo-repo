package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.SchoolUserApplyDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface SchoolUserApplyDao {
    @Insert("INSERT INTO grtu_school_user_apply ( user_id, NAME, gender, type, mobile, company, position, brief, group_id, `status`,graduated_at, applied_at ) " +
            "VALUES " +
            " (#{userDO.userId},#{name},#{gender} ,#{type} ,#{mobile} ,#{company} ,#{position} ,#{brief} ,#{schoolGroupDO.groupId} ,1,#{graduatedAt} ,UNIX_TIMESTAMP(NOW()));")
    int insert(SchoolUserApplyDO schoolUserApplyDO);

    @Update("UPDATE " +
                "grtu_school_user_apply " +
            "SET " +
                "processed_user_id = #{processedUserId}, " +
                "status = #{status}, " +
                "processed_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "idx = #{idx} ")
    int updateStatus(SchoolUserApplyDO schoolUserApplyDO);

    List<SchoolUserApplyDO>  selectApplyRecordByIds(@Param("ids") List<Long> ids);

    List<SchoolUserApplyDO> selectReviewRecordByIds(@Param("ids") List<Long> ids);

    List<SchoolUserApplyDO> selectReviewRecordByGroupUserId(long userId);

    SchoolUserApplyDO selectByApplyId(@Param("applyId") long applyId);

    @Select("SELECT " +
                "idx " +
            "FROM " +
                "grtu_school_user_apply " +
            "WHERE " +
                "group_id = #{groupId} " +
                "AND user_id = #{userId} " +
                "AND status = 1")
    Long selectNotReviewedApplyByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @ResultMap("schoolUserApplyResultMap")
    @Select("Select" +
            "    group_id as school_group_group_id " +
            " FROM " +
            "    grtu_school_user_apply " +
            "WHERE " +
            "    idx=#{applyId} ")
    SchoolUserApplyDO selectGroupIdByApplyId(long applyId);


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
            " grtu_school_user_apply  " +
            "WHERE " +
            " user_id = #{userId}  " +
            " AND processed_at > ( SELECT apply_requested_at FROM u_user WHERE user_id = #{userId} )")
    boolean userHasNewMessage(@Param("userId") long userId);


    /**
     * 查询管理员是否有新的消息审核
     * @param userId
     * @return
     */
    @Select("SELECT " +
            "CASE " +
            "    count( * )  " +
            "    WHEN 0 THEN " +
            "    'false' ELSE 'true'  " +
            "    END AS result  " +
            "FROM " +
            "    grtu_school_user_apply  " +
            "WHERE " +
            "    `status` = 1  " +
            "    AND group_id IN ( SELECT group_id FROM gr_school_group WHERE user_id = #{userId} AND is_enable = 1 )")
    boolean adminHasNewMessage(@Param("userId") long userId);

    /**
     * 获取用户自身的申请记录
     *
     * @param userId
     * @return
     */
    @Select("select idx from  grtu_school_user_apply where user_id =#{userId}")
    List<Long> selectIdsByUserId(long userId);

    SchoolUserApplyDO isApplyUser(@Param("userId") long userId, @Param("groupId") long groupId);

    List<SchoolUserApplyDO> selectNotReviewByDateTime(@Param("beginTimestamp") long beginTimestamp, @Param("endTimestamp") long endTimestamp);
}
