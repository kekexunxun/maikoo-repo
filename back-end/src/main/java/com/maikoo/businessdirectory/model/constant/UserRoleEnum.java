package com.maikoo.businessdirectory.model.constant;

import java.util.Arrays;
import java.util.Map;
import java.util.stream.Collectors;

public enum UserRoleEnum {
    ADMIN(1, "管理员"),
    MEMBER(2, "成员"),
    STRANGER(3, "陌生用户");

    private final int intStatus;
    private final String stringStatus;
    private static final Map<Integer, UserRoleEnum> intStatusToEnum;

    static {
        intStatusToEnum = Arrays.stream(UserRoleEnum.values()).collect(Collectors.toMap(userRoleEnum -> userRoleEnum.getIntStatus(), userRoleEnum -> userRoleEnum));
    }

    UserRoleEnum(int intStatus, String stringStatus) {
        this.intStatus = intStatus;
        this.stringStatus = stringStatus;
    }

    public int getIntStatus() {
        return intStatus;
    }

    public String getStringStatus() {
        return stringStatus;
    }

    public static UserRoleEnum intStatusToEnum(int intStatus){
        return intStatusToEnum.get(intStatus);
    }
}
