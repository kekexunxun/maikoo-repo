package com.maikoo.superminercions.util;

import java.time.Instant;
import java.time.LocalDateTime;
import java.time.ZoneId;

public class TimeUtil {

    public static LocalDateTime timeStampToDateTime(long timeStamp){
        return LocalDateTime.ofInstant(Instant.ofEpochSecond(timeStamp), ZoneId.of("UTC+08:00"));
    }
}
