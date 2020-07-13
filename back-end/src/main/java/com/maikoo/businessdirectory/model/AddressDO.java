package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class AddressDO {
    private long addrId;
    private String addrName;
    private int parentCode;
    private long createdAt;
    private int addrCode;
    private int parentId;
}
