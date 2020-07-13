package com.maikoo.superminercions.config;

import org.mybatis.spring.annotation.MapperScan;
import org.springframework.context.annotation.Configuration;

@Configuration
@MapperScan("com.maikoo.superminercions.dao")
public class MybatisConfig {
}
