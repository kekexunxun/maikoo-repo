package com.maikoo.superminercions.serialize;

import com.fasterxml.jackson.core.JsonGenerator;
import com.fasterxml.jackson.databind.JsonSerializer;
import com.fasterxml.jackson.databind.SerializerProvider;
import com.maikoo.superminercions.config.CustomEnvironmentConfig;
import org.springframework.beans.factory.annotation.Autowired;

import java.io.IOException;

public class UrlSerializer extends JsonSerializer<String> {
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;

    @Override
    public void serialize(String value, JsonGenerator gen, SerializerProvider serializers) throws IOException {
        gen.writeString(customEnvironmentConfig.getBaseUrl() + value);
    }
}
