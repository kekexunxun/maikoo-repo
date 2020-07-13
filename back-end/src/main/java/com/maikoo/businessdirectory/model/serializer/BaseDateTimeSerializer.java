package com.maikoo.businessdirectory.model.serializer;

import com.fasterxml.jackson.core.JsonGenerator;
import com.fasterxml.jackson.databind.JsonSerializer;
import com.fasterxml.jackson.databind.SerializerProvider;
import com.maikoo.businessdirectory.model.constant.DateTimeFormatEnum;
import org.springframework.util.StringUtils;

import java.io.IOException;
import java.time.Instant;
import java.time.LocalDateTime;
import java.time.ZoneId;

public class BaseDateTimeSerializer extends JsonSerializer<String> {
    @Override
    public void serialize(String value, JsonGenerator gen, SerializerProvider serializers) throws IOException {
        if (!StringUtils.isEmpty(value)) {
            gen.writeString(LocalDateTime.
                    ofInstant(Instant.ofEpochSecond(Long.valueOf(value)), ZoneId.of("UTC+08:00")).
                    format(DateTimeFormatEnum.COMMON.getDateTimeFormatter()));
        }else{
            gen.writeString(value);
        }
    }
}
