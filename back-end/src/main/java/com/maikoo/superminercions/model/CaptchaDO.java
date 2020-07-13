package com.maikoo.superminercions.model;

import lombok.Data;

import java.io.Serializable;

@Data
public class CaptchaDO implements Serializable {
    private String captcha;
    private boolean isUsed;
}
