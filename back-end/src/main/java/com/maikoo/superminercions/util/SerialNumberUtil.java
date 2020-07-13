package com.maikoo.superminercions.util;

import java.time.LocalDateTime;

public class SerialNumberUtil {

    public static long order() {
        StringBuilder serialNumber = new StringBuilder();

        LocalDateTime current = LocalDateTime.now();
        serialNumber.append(current.format(ConstantUtil.ORDER_SERIAL_NUMBER_DATE_TIME_PATTERN));
        serialNumber.append(randomNumber());

        return Long.valueOf(serialNumber.toString());
    }

    private static String randomNumber() {
        StringBuilder numberStringBuilder = new StringBuilder();

        int number = (int) (Math.random() * 1000);
        if (number < 100 && number >= 10) {
            numberStringBuilder.append("0");
        }
        if (number < 10) {
            numberStringBuilder.append("00");
        }

        numberStringBuilder.append(number);

        return numberStringBuilder.toString();
    }
}
