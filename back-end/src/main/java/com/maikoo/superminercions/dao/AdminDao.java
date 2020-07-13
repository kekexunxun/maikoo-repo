package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.AdminDO;
import org.apache.ibatis.annotations.Mapper;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

@Mapper
public interface AdminDao {

    @Select("SELECT id, username FROM s_user WHERE username = #{username} AND password = #{password}")
    AdminDO login(@Param("username") String username, @Param("password") String password);

    @Update("UPDATE s_user SET password = #{password}, updated_at = UNIX_TIMESTAMP(NOW()) WHERE id = #{id}")
    int updatePassword(AdminDO adminDO);

    @Select("SELECT IF(password=#{newPassword}, 1, 0) FROM s_user WHERE id = #{id}")
    boolean checkOldPassword(@Param("id") long id, @Param("newPassword") String newPassword);
}
