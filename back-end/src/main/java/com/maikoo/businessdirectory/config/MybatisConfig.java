package com.maikoo.businessdirectory.config;

import org.mybatis.spring.annotation.MapperScan;
import org.springframework.context.annotation.Configuration;

@Configuration
@MapperScan("com.maikoo.businessdirectory.dao")
public class MybatisConfig {
}
