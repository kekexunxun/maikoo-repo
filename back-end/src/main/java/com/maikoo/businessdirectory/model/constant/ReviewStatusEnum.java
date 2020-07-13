package com.maikoo.businessdirectory.model.constant;

import java.util.Arrays;
import java.util.Map;
import java.util.stream.Collectors;

public enum ReviewStatusEnum {
    PENDING(1, "待审核"),
    APPROVE(2, "已通过"),
    REJECT(3, "已驳回");

    private final int intStatus;
    private final String stringStatus;
    private static final Map<Integer, ReviewStatusEnum> intStatusToEnum;

    static {
        intStatusToEnum = Arrays.stream(ReviewStatusEnum.values()).collect(Collectors.toMap(reviewStatusEnum -> reviewStatusEnum.getIntStatus(), reviewStatusEnum -> reviewStatusEnum));
    }

    ReviewStatusEnum(int intStatus, String stringStatus) {
        this.intStatus = intStatus;
        this.stringStatus = stringStatus;
    }

    public int getIntStatus() {
        return intStatus;
    }

    public String getStringStatus() {
        return stringStatus;
    }

    public static ReviewStatusEnum intStatusToEnum(int intStatus){
        return intStatusToEnum.get(intStatus);
    }
}
