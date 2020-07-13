package com.maikoo.superminercions.model.query;

import lombok.Data;

@Data
public class OrderQuery {
    private long listSn;
    private boolean success;
    private String remark;
}
