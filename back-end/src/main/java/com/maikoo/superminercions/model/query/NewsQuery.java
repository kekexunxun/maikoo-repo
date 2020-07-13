package com.maikoo.superminercions.model.query;

import lombok.Data;

@Data
public class NewsQuery {

    private String newsImg;
    private String newsTitle;
    private String newsContent;
    private int status;
}
