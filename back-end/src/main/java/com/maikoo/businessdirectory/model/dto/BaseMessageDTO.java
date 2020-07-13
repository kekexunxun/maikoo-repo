package com.maikoo.businessdirectory.model.dto;

import lombok.Data;

@Data
public class BaseMessageDTO {
    private String touser;
    private OfficialMessageDTO mpTemplateMsg;
}
