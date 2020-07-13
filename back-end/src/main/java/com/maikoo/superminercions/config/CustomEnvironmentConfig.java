package com.maikoo.superminercions.config;

import lombok.Data;
import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.context.annotation.Configuration;

@Configuration
@ConfigurationProperties(prefix = "custom")
@Data
public class CustomEnvironmentConfig {
    private int pageNumber;
    private int perPage;
    private String uploadLocation;
    private String imageLocation;
    private int messageAppId;
    private String messageAppKey;
    private int messageTemplateId;
    private String messageSign;
    private String baseUrl;
}
