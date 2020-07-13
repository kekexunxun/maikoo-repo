package com.maikoo.businessdirectory.config;

import com.google.common.cache.Cache;
import com.google.common.cache.CacheBuilder;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.Scope;

import java.util.concurrent.TimeUnit;

@Configuration
public class CacheConfig {
    @Bean
    @Scope("singleton")
    public Cache<String, String> stringCache(){
        return CacheBuilder.newBuilder()
                .maximumSize(10)
                .expireAfterAccess(30, TimeUnit.MINUTES)
                .recordStats()
                .build();
    }
}
