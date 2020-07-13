package com.maikoo.superminercions.model.query;

import com.maikoo.superminercions.model.constant.SlideshowActionEnum;
import lombok.Data;

@Data
public class SlideShowQuery {
    private long bannerId;
    private String img;
    private int navType;
    private int navId;
    private SlideshowActionEnum action;
    private boolean status;
}
