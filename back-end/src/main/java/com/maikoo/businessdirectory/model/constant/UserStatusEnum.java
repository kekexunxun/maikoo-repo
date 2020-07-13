package com.maikoo.businessdirectory.model.constant;

import java.util.Arrays;
import java.util.Map;
import java.util.stream.Collectors;

public enum UserStatusEnum {
    ENABLE(1, "ENABLE"),
    DISABLE(2, "DISABLE");

    private final int intStatus;
    private final String stringStatus;
    private static final Map<Integer, UserStatusEnum> intStatusToEnum;
    static {
        intStatusToEnum = Arrays.stream(UserStatusEnum.values()).collect(Collectors.toMap(userRoleEnum -> userRoleEnum.getIntStatus(), userStatusEnum -> userStatusEnum));
    }
    UserStatusEnum(int intStatus, String stringStatus) {
        this.intStatus = intStatus;
        this.stringStatus = stringStatus;
    }
    public int getIntStatus() {
        return intStatus;
    }

    public String getStringStatus() {
        return stringStatus;
    }

    public static UserStatusEnum intStatusToEnum(int intStatus){
        return intStatusToEnum.get(intStatus);
    }
}