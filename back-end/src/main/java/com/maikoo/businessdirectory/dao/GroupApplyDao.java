package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.GroupApplyDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Options;
import org.apache.ibatis.annotations.ResultMap;
import org.apache.ibatis.annotations.Select;

public interface GroupApplyDao {

    @Options(useGeneratedKeys = true, keyProperty="applyId")
    @Insert("INSERT INTO u_group_apply(user_id, applied_at) VALUES(#{userId}, UNIX_TIMESTAMP(NOW(3)))")
    int insert(GroupApplyDO groupApplyDO);

    @ResultMap("groupApplyResultMap")
    @Select("SELECT " +
                "apply_id, " +
                "user_id, " +
                "processed_user_id, " +
                "status, " +
                "applied_at, " +
                "processed_at  " +
            "FROM " +
                "u_group_apply " +
            "WHERE " +
                "apply_id = #{id}")
    GroupApplyDO selectOne(long id);
}
