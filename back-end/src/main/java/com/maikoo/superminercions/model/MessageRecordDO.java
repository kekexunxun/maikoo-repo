package com.maikoo.superminercions.model;

import lombok.Data;

@Data
public class MessageRecordDO {
    private Long id;
    private CustomerDO customerDO;
    private String phone;
}
