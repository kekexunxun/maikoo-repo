package com.maikoo.businessdirectory.model.constant;

import java.time.format.DateTimeFormatter;

public enum DateTimeFormatEnum {
    COMMON(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")),
    COMMON_DATE(DateTimeFormatter.ofPattern("yyyy-MM-dd"));

    private final DateTimeFormatter dateTimeFormatter;

    DateTimeFormatEnum(DateTimeFormatter dateTimeFormatter) {
        this.dateTimeFormatter = dateTimeFormatter;
    }

    public DateTimeFormatter getDateTimeFormatter() {
        return dateTimeFormatter;
    }
}
