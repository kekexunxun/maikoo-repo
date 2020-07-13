package com.maikoo.superminercions.model;

import lombok.Data;

@Data
public class OrderDO {
    private Long id;
    private Long orderSN;
    private CustomerDO customerDO;
    private Integer status;
    private String note;
    private Long createdAt;
    private Long updatedAt;
}
