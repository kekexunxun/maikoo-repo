package com.maikoo.superminercions.model.constant;

public enum SlideshowActionEnum {
    ACTIVATE(true), DEACTIVATE(false);

    private final boolean value;

    SlideshowActionEnum(boolean value) {
        this.value = value;
    }

    public boolean getValue() {
        return value;
    }
}
