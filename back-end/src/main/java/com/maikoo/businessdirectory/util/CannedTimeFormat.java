package com.maikoo.businessdirectory.util;

import com.maikoo.businessdirectory.model.TimeFrequentQuery;

import java.time.*;
import java.util.ArrayList;
import java.util.List;

/**
 * 用于获取每天的开头和结尾，以及每月的开头和结尾
 */
public class CannedTimeFormat {

    public static List<TimeFrequentQuery> timeFrequent(int frequent) {
        ZoneId zone = ZoneId.systemDefault();
        List<TimeFrequentQuery> list = null;
        switch (frequent) {
            //查询30天
            case 1: {
                list = new ArrayList<TimeFrequentQuery>();
                for (int i = 0; i < 30; i++) {
                    LocalDateTime today_start = LocalDateTime.of(LocalDate.now(), LocalTime.MIN);
                    LocalDateTime today_end = LocalDateTime.of(LocalDate.now(), LocalTime.MAX);
                    Instant instant_start = today_start.atZone(zone).toInstant();
                    Instant instant_end = today_end.atZone(zone).toInstant();

                    TimeFrequentQuery timeFrequentQuery = new TimeFrequentQuery(instant_start.toEpochMilli()/1000 - i * 24 * 60 * 60,instant_end.toEpochMilli()/1000 - i * 24 * 60 * 60 );
//                    timeFrequentQuery.setStartTime(instant_start.toEpochMilli()/1000 + i * 24 * 60 * 60 );
//                    timeFrequentQuery.setEndTime(instant_end.toEpochMilli()/1000 + i * 24 * 60 * 60 );
                    list.add(timeFrequentQuery);
                }
                break;
            }
            //查询12个月的每个月开始的long
            case 2: {
                list = new ArrayList<TimeFrequentQuery>();
                for (int i = 0; i < 12; i++) {
                    TimeFrequentQuery timeFrequentQuery = new TimeFrequentQuery(CommonTimeUtil.getFirstDayOfMonth(i),CommonTimeUtil.getLastDayOfMonth(i));
//                    timeFrequentQuery.setStartTime(CommonTimeUtil.getFirstDayOfMonth(i));
//                    timeFrequentQuery.setEndTime(CommonTimeUtil.getLastDayOfMonth(i));
                    list.add(timeFrequentQuery);
                }
                break;
            }
        }
        return list;
    }

}
