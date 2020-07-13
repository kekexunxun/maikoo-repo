package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.SettingDO;
import org.apache.ibatis.annotations.ResultMap;
import org.apache.ibatis.annotations.Select;

public interface SettingDao {

    int update(SettingDO settingDO);

    @ResultMap("settingResultMap")
    @Select("SELECT " +
                "smc_price," +
                "eth_price," +
                "withdrawal_fee," +
                "smc_fee " +
            "FROM " +
                "comm_setting")
    SettingDO select();
}
