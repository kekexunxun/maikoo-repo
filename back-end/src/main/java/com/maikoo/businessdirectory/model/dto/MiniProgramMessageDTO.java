package com.maikoo.businessdirectory.model.dto;

import lombok.Data;

import java.util.Map;

@Data
public class MiniProgramMessageDTO {
    private String touser;
    private String templateId;
    private String formId;
    private Map<String, MessageDataDTO> data;
    private String page;
}
