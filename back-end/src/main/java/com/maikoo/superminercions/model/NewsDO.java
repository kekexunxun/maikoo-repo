package com.maikoo.superminercions.model;

import lombok.Data;

@Data
public class NewsDO {
    private long id;
    private String imgUri;
    private String title;
    private String content;
    private long createdAt;
    private boolean isShowed;
}
