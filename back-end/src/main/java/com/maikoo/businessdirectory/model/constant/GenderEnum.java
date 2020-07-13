package com.maikoo.businessdirectory.model.constant;

import java.util.Arrays;
import java.util.Map;
import java.util.stream.Collectors;

public enum GenderEnum {
    MAN(1, "MALE"),
    WOMAN(2, "FEMALE");

    private final int intStatus;
    private final String stringStatus;
    private static final Map<Integer, GenderEnum> intGenderToEnum;
    private static final Map<String, GenderEnum> stringGenderToEnum;

    static {
        intGenderToEnum = Arrays.stream(GenderEnum.values()).collect(Collectors.toMap(genderEnum -> genderEnum.getIntStatus(), genderEnum -> genderEnum));
        stringGenderToEnum = Arrays.stream(GenderEnum.values()).collect(Collectors.toMap(genderEnum -> genderEnum.getStringStatus(), genderEnum -> genderEnum));
    }

    GenderEnum(int intStatus, String stringStatus) {
        this.intStatus = intStatus;
        this.stringStatus = stringStatus;
    }

    public int getIntStatus() {
        return intStatus;
    }

    public String getStringStatus() {
        return stringStatus;
    }

    public static GenderEnum intGenderToEnum(int intGender){
        return intGenderToEnum.get(intGender);
    }

    public static GenderEnum stringGenderToEnum(String stringGender){
        return stringGenderToEnum.get(stringGender);
    }
}
