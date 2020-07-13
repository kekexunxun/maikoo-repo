package com.maikoo.superminercions;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.scheduling.annotation.EnableScheduling;

@EnableScheduling
@SpringBootApplication
public class SuperMinerCionsApplication {

    public static void main(String[] args) {
        SpringApplication.run(SuperMinerCionsApplication.class, args);
    }
}
