package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.AddressDO;
import org.apache.ibatis.annotations.Options;
import org.apache.ibatis.annotations.ResultMap;
import org.apache.ibatis.annotations.Select;

public interface AddressDao {

    @ResultMap("addressResultMap")
    @Options
    @Select("SELECT " +
                "addr_id, " +
                "addr_name, " +
                "addr_code, " +
                "parent_code, " +
                "parent_id, " +
                "created_at " +
            "FROM " +
                "comm_address " +
            "WHERE " +
                "addr_code = #{addressCode}")
    AddressDO selectByAddressCode(String addressCode);
}
