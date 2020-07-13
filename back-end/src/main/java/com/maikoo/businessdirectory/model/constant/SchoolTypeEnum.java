package com.maikoo.businessdirectory.model.constant;

import java.util.Arrays;
import java.util.Map;
import java.util.stream.Collectors;

public enum SchoolTypeEnum {
    STUDENT(1, "学生"),
    TEACHER(2, "教师");

    private final int intStatus;
    private final String stringStatus;
    private static final Map<Integer, SchoolTypeEnum> intStatusToEnum;

    static {
        intStatusToEnum = Arrays.stream(SchoolTypeEnum.values()).collect(Collectors.toMap(schoolTypeEnum -> schoolTypeEnum.getIntStatus(), schoolTypeEnum -> schoolTypeEnum));
    }

    SchoolTypeEnum(int intStatus, String stringStatus) {
        this.intStatus = intStatus;
        this.stringStatus = stringStatus;
    }

    public int getIntStatus() {
        return intStatus;
    }

    public String getStringStatus() {
        return stringStatus;
    }

    public static SchoolTypeEnum intStatusToEnum(int intStatus){
        return intStatusToEnum.get(intStatus);
    }
}
