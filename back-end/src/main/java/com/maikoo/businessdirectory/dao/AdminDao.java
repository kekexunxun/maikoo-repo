package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.AdminDO;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.ResultMap;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

public interface AdminDao {
    @Update("UPDATE u_admin SET password = #{password} WHERE admin_id = #{adminId}")
    int updatePassword(AdminDO adminDO);

    @ResultMap("adminResultMap")
    @Select("SELECT admin_id, username, password FROM u_admin WHERE admin_id = #{adminId}")
    AdminDO selectOne(long adminId);

    @ResultMap("adminResultMap")
    @Select("SELECT admin_id FROM u_admin WHERE username = #{username} AND password = #{password}")
    AdminDO selectByUsernameAndPassword(@Param("username") String username, @Param("password") String password);
}
