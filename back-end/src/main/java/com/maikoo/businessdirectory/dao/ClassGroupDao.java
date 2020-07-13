package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.ClassGroupDO;
import com.maikoo.businessdirectory.model.TimeFrequentQuery;
import org.apache.ibatis.annotations.*;

import java.util.List;

@Mapper
public interface ClassGroupDao {
    @Options(useGeneratedKeys = true, keyProperty = "groupId")
    @Insert("INSERT " +
            "INTO " +
                "gr_class_group" +
                "(group_name, group_avatar_url, group_brief, group_addr_code, " +
                    "group_addr_detail, school_name, class_name, user_id, is_enable, created_at) " +
            "VALUES" +
                "(#{groupName}, #{groupAvatarUrl}, #{groupBrief}, #{groupAddrCode}, " +
                    "#{groupAddrDetail}, #{schoolName}, #{className}, #{userDO.userId}, 1, UNIX_TIMESTAMP(NOW(3)))")
    int insert(ClassGroupDO classGroupDO);

    @Update("UPDATE " +
                "gr_class_group " +
            "SET " +
                "group_name = #{groupName}, " +
                "group_avatar_url = #{groupAvatarUrl}, " +
                "group_brief = #{groupBrief}, " +
                "group_addr_code = #{groupAddrCode}, " +
                "group_addr_detail = #{groupAddrDetail}, " +
                "school_name = #{schoolName}, " +
                "class_name = #{className}, " +
                "updated_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
                "group_id = #{groupId}")
    int update(ClassGroupDO classGroupDO);

    @Update("UPDATE " +
                "gr_class_group " +
            "SET " +
                "poster_url = #{posterUrl}, " +
                "qr_code_url = #{qrCodeUrl}, " +
                "updated_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
                "group_id = #{groupId}")
    int updateShareUrl(ClassGroupDO classGroupDO);

    @ResultMap("classGroupResultMap")
    @Select("SELECT " +
                "group_id " +
            "FROM " +
                "gr_class_group " +
            "WHERE " +
                "user_id = #{userId} " +
                "AND group_id = #{groupId}")
    ClassGroupDO isExistedByUserIdAndGroupId(@Param("userId") long userId, @Param("groupId") long groupId);

    @ResultMap("classGroupResultMap")
    @Select("SELECT " +
                "group_id, " +
                "group_name, " +
                "group_avatar_url, " +
                "group_brief, " +
                "group_addr_code, " +
                "group_addr_detail, " +
                "school_name, " +
                "class_name, " +
                "is_enable, " +
                "created_at, " +
                "dismissed_at, " +
                "poster_url, " +
                "qr_code_url, " +
                "user_id AS user_user_id " +
            "FROM " +
                "gr_class_group " +
            "WHERE " +
                "group_id = #{id}")
    ClassGroupDO selectOne(long id);

    @Update("UPDATE " +
                "gr_class_group " +
            "SET " +
                "is_enable = 0, " +
                "dismissed_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
                "group_id = #{id}")
    int dismiss(long id);

    @Update("update gr_class_group " +
            " set " +
            " user_id =#{userId} " +
            " where " +
            " group_id=#{groupId}")
    int changeOwner(@Param("userId") long userId,@Param("groupId") long groupId);

    List<Long> selectIdsByKeyAndUserId(@Param("key") String key, @Param("userId") long userId);

    List<ClassGroupDO> selectByIds(@Param("ids") List<Long> ids);

    Integer analysisGroupData(TimeFrequentQuery timeFrequentQuery);

    Integer analysisGroupUserData(TimeFrequentQuery timeFrequentQuery);

    List<ClassGroupDO> selectAll();

    @Select("select group_name from gr_class_group where group_id=#{group_id}")
    String getGroupName(long groupId);

}
