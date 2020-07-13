package com.maikoo.superminercions.model;

import lombok.Data;

@Data
public class SlideshowDO{
    private long id;
    private String imageUri;
    private NewsDO newsDO;
    private int pageType;
    private int rank;
    private boolean isEnabled;
}
