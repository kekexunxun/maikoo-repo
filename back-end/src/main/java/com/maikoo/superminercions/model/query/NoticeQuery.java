package com.maikoo.superminercions.model.query;

import lombok.Data;
import lombok.NonNull;

@Data
public class NoticeQuery {
    private long navId;
    @NonNull
    private String text;
}
