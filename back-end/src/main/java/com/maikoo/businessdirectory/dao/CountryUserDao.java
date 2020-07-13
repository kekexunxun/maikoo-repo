package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.CountryUserDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface CountryUserDao {

    @ResultMap("countryUserResultMap")
    @Select("select idx from grtu_country_user_apply  where user_id=#{userId} and group_id=#{groupId} and `status`=2")
    CountryUserDO selectByUserId(long userId ,long groupId);

    @Update("UPDATE " +
            " grtu_country_user " +
            " SET " +
            " `status`=2," +
            " processed_remove_user_id=#{processedRemoveCountryUserDO.userDO.userId}," +
            " quited_at=UNIX_TIMESTAMP(NOW())" +
            " WHERE " +
            " user_id=#{userDO.userId} " +
            " AND group_id=#{countryGroupDO.groupId} " +
            " AND status = 1 ")
    int delete(CountryUserDO countryUserDO);

    CountryUserDO selectByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @ResultMap("countryUserResultMap")
    @Select("SELECT idx FROM grtu_country_user WHERE group_id = #{groupId} AND user_id = #{userId} AND status = 1")
    CountryUserDO selectIdxByGroupIdAndUserId(@Param("groupId") long groupId, @Param("userId") long userId);

    @Select("SELECT idx FROM grtu_country_user WHERE status = 1 AND group_id = #{groupId}")
    List<Long> selectIdsByGroupId(long groupId);

    List<CountryUserDO> selectByIds(@Param("ids") List<Long> ids);

    List<CountryUserDO> selectInformationByIds(@Param("ids") List<Long> ids);



    @Insert("INSERT INTO " +
            " grtu_country_user ( user_id, NAME, gender, mobile, company, position, brief, " +
            " group_id,tag,joined_at ) " +
            "VALUES " +
            " (#{userDO.userId}, #{name}, #{gender}, #{mobile}, #{company},#{position},#{brief}, " +
            " #{countryGroupDO.groupId}, #{tag}, UNIX_TIMESTAMP(NOW(3)))")
    int insert(CountryUserDO countryUserDO);

    @Update(" UPDATE   " +
            " grtu_country_user   " +
            " SET   " +
            " NAME=#{name} ," +
            " gender=#{gender}," +
            " mobile=#{mobile}," +
            " company=#{company}," +
            " position=#{position}," +
            " brief = #{brief}," +
            " joined_at=UNIX_TIMESTAMP(NOW()) ," +
            " tag=#{tag}  " +
            " where   " +
            "  idx = #{idx} ")
    int update(CountryUserDO countryUserDO);




    @Select("select group_id from gr_country_group where user_id = #{userId} ")
    List<Long> selectIdsByAdminUserId(long userId);



    @Select("select count(*) from grtu_country_user where group_id=#{groupId}")
    int selectIsExistUserInThisGroup(long groupId);

    List<CountryUserDO> selectUserInformationListByGroupId(@Param("groupId") long groupId);

    List<CountryUserDO> selectUserListInfoExportExcel(long groupId);

    CountryUserDO userInfoAdmin(@Param("userId")long userId, @Param("groupId")long groupId);

    @Select("SELECT name FROM grtu_country_user WHERE user_id=#{userId} AND group_id=#{groupId} AND status = 1")
    String getUserName(@Param("userId") long userId,@Param("groupId") long groupId);

    List<CountryUserDO> searchUserInfo(CountryUserDO countryUserDO);

    @Select("SELECT user_id FROM grtu_country_user WHERE status = 1 AND group_id = #{groupId}")
    List<Long> selectUserIdByGroupId(long groupId);
}
