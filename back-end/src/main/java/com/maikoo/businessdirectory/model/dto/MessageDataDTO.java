package com.maikoo.businessdirectory.model.dto;

import lombok.Data;

@Data
public class MessageDataDTO {
    private String value;

    public static MessageDataDTO value(String value){
        MessageDataDTO messageDataDTO = new MessageDataDTO();
        messageDataDTO.setValue(value);
        return messageDataDTO;
    }
}
