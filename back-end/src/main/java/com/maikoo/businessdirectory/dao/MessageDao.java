package com.maikoo.businessdirectory.dao;

import com.maikoo.businessdirectory.model.MessageDO;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

import java.util.List;

public interface MessageDao {

    @Select("SELECT msg_id FROM comm_msg WHERE send_to = #{userId} ORDER BY msg_id DESC ")
    List<Long> selectIdsByUserId(long userId);

    List<MessageDO> selectByIds(@Param("ids") List<Long> ids);

    @Select("select case count(*) when 0 then 'false' else 'true' end  from comm_msg where send_to=#{userId}  and sent_at >(select message_requested_at from u_user where user_id=#{userId})")
    boolean hasNewMessage(@Param("userId") long userId);


    int createMessage(List<MessageDO> messageDOList);


}
