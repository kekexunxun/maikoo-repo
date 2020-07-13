package com.maikoo.businessdirectory.model.constant;

import java.util.Arrays;
import java.util.Map;
import java.util.stream.Collectors;

public enum CommunityTypeEnum {
    OWNER(1, "业主"),
    MANAGER(2, "物业");

    private final int intStatus;
    private final String stringStatus;
    private static final Map<Integer, CommunityTypeEnum> intStatusToEnum;

    static {
        intStatusToEnum = Arrays.stream(CommunityTypeEnum.values()).collect(Collectors.toMap(communityTypeEnum -> communityTypeEnum.getIntStatus(), communityTypeEnum -> communityTypeEnum));
    }

    CommunityTypeEnum(int intStatus, String stringStatus) {
        this.intStatus = intStatus;
        this.stringStatus = stringStatus;
    }

    public int getIntStatus() {
        return intStatus;
    }

    public String getStringStatus() {
        return stringStatus;
    }

    public static CommunityTypeEnum intStatusToEnum(int intStatus){
        return intStatusToEnum.get(intStatus);
    }
}
