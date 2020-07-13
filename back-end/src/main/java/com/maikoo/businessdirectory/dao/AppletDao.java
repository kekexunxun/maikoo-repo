package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.AppletDO;
import com.maikoo.businessdirectory.model.SettingDO;
import org.apache.ibatis.annotations.ResultMap;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

public interface AppletDao {
    @Update("UPDATE comm_setting SET share_text = #{shareText}, mini_name = #{miniName}, lbs_key = #{LBSKey}")
    int update(AppletDO appletDO);

    @ResultMap("settingResultMap")
    @Select("SELECT share_text, mini_name, lbs_key FROM comm_setting")
    SettingDO select();
}
