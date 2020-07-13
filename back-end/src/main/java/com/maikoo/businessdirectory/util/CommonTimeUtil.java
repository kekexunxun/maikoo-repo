package com.maikoo.businessdirectory.util;

import java.text.ParsePosition;
import java.text.SimpleDateFormat;
import java.time.Instant;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.ZoneId;
import java.time.temporal.TemporalAdjusters;
import java.util.Calendar;
import java.util.Date;

public class CommonTimeUtil {

    /**
     * java8(别的版本获取2月有bug) 获取某月开始一天的00:00:00
     *
     * @return
     */
    public static long getFirstDayOfMonth(int month) {
        //获取当月的上month个月
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");
        Calendar cal_1 = Calendar.getInstance();// 获取当前日期
        cal_1.add(Calendar.MONTH, -month);
        cal_1.set(Calendar.DAY_OF_MONTH, 1);// 设置为1号,当前日期既为本月第一天

        Date date = strToDateNotDD(format.format(cal_1.getTime()));
        LocalDateTime localDateTime = LocalDateTime.ofInstant(Instant.ofEpochMilli(date.getTime()),
                ZoneId.systemDefault());
        LocalDateTime endOfDay = localDateTime.with(TemporalAdjusters.firstDayOfMonth()).with(LocalTime.MIN);
        //将LocalDateTime转换成long类型
        ZoneId zone = ZoneId.systemDefault();
        Instant instant = endOfDay.atZone(zone).toInstant();
//        System.out.println(instant.toEpochMilli());
        return instant.toEpochMilli()/1000;
    }

    /**
     * java8(别的版本获取2月有bug) 获取某月最后一天的23:59:59
     *
     * @return
     */
    public static long getLastDayOfMonth(int month) {
        //获取当月的上month个月
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");
        Calendar cal_1 = Calendar.getInstance();// 获取当前日期
        cal_1.add(Calendar.MONTH, -month);
        cal_1.set(Calendar.DAY_OF_MONTH, 1);// 设置为1号,当前日期既为本月第一天

        Date date = strToDateNotDD(format.format(cal_1.getTime()));
        LocalDateTime localDateTime = LocalDateTime.ofInstant(Instant.ofEpochMilli(date.getTime()),
                ZoneId.systemDefault());
        LocalDateTime endOfDay = localDateTime.with(TemporalAdjusters.lastDayOfMonth()).with(LocalTime.MAX);
        //将LocalDateTime转换成long类型
        ZoneId zone = ZoneId.systemDefault();
        Instant instant = endOfDay.atZone(zone).toInstant();
        return instant.toEpochMilli()/1000;
    }

    public static Date strToDateNotDD(String strDate) {
        SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM");
        ParsePosition pos = new ParsePosition(0);
        Date strtodate = formatter.parse(strDate, pos);
        return strtodate;
    }

}
