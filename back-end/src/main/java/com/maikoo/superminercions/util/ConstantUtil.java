package com.maikoo.superminercions.util;

import java.time.format.DateTimeFormatter;

public class ConstantUtil {
    public static final DateTimeFormatter BASE_DATE_TIME_PATTERN = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");
    public static final DateTimeFormatter ORDER_SERIAL_NUMBER_DATE_TIME_PATTERN = DateTimeFormatter.ofPattern("yyyyMMddHHmm");
    public static final DateTimeFormatter ORDER_DATE_TIME_PATTERN = BASE_DATE_TIME_PATTERN;

    public static final int ORDER_STATUS_PROCESSING = 0;
    public static final int ORDER_STATUS_COMPLETED = 1;
    public static final int ORDER_STATUS_REJECTED = 2;

    public static final int SMC_TRADING_BUY = 0;
    public static final int SMC_TRADING_SELL = 1;

    public static final int SMC_EXCHANGE_ETH = 0;
    public static final int ETH_EXCHANGE_SMC = 1;

    public static final int ORDER_STATUS_SMC_TRADING_PROCESSING = ORDER_STATUS_PROCESSING;
    public static final int ORDER_STATUS_SMC_TRADING_COMPLETED = ORDER_STATUS_COMPLETED;
    public static final int ORDER_STATUS_SMC_TRADING_REJECTED = ORDER_STATUS_REJECTED;

    public static final int ALL_ACCOUNT_TYPE = -1;
    public static final int FRONT_ACCOUNT_TYPE = 0;
    public static final int ADMIN_ACCOUNT_TYPE = 1;
}
