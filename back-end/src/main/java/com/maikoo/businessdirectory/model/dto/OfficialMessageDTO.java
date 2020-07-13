package com.maikoo.businessdirectory.model.dto;

import lombok.Data;

import java.util.Map;

@Data
public class OfficialMessageDTO {
    private String appid;
    private String templateId;
    private String url;
    private Map<String, String> miniprogram;
    private Map<String, MessageDataDTO> data;
}
