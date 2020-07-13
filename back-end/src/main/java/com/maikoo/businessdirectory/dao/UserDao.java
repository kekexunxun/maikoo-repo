package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.UserDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface UserDao {

    @Options(useGeneratedKeys = true, keyProperty = "userId")
    @Insert("INSERT " +
            "INTO " +
                "u_user" +
                "(openid, union_id, session_key, created_at) " +
            "VALUES" +
                "(#{openid}, #{unionId}, #{sessionKey}, UNIX_TIMESTAMP(NOW()))")
    int insert(UserDO userDO);

    int update(UserDO userDO);

    @ResultMap("userResultMap")
    @Select("SELECT " +
                "user_id, " +
                "openid, " +
                "union_id, " +
                "session_key " +
            "FROM " +
                "u_user " +
            "WHERE " +
                "openid = #{openid}")
    UserDO selectByOpenId(String openid);

    @ResultMap("userResultMap")
    @Select("SELECT " +
                "user_id, " +
                "openid, " +
                "nickname, " +
                "avatar_url, " +
                "mobile, " +
                "is_auth " +
            "FROM " +
                "u_user " +
            "WHERE " +
                "user_id = #{userId}")
    UserDO selectOne(long userId);

    @ResultMap("userResultMap")
    @Select("SELECT " +
                "user_id as user_id, " +
                "nickname as nickname, " +
                "mobile as mobile, " +
                "avatar_url as avatar_url, " +
                "created_at as created_at " +
            "FROM " +
                "u_user")
    List<UserDO> selectUserList();


    @Update("UPDATE u_user " +
            " SET apply_requested_at = UNIX_TIMESTAMP( NOW( ) )  " +
            "WHERE " +
            " user_id = #{userId}")
    int updateApplyRequestAt(@Param("userId") long userId);

    @Update("UPDATE u_user " +
            "   SET review_requested_at = UNIX_TIMESTAMP( NOW( ) ) " +
            "WHERE " +
            " user_id = #{userId}")
    int updateReviewRequestAt(@Param("userId") long userId);

    @Update("UPDATE u_user  " +
            "SET message_requested_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
            "  user_id = #{userId}")
    int updateMessageRequestAt(@Param("userId") long userId);


}
