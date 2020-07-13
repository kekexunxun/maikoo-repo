package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.MessageRecordDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Mapper;

@Mapper
public interface MessageRecordDao {
    @Insert("INSERT " +
            "INTO " +
                "comm_message_record" +
                "(c_user_id, phone, created_at) " +
            "VALUES" +
                "(#{customerDO.id}, #{phone}, UNIX_TIMESTAMP(NOW()))")
    int insert(MessageRecordDO messageRecordDO);
}
