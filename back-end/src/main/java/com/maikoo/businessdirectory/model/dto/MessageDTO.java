package com.maikoo.businessdirectory.model.dto;

import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.businessdirectory.model.MessageDO;
import com.maikoo.businessdirectory.model.serializer.BaseDateTimeSerializer;
import lombok.Data;

@Data
public class MessageDTO {
    private long msgId;
    private String msgTitle;
    private String msgContent;
    @JsonSerialize(using = BaseDateTimeSerializer.class)
    private String sendAt;


    public static MessageDTO valueOf(MessageDO messageDO) {
        MessageDTO messageDTO = new MessageDTO();
        messageDTO.setMsgId(messageDO.getMsgId());
        messageDTO.setMsgTitle(messageDO.getMsgTitle());
        messageDTO.setMsgContent(messageDO.getMsgContent());
        messageDTO.setSendAt(String.valueOf(messageDO.getSentAt()));
        return messageDTO;
    }
}
