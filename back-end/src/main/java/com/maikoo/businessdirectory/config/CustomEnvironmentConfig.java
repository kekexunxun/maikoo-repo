package com.maikoo.businessdirectory.config;

import lombok.Data;
import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.boot.context.properties.EnableConfigurationProperties;
import org.springframework.context.annotation.Configuration;

@Configuration
@EnableConfigurationProperties(CustomEnvironmentConfig.class)
@ConfigurationProperties(prefix = "custom")
@Data
public class CustomEnvironmentConfig {
    private int pageNumber;
    private int perPage;
    private String uploadLocation;
    private String imageLocation;
    private String excelLocation;
    private String verticalImageLocation;
    private String commonPosterImageLocation;
    private String countryPosterImageLocation;
    private String baseUrl;
    private String miniAppId;
    private String miniAppSecret;
    private String miniApplyResultTemplateId;
    private String miniCommunityApplyTemplateId;
    private String miniChangeOwnerTemplateId;
    private String appId;
    private String reviewTemplateId;
    private String dismissTemplateId;
    private String changeOwnerTemplateId;
}
