package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.FormIdDO;
import org.apache.ibatis.annotations.*;

import java.util.List;

public interface FormIdDao {
    @Insert("INSERT " +
            "INTO " +
                "comm_formid" +
                "(user_id, form_id, expire_at) " +
            "VALUES" +
                "(#{userId}, #{formId}, #{expireAt})")
    int insert(FormIdDO formIdDO);

    @Update("UPDATE " +
                "comm_formid " +
            "SET " +
                "is_used = #{isUsed} " +
            "WHERE " +
                "idx = #{idx}")
    int updateIsUsed(@Param("idx") long idx, @Param("isUsed") boolean isUsed);

    @ResultMap("formIdResultMap")
    @Select("SELECT " +
                "idx, " +
                "form_id " +
            "FROM " +
                "comm_formid " +
            "WHERE " +
                "user_id = #{userId} " +
                "AND expire_at > #{expireAt} " +
                "AND is_used = #{isUsed}")
    List<FormIdDO> selectByUserIdAndExpireAtAndIsUsed(FormIdDO formIdDO);
}
