package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.CountryGroupDO;
import com.maikoo.businessdirectory.model.TimeFrequentQuery;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface CountryGroupDao {
    @Options(useGeneratedKeys = true, keyProperty = "groupId")
    @Insert("insert into gr_country_group " +
            " (group_name,group_avatar_url,group_addr_code,group_addr_detail,group_brief,user_id,is_enable,created_at) " +
            "  values " +
            " (#{groupName},#{groupAvatarUrl},#{groupAddrCode},#{groupAddrDetail},#{groupBrief},#{userDO.userId},1,UNIX_TIMESTAMP(NOW(3)))")
    int insert(CountryGroupDO countryGroupDO);

    @Update(" update gr_country_group " +
            " set group_name=#{groupName},group_avatar_url=#{groupAvatarUrl},group_addr_code=#{groupAddrCode}," +
            " group_addr_detail=#{groupAddrDetail},group_brief=#{groupBrief} " +
            " where " +
            " group_id=#{groupId}")
    int update(CountryGroupDO countryGroupDO);

    @Update("UPDATE " +
                "gr_country_group " +
            "SET " +
                "poster_url = #{posterUrl}, " +
                "qr_code_url = #{qrCodeUrl}, " +
                "updated_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
                "group_id = #{groupId}")
    int updateShareUrl(CountryGroupDO countryGroupDO);

    @ResultMap("countryGroupResultMap")
    @Select("SELECT " +
            "group_id " +
            "FROM " +
            "gr_country_group " +
            "WHERE "+
            "user_id = #{userId} " +
            "AND group_id = #{groupId}")
    CountryGroupDO isExistedByUserIdAndGroupId(@Param("userId") long userId, @Param("groupId") long groupId);

    @Update("UPDATE " +
            "gr_country_group " +
            "SET " +
            "is_enable = 0, " +
            "dismissed_at = UNIX_TIMESTAMP(NOW(3)) " +
            "WHERE " +
            "group_id = #{id}")
    int dissmiss(long id);

    @ResultMap("countryGroupResultMap")
    @Select("SELECT " +
                "group_id, " +
                "group_name, " +
                "group_avatar_url, " +
                "group_brief, " +
                "group_addr_code, " +
                "group_addr_detail, " +
                "is_enable, " +
                "created_at, " +
                "dismissed_at, " +
                "poster_url," +
                "qr_code_url," +
                "user_id AS user_user_id " +
            "FROM " +
                "gr_country_group " +
            "WHERE " +
                "group_id = #{id}")
    CountryGroupDO selectOne(long id);

    @Update("update gr_country_group " +
            " set " +
            " user_id =#{userId} " +
            " where " +
            " group_id=#{groupId}")
    int changeOwner(@Param("userId") long userId, @Param("groupId")long groupId);

    List<Long> selectIdsByKeyAndUserId(@Param("key") String key, @Param("userId") long userId);

    List<CountryGroupDO> selectByIds(@Param("ids") List<Long> ids);

    Integer analysisGroupData(TimeFrequentQuery timeFrequentQuery);

    Integer analysisGroupUserData(TimeFrequentQuery timeFrequentQuery);

    List<CountryGroupDO> selectAll();

    @Select("select group_name from gr_country_group where group_id=#{groupId}")
    String getGroupName(long groupId);
}
